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

if (!function_exists('getIkmGrade')) {
    /**
     * Mengonversi skor IKM numerik menjadi nilai huruf dan deskripsi.
     * @param float $score Skor IKM (0-100)
     * @return array
     */
    function getIkmGrade(float $score): array
    {
        if ($score >= 88.31) {
            return ['grade' => 'A', 'description' => 'Sangat Baik', 'color' => 'green'];
        } elseif ($score >= 76.61) {
            return ['grade' => 'B', 'description' => 'Baik', 'color' => 'sky'];
        } elseif ($score >= 65.00) {
            return ['grade' => 'C', 'description' => 'Kurang Baik', 'color' => 'amber'];
        } else {
            return ['grade' => 'D', 'description' => 'Tidak Baik', 'color' => 'red'];
        }
    }
}
