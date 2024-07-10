<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use FFMpeg\FFMpeg;

class FFMpegServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FFMpeg::class, function ($app) {
            return FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),
                'timeout'          => 3600, // optional
                'ffmpeg.threads'   => 12,   // optional
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
