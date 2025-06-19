<?php

namespace Arpanpatoliya\DBBackup\Services;

use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * DatabaseExporter Class
 * 
 * Handles the export of database backups using mysqldump command.
 * Implements the ExporterInterface to provide standardized backup functionality.
 */
class DatabaseExporter implements ExporterInterface
{
    /** @var string Path where local backups will be stored */
    protected string $localBackupPath;
    
    /** @var int Maximum number of backup files to keep locally */
    protected int $maxStoredBackups;

    /**
     * Constructor
     * 
     * Initializes the exporter with configuration values from the dbbackup config file.
     * Sets up the local backup path and maximum number of stored backups.
     */
    public function __construct()
    {
        // Get local backup path from configuration
        $this->localBackupPath = config('dbbackup.local_backup_path');
        // Get maximum number of backups to keep from configuration
        $this->maxStoredBackups = config('dbbackup.max_stored_backups');
    }

    /**
     * Export Database
     * 
     * Creates a database backup using mysqldump command.
     * Generates a timestamped filename, creates temporary credentials file,
     * executes mysqldump, and cleans up old backups.
     * 
     * @return string JSON encoded response with status and message
     */
    public function export(): string
    {
        try {
            // Ensure backup directory exists
            $this->makeDir();
        
            // Generate filename with current timestamp
            $fileName = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $filePath = rtrim($this->localBackupPath, '/') . '/' . $fileName;
        
            // Get database connection configuration
            $connectionName = config('dbbackup.db_connection', config('database.default'));
            $connection = config("database.connections.{$connectionName}");
        
            // Create temporary MySQL configuration file for credentials
            $tempCnfPath = storage_path('app/tmp-my.cnf');
            file_put_contents($tempCnfPath, <<<EOF
        [client]
        user="{$connection['username']}"
        password="{$connection['password']}"
        host="{$connection['host']}"
        EOF
            );
            // Set restrictive permissions on credentials file
            chmod($tempCnfPath, 0600);
        

            // Build mysqldump command with security and performance options
            $command = sprintf(
                'mysqldump --defaults-extra-file=%s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
                escapeshellarg($tempCnfPath),
                escapeshellarg($connection['database']),
                escapeshellarg($filePath)
            );
        
            // Execute the mysqldump command
            exec($command, $output, $returnVar);
    
            // Clean up temporary credentials file
            unlink($tempCnfPath);
        
            // Check if mysqldump command was successful
            if ($returnVar !== 0) {
                return json_encode([
                    'status' => false,
                    'message' => 'mysqldump failed: ' . implode("\n", $output)
                ]);
            }
        
            // Remove old backup files to maintain storage limits
            $this->cleanupOldBackups();
        
            // Return success response with file path
            return json_encode([
                'status' => true,
                'message' => 'Database backup created successfully',
                'file_path' => $filePath
            ]);
        
        } catch (\Throwable $e) {
            // Return error response if any exception occurs
            return json_encode([
                'status' => false,
                'message' => 'Database backup failed: ' . $e->getMessage()
            ]);
        }
        
    }

    /**
     * Make Directory
     * 
     * Creates the backup directory if it doesn't exist.
     * Uses recursive directory creation with appropriate permissions.
     * 
     * @return void
     */
    protected function makeDir(): void
    {
        // Check if directory exists, create if not
        if (!is_dir($this->localBackupPath)) {
            mkdir($this->localBackupPath, 0755, true);
        }
    }

    /**
     * Cleanup Old Backups
     * 
     * Removes old backup files to maintain the configured maximum number of stored backups.
     * Files are sorted by modification time (newest first) and excess files are deleted.
     * 
     * @return void
     */
    protected function cleanupOldBackups(): void
    {
        // Skip cleanup if max stored backups is not a valid number
        if (!is_numeric($this->maxStoredBackups)) return;

        // Get all backup files matching the pattern
        $files = glob(rtrim($this->localBackupPath, '/') . '/backup_*.sql');

        // Sort files by modification time (newest first)
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

        // Get files to delete (files beyond the maximum limit)
        $toDelete = array_slice($files, $this->maxStoredBackups);

        // Delete excess backup files
        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }
}

