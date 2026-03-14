<?php

if (!function_exists('asset_with_env')) {
    function asset_with_env($path)
    {
        if (env('APP_ENV') === 'local') {
            return asset($path);
            Log::info('Current APP_ENV: ' . env('APP_ENV'));
        } else {
            return asset('public/' . $path);
        }
    }
}