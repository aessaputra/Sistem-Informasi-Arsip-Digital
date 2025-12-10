<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DuplicateDetectionInterface;
use App\Services\DuplicateDetectionService;

class DuplicateDetectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DuplicateDetectionInterface::class, DuplicateDetectionService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}