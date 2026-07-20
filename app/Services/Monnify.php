<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Thin Monnify (monnify.com) checkout client — mirrors the Paystack service.
 * Credentials come from Site Settings (monnify_*) or config/services.php.
 */
class Monnify
{
    public function apiKey(): ?string
    {
        return Setting::value('monnify_api_key') ?: config('services.monnify.api_key');
    }

    public function secretKey(): ?string
    {
        return Setting::value('monnify_secret_key') ?: config('services.monnify.secret_key');
    }

    public function contractCode(): ?string
    {
        return Setting::value('monnify_contract_code') ?: config('services.monnify.contract_code');
    }

    public function isLive(): bool
    {
        $setting = Setting::value('monnify_live');

        return $setting !== null
            ? filter_var($setting, FILTER_VALIDATE_BOOLEAN)
            : (bool) config('services.monnify.live');
    }

    public function base(): string
    {
        return $this->isLive() ? 'https://api.monnify.com' : 'https://sandbox.monnify.com';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey()) && ! empty($this->secretKey()) && ! empty($this->contractCode());
    }

    /** Authenticate and return a bearer access token, or null on failure. */
    public function token(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withBasicAuth($this->apiKey(), $this->secretKey())
            ->acceptJson()
            ->post("{$this->base()}/api/v1/auth/login");

        if ($response->successful() && $response->json('requestSuccessful')) {
            return $response->json('responseBody.accessToken');
        }

        return null;
    }

    /**
     * Initialize a transaction. Returns [checkout_url, transaction_reference, payment_reference] or null.
     */
    public function initialize(
        string $name,
        string $email,
        int $amountNaira,
        string $paymentReference,
        string $redirectUrl,
        string $description = 'Payment',
    ): ?array {
        $token = $this->token();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->base()}/api/v1/merchant/transactions/init-transaction", [
                'amount'             => $amountNaira,
                'customerName'       => $name,
                'customerEmail'      => $email,
                'paymentReference'   => $paymentReference,
                'paymentDescription' => $description,
                'currencyCode'       => 'NGN',
                'contractCode'       => $this->contractCode(),
                'redirectUrl'        => $redirectUrl,
                'paymentMethods'     => ['CARD', 'ACCOUNT_TRANSFER'],
            ]);

        if ($response->successful() && $response->json('requestSuccessful')) {
            return [
                'checkout_url'          => $response->json('responseBody.checkoutUrl'),
                'transaction_reference' => $response->json('responseBody.transactionReference'),
                'payment_reference'     => $response->json('responseBody.paymentReference'),
            ];
        }

        return null;
    }

    /**
     * Verify a transaction by its Monnify transactionReference.
     * Returns the responseBody when PAID, otherwise null.
     */
    public function verify(string $transactionReference): ?array
    {
        $token = $this->token();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->base()}/api/v2/transactions/".urlencode($transactionReference));

        if ($response->successful() && $response->json('requestSuccessful')) {
            $body = $response->json('responseBody');

            if (in_array($body['paymentStatus'] ?? null, ['PAID', 'OVERPAID'], true)) {
                return $body;
            }
        }

        return null;
    }
}
