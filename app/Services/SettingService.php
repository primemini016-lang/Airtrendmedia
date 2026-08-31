<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

/**
 * Cached access to the single app_settings row + default currency.
 */
class SettingService
{
    private const CACHE_KEY = 'app_settings:singleton';

    public function all(): AppSetting
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $s = AppSetting::find(1);
            if (! $s) {
                // Boot a default row if missing (shouldn't happen post-install).
                $s = AppSetting::create(['id' => 1]);
            }
            return $s;
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    public function defaultCurrency(): Currency
    {
        $settings = $this->all();
        $currency = $settings->defaultCurrency;
        if ($currency) {
            return $currency;
        }
        return Currency::where('is_default', true)->first()
            ?? Currency::where('code', 'USD')->first()
            ?? new Currency(['code' => 'USD', 'symbol' => '$', 'usd_value' => 1]);
    }

    public function activationFee(): float
    {
        return (float) $this->get('activation_fee', 5.00);
    }

    public function affiliateReward(): float
    {
        return (float) $this->get('affiliate_reward', 1.50);
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
