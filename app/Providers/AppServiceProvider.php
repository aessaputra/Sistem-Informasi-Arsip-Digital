<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use custom Tabler pagination view
        Paginator::defaultView('vendor.pagination.tabler');

        // Configure polymorphic morph map for duplicate detection
        // Using morphMap instead of enforceMorphMap to allow other models to work
        Relation::morphMap([
            'surat_masuk' => \App\Models\SuratMasuk::class,
            'surat_keluar' => \App\Models\SuratKeluar::class,
        ]);
    }
}

