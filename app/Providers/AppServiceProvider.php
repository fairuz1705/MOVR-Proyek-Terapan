<?php

namespace App\Providers;

use App\Models\AdminLog;
use App\Models\DetailProduk;
use App\Models\Ekspedisi;
use App\Models\Pengguna;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Promo;
use App\Models\Produk;
use App\Models\RatingProduk;
use App\Models\StockMovement;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderDetail;
use App\Models\Transaksi;
use App\Models\TrackingLog;
use App\Models\Voucher;
use App\Observers\AdminActivityObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Kategori;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\TryOn\TryOnProvider::class, function ($app) {
            $driver = config('services.tryon.driver', 'mock');
            return match ($driver) {
                'huggingface' => new \App\Services\TryOn\Providers\HuggingFaceTryOnProvider(),
                'gemini' => new \App\Services\TryOn\Providers\GeminiTryOnProvider(),
                default => new \App\Services\TryOn\Providers\MockTryOnProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Voucher::observe(AdminActivityObserver::class);
        Promo::observe(AdminActivityObserver::class);
        Ekspedisi::observe(AdminActivityObserver::class);
        Pengguna::observe(AdminActivityObserver::class);
        Transaksi::observe(AdminActivityObserver::class);
        Pembayaran::observe(AdminActivityObserver::class);
        Pesanan::observe(AdminActivityObserver::class);
        TrackingLog::observe(AdminActivityObserver::class);
        RatingProduk::observe(AdminActivityObserver::class);
        Produk::observe(AdminActivityObserver::class);
        DetailProduk::observe(AdminActivityObserver::class);
        StockMovement::observe(AdminActivityObserver::class);
        SupplierOrder::observe(AdminActivityObserver::class);
        SupplierOrderDetail::observe(AdminActivityObserver::class);

        // Skip view/database bootstrap during CLI commands (e.g., artisan serve).
        if ($this->app->runningInConsole()) {
            return;
        }

        // Share top-level active categories (with children) to all views
        try {
            $menuKategori = Kategori::with(['children.children'])
                ->whereNull('parent_id')
                ->where('is_active', 1)
                ->orderBy('urutan')
                ->get();

            View::share('menuKategori', $menuKategori);
        } catch (\Throwable $e) {
            // If something goes wrong (e.g., migrations not run yet), don't break the app
        }
    }
}
