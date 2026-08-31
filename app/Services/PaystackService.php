<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SiteSetting;

/**
 * Secure Paystack integration.
 *
 * - initialise(): creates a transaction on Paystack and returns the authorization URL.
 * - verify(): confirms a transaction by reference against Paystack's API (server-side,
 *   the only trusted source of truth — never trust the client callback).
 * - Supports currency conversion: Paystack only accepts supported local currencies
 *   (NGN, GHS, ZAR, KES). Dollar amounts are converted using the currency usd_value.
 */
class PaystackService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret_key', '');
        $this->publicKey = (string) config('services.paystack.public_key', '');
        $this->baseUrl   = rtrim((string) config('services.paystack.base_url', 'https://api.paystack.co'), '/');
        // Prefer admin-managed settings when the database is installed. During the installer/CLI,
        // fall back silently to config so a missing DB driver/table cannot break application boot.
        try {
            $this->secretKey = (string) SiteSetting::get('paystack_secret_key', $this->secretKey);
            $this->publicKey = (string) SiteSetting::get('paystack_public_key', $this->publicKey);
            $this->baseUrl   = rtrim((string) SiteSetting::get('paystack_base_url', $this->baseUrl), '/');
        } catch (\Throwable $e) {
            // Installer or first boot: config values remain authoritative until settings exist.
        }
    }

    public function configured(): bool
    {
        return ! empty($this->secretKey) && ! empty($this->publicKey);
    }

    public function publicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Initialise a Paystack transaction.
     *
     * @param array $params email, amount (in PAYSTACK currency minor units), reference, callback_url, metadata
     */
    public function initialise(array $params): ?array
    {
        $response = Http::withToken($this->secretKey)
            ->timeout(40)
            ->post($this->baseUrl.'/transaction/initialize', $params);

        if (! $response->successful()) {
            Log::error('Paystack initialize failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        return $data['data'] ?? null;
    }

    /**
     * Verify a transaction by its reference. Always fetched server-side.
     */
    public function verify(string $reference): ?array
    {
        $response = Http::withToken($this->secretKey)
            ->timeout(40)
            ->get($this->baseUrl.'/transaction/verify/'.urlencode($reference));

        if (! $response->successful()) {
            Log::error('Paystack verify failed', [
                'reference' => $reference,
                'status'    => $response->status(),
                'body'      => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        if (($data['status'] ?? false) === true && ($data['data']['status'] ?? '') === 'success') {
            return $data['data'];
        }

        return null;
    }

    /**
     * Generate a unique reference.
     */
    public function generateReference(string $prefix = 'MW'): string
    {
        return $prefix.'_'.strtoupper(\Illuminate\Support\Str::random(14)).'_'.time();
    }
}
