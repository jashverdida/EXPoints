<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to Supabase REST API.
    |
    */

    'url' => env('SUPABASE_URL', ''),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
    'service_key' => env('SUPABASE_SERVICE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests to Supabase.
    |
    */

    'timeout' => env('SUPABASE_TIMEOUT', 30),
];
