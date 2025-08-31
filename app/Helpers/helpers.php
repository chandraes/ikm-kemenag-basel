<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // Ambil dari cache, atau query ke DB dan simpan di cache selamanya
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key');
        });

        return $settings->get($key, $default);
    }
}
