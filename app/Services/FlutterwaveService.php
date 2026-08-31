<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.flutterwave.secret_key', '');
        $this->publicKey = (string) config('services.flutterwave.public_key', '');
        $this->baseUrl = rtrim((string) config('services.flutterwave.base_url', 'https://api.flutterwave.com/v3'), '/');
        try {
            $this->secretKey = (string) SiteSetting::get('flutterwave_secret_key', $this->secretKey);
            $this->publicKey = (string) SiteSetting::get('flutterwave_public_key', $this->publicKey);
            $this->baseUrl = rtrim((string) SiteSetting::get('flutterwave_base_url', $this->baseUrl), '/');
        } catch (\Throwable $e) {}
    }

    public function configured(): bool { return $this->secretKey !== ''; }
    public function publicKey(): string { return $this->publicKey; }

    public function initialise(array $payload): ?array
    {
        try {
            $r = Http::withToken($this->secretKey)->acceptJson()->timeout(40)->post($this->baseUrl.'/payments', $payload);
            if (!$r->successful()) {
                Log::error('Flutterwave initialize failed', ['status'=>$r->status(),'body'=>$r->body()]);
                return null;
            }
            $json = $r->json();
            return ($json['status'] ?? '') === 'success' ? ($json['data'] ?? null) : null;
        } catch (\Throwable $e) {
            Log::error('Flutterwave initialize exception', ['message'=>$e->getMessage()]);
            return null;
        }
    }

    public function verify(string $transactionId): ?array
    {
        try {
            $r = Http::withToken($this->secretKey)->acceptJson()->timeout(40)->get($this->baseUrl.'/transactions/'.rawurlencode($transactionId).'/verify');
            if (!$r->successful()) {
                Log::error('Flutterwave verify failed', ['transaction'=>$transactionId,'status'=>$r->status()]);
                return null;
            }
            $json=$r->json();
            if (($json['status'] ?? '') !== 'success') return null;
            $data=$json['data'] ?? null;
            return is_array($data) && ($data['status'] ?? '') === 'successful' ? $data : null;
        } catch (\Throwable $e) {
            Log::error('Flutterwave verify exception', ['transaction'=>$transactionId,'message'=>$e->getMessage()]);
            return null;
        }
    }
}
