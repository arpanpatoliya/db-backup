<?php

namespace Arpanpatoliya\DBBackup;

use Arpanpatoliya\DBBackup\Commands\DBBackup;
use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;
use Arpanpatoliya\DBBackup\Services\DatabaseExporter;
use Arpanpatoliya\DBBackup\Services\GoogleDriveUploader;
use Illuminate\Support\ServiceProvider;

class DBBackupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ExporterInterface::class, DatabaseExporter::class);
        $this->app->bind(UploaderInterface::class, GoogleDriveUploader::class);
    
        $this->app->singleton(Backup::class, function ($app) {
            return new Backup();
        });

        $this->commands([
            DBBackup::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/config/dbbackup.php', 'dbbackup'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/dbbackup.php' => config_path('dbbackup.php'),
        ], 'config');

        if (!function_exists('storage_path')) {
            function storage_path($path = '')
            {
                return app()->storagePath($path);
            }
        }
    }
} 