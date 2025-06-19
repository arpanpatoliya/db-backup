<?php

namespace Arpanpatoliya\DBBackup;

use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;

/**
 * BackupManager Abstract Class
 * 
 * Abstract base class that provides the foundation for backup operations.
 * Manages dependency injection of exporter and uploader services through Laravel's service container.
 * Defines the contract for backup implementations through the abstract run() method.
 */
abstract class BackupManager
{
    /** @var ExporterInterface Service responsible for database export operations */
    protected ExporterInterface $exporter;
    
    /** @var UploaderInterface Service responsible for file upload operations */
    protected UploaderInterface $uploader;

    /**
     * Constructor
     * 
     * Initializes the backup manager by resolving the exporter and uploader services
     * from Laravel's service container. This allows for dependency injection and
     * easy service swapping through configuration.
     */
    public function __construct()
    {
        // Resolve the Exporter service from the container using the interface
        $this->exporter = app(ExporterInterface::class);
        // Resolve the Uploader service from the container using the interface
        $this->uploader = app(UploaderInterface::class);
    }

    /**
     * Run Backup Process (Abstract Method)
     * 
     * Abstract method that must be implemented by child classes.
     * Defines the contract for executing backup operations.
     * 
     * @return string JSON encoded response with backup status and details
     */
    abstract public function run(): string;
}
