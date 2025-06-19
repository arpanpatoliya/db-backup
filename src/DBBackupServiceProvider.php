<?php

namespace Arpanpatoliya\DBBackup;

use Arpanpatoliya\DBBackup\Commands\DBBackup;
use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;
use Arpanpatoliya\DBBackup\Services\DatabaseExporter;
use Arpanpatoliya\DBBackup\Services\GoogleDriveUploader;
use Illuminate\Support\ServiceProvider;

/**
 * DBBackupServiceProvider Class
 * 
 * Laravel service provider for the DBBackup package.
 * Handles service registration, dependency injection bindings,
 * configuration publishing, and command registration.
 */
class DBBackupServiceProvider extends ServiceProvider
{
    /**
     * Register Services
     * 
     * Registers all services and bindings for the DBBackup package.
     * Sets up dependency injection bindings for interfaces to concrete implementations.
     * Registers the backup command and merges configuration files.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ExporterInterface to the DatabaseExporter implementation
        $this->app->bind(ExporterInterface::class, DatabaseExporter::class);
        // Bind the UploaderInterface to the GoogleDriveUploader implementation
        $this->app->bind(UploaderInterface::class, GoogleDriveUploader::class);
    
        // Register the Backup class as a singleton for consistent state
        $this->app->singleton(Backup::class, function ($app) {
            return new Backup();
        });

        // Register the DBBackup command with Laravel's command system
        $this->commands([
            DBBackup::class,
        ]);

        // Merge the package configuration with the application's config
        $this->mergeConfigFrom(
            __DIR__.'/config/dbbackup.php', 'dbbackup'
        );
    }

    /**
     * Bootstrap Services
     * 
     * Bootstraps the package after all services are registered.
     * Publishes configuration files and ensures required helper functions exist.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the configuration file to the application's config directory
        $this->publishes([
            __DIR__.'/config/dbbackup.php' => config_path('dbbackup.php'),
        ], 'config');

        // Ensure the storage_path helper function exists (for older Laravel versions)
        if (!function_exists('storage_path')) {
            function storage_path($path = '')
            {
                return app()->storagePath($path);
            }
        }
    }
} 