<?php

namespace Arpanpatoliya\DBBackup\Contracts;

/**
 * ExporterInterface Contract
 * 
 * Defines the contract for database export operations.
 * Any class implementing this interface must provide a method
 * to export database data and return the result as a string.
 * This allows for different export strategies and implementations.
 */
interface ExporterInterface
{
    /**
     * Export Database
     * 
     * Exports the database according to the implementing class's strategy.
     * The method should handle the complete export process and return
     * a JSON encoded string with status and result information.
     * 
     * @return string|null JSON encoded response with export status and details
     */
    public function export(): ?string;
}
