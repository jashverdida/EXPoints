<?php

// Load environment variables if not already loaded
if (!getenv('SUPABASE_URL')) {
    require_once __DIR__ . '/env.php';
}

/**
 * Helper function to mimic Laravel's env() for standalone PHP
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to Supabase PostgreSQL database
    | via REST API.
    |
    */

    'url' => env('SUPABASE_URL', ''),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
    'service_key' => env('SUPABASE_SERVICE_KEY', ''),
    'timeout' => env('SUPABASE_TIMEOUT', 30),
];
