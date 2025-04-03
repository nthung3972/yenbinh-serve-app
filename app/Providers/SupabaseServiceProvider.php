<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Supabase\CreateClient;

class SupabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('supabase', function ($app) {
            $supabaseKey = config('services.supabase.key');
            $supabaseUrl = config('services.supabase.url');
            
            // 👇 Xóa 'https://', 'http://' và path nếu có
            $cleanUrl = preg_replace(
                ['/^https?:\/\//', '/\/storage\/v1$/'], 
                '', 
                $supabaseUrl
            );
            
            $client = new CreateClient($supabaseKey, $cleanUrl);
            return $client;
        });

    //     public function register()
    // {
    //     $this->app->singleton('supabase', function ($app) {
    //         return new \Supabase\CreateClient(
    //             config('services.supabase.key'),
    //             config('services.supabase.url'),
    //             // config('services.supabase.secret')
    //         );
    //     });
    // }
    }

    public function boot()
    {
        //
    }
}
