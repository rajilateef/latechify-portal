<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Thin Monnify (monnify.com) checkout client — the site's only payment gateway.
 * Credentials come from Site Settings (monnify_*) or config/services.php.
 */
class Monnify
{
    /**
     * Every payment reference we send to Monnify carries this prefix, so Latechify
     * transactions are identifiable at a glance in the Monnify dashboard and in
     * settlement reports.
     */
    public const REFERENCE_PREFIX = 'LATECHIFY';

    /**
     * Build a prefixed, collision-resistant payment reference.
     * e.g. reference('CHK', 42) => "LATECHIFY-CHK-42-8F3KQ1"
     */
    public static function reference(string $scope, int|string $id): string
    {
        return implode('-', [
            self::REFERENCE_PREFIX,
            Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $scope)),
            $id,
            Str::upper(Str::random(6)),
        ]);
    }

    public function apiKey(): ?string
    {
        return trim((string) (Setting::value('monnify_api_key') ?: config('services.monnify.api_key'))) ?: null;
    }

    public function secretKey(): ?string
    {
        return trim((string) (Setting::value('monnify_secret_key') ?: config('services.monnify.secret_key'))) ?: null;
    }

    public function contractCode(): ?string
    {
        return trim((string) (Setting::value('monnify_contract_code') ?: config('services.monnify.contract_code'))) ?: null;
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

    /**
     * Authenticate and return a bearer access token, or null on failure.
     * Never throws — callers fall back to bank transfer rather than erroring.
     */
    public function token(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->apiKey(), $this->secretKey())
                ->acceptJson()
                ->timeout(20)
                ->retry(2, 250, throw: false)
                ->post("{$this->base()}/api/v1/auth/login");
        } catch (ConnectionException $e) {
            $this->fail('auth', 'Could not reach Monnify: '.$e->getMessage());

            return null;
        }

        if ($response->successful() && $response->json('requestSuccessful')) {
            return $response->json('responseBody.accessToken');
        }

        $this->fail('auth', $response->json('responseMessage') ?: 'HTTP '.$response->status());

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

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(25)
                ->retry(2, 300, throw: false)
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
        } catch (ConnectionException $e) {
            $this->fail('initialize', 'Could not reach Monnify: '.$e->getMessage());

            return null;
        }

        if ($response->successful() && $response->json('requestSuccessful')) {
            return [
                'checkout_url'          => $response->json('responseBody.checkoutUrl'),
                'transaction_reference' => $response->json('responseBody.transactionReference'),
                'payment_reference'     => $response->json('responseBody.paymentReference'),
            ];
        }

        $this->fail('initialize', $response->json('responseMessage') ?: 'HTTP '.$response->status());

        return null;
    }

    /**
     * Record why a call failed, so a silent fallback to bank transfer is still
     * diagnosable. The last reason is also surfaced by the admin's test button.
     */
    protected function fail(string $stage, ?string $reason): void
    {
        static::$lastError = trim($stage.': '.($reason ?: 'unknown error'));

        Log::warning('[monnify] '.static::$lastError, ['live' => $this->isLive()]);
    }

    public static ?string $lastError = null;

    public function lastError(): ?string
    {
        return static::$lastError;
    }

    /**
     * Check the credentials end to end: authenticate, then initialise a tiny
     * throwaway transaction to prove the contract code belongs to this account.
     * Returns [ok, message].
     */
    public function testConnection(): array
    {
        static::$lastError = null;

        if (! $this->isConfigured()) {
            return [false, 'API key, secret key and contract code are all required.'];
        }

        if (! $this->token()) {
            return [false, 'Authentication failed — check the API key and secret key. ('.(static::$lastError ?: 'no detail').')'];
        }

        // Auth can succeed while the contract code belongs to a different account,
        // so prove the whole path the way checkout actually uses it.
        $probe = $this->initialize(
            'Connection test',
            'test@'.parse_url(config('app.url'), PHP_URL_HOST ?: 'latechify.test'),
            100,
            static::reference('TEST', time()),
            route('checkout.callback'),
            'Monnify connection test',
        );

        if (! $probe) {
            $error = static::$lastError ?: 'unknown error';

            if (str_contains($error, 'Could not reach Monnify')) {
                return [false, 'Network problem: the server could not reach Monnify. Credentials look fine — retry, and check outbound HTTPS/firewall if it persists.'];
            }

            if (stripos($error, 'contract') !== false) {
                return [false, 'The API key and secret are valid, but Monnify rejected the CONTRACT CODE ("'
                    .$this->contractCode().'"). Copy the Contract Code from your Monnify dashboard → Settings → API Keys & Webhooks, making sure it is from the same '
                    .($this->isLive() ? 'LIVE' : 'sandbox').' account as the API key.'];
            }

            return [false, 'Authenticated, but the transaction could not be created — '.$error.'.'];
        }

        return [true, 'Connected to Monnify in '.($this->isLive() ? 'LIVE' : 'sandbox').' mode. Online payment is working.'];
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

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(25)
                ->retry(2, 250, throw: false)
                ->get("{$this->base()}/api/v2/transactions/".urlencode($transactionReference));
        } catch (ConnectionException $e) {
            $this->fail('verify', 'Could not reach Monnify: '.$e->getMessage());

            return null;
        }

        if ($response->successful() && $response->json('requestSuccessful')) {
            $body = $response->json('responseBody');

            if (in_array($body['paymentStatus'] ?? null, ['PAID', 'OVERPAID'], true)) {
                return $body;
            }
        }

        return null;
    }
}
