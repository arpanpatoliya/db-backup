<?php

namespace Arpanpatoliya\DBBackup;

use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;

abstract class BackupManager
{
    protected ExporterInterface $exporter;
    protected UploaderInterface $uploader;

    public function __construct()
    {
        // Directly resolve the Exporter and Uploader via the container
        $this->exporter = app(ExporterInterface::class);
        $this->uploader = app(UploaderInterface::class);
    }

    // Abstract method for child classes to implement
    abstract public function run(): bool;
}
