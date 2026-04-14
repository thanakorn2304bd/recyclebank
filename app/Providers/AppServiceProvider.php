<?php

namespace App\Providers;

use App\Models\Household;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Policies\HouseholdPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\WithdrawRequestPolicy;
use App\Support\Backup\BackupStorage;
use App\Support\Storage\DocumentStorage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        config([
            'dompdf.options.temp_dir' => $this->dompdfTempDir(),
            'dompdf.options.font_dir' => $this->dompdfFontCacheDir(),
            'dompdf.options.font_cache' => $this->dompdfFontCacheDir(),
        ]);

        $this->app->singleton(DocumentStorage::class, function ($app) {
            $config = $app['config']->get('services.document_storage', []);

            return new DocumentStorage(
                driver: (string) ($config['driver'] ?? 'local'),
                localDisk: (string) ($config['local_disk'] ?? 'local'),
                blobToken: $config['vercel_blob_token'] ?? null,
            );
        });

        $this->app->singleton(BackupStorage::class, function ($app) {
            $config = $app['config']->get('services.backup_storage', []);

            return new BackupStorage(
                driver: (string) ($config['driver'] ?? 'local'),
                localDisk: (string) ($config['local_disk'] ?? 'local'),
                blobToken: $config['vercel_blob_token'] ?? null,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Household::class, HouseholdPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(WithdrawRequest::class, WithdrawRequestPolicy::class);

        $this->ensureDompdfRuntimeDirectories();

        Paginator::useBootstrapFive();
    }

    private function ensureDompdfRuntimeDirectories(): void
    {
        File::ensureDirectoryExists($this->dompdfTempDir());
        File::ensureDirectoryExists($this->dompdfFontCacheDir());
    }

    private function dompdfTempDir(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'dompdf';
    }

    private function dompdfFontCacheDir(): string
    {
        return $this->dompdfTempDir().DIRECTORY_SEPARATOR.'fonts';
    }
}
