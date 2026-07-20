<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class Paystack
{
    protected string $base = 'https://api.paystack.co';

    public function secretKey(): ?string
    {
        return Setting::value('paystack_secret_key') ?: config('services.paystack.secret');
    }

    public function publicKey(): ?string
    {
        return Setting::value('paystack_public_key') ?: config('services.paystack.public');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->secretKey());
    }

    /**
     * Initialize a transaction. Returns [authorization_url, reference] or null on failure.
     */
    public function initialize(string $email, int $amountNaira, string $callbackUrl, array $metadata = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken($this->secretKey())
            ->acceptJson()
            ->post("{$this->base}/transaction/initialize", [
                'email'        => $email,
                'amount'       => $amountNaira * 100, // kobo
                'callback_url' => $callbackUrl,
                'metadata'     => $metadata,
            ]);

        if ($response->successful() && $response->json('status')) {
            return [
                'authorization_url' => $response->json('data.authorization_url'),
                'reference'         => $response->json('data.reference'),
            ];
        }

        return null;
    }

    /**
     * Verify a transaction by reference. Returns the Paystack "data" payload or null.
     */
    public function verify(string $reference): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken($this->secretKey())
            ->acceptJson()
            ->get("{$this->base}/transaction/verify/".urlencode($reference));

        if ($response->successful() && $response->json('data.status') === 'success') {
            return $response->json('data');
        }

        return null;
    }
}
