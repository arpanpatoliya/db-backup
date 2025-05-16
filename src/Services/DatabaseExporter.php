<?php

namespace Arpanpatoliya\DBBackup\Services;

use Arpanpatoliya\DBBackup\Contracts\ExporterInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DatabaseExporter implements ExporterInterface
{
    protected string $localBackupPath;
    protected int $maxStoredBackups;

    public function __construct()
    {
        $this->localBackupPath = config('dbbackup.local_backup_path');
        $this->maxStoredBackups = config('dbbackup.max_stored_backups');
    }

    public function export(): ?string
    {
        try {
            $this->makeDir();
        
            $fileName = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $filePath = rtrim($this->localBackupPath, '/') . '/' . $fileName;
        
            $connectionName = config('dbbackup.db_connection', config('database.default'));
            $connection = config("database.connections.{$connectionName}");
        
            $tempCnfPath = storage_path('app/tmp-my.cnf');
            file_put_contents($tempCnfPath, <<<EOF
        [client]
        user="{$connection['username']}"
        password="{$connection['password']}"
        host="{$connection['host']}"
        EOF
            );
            chmod($tempCnfPath, 0600);
        

            $command = sprintf(
                'mysqldump --defaults-extra-file=%s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
                escapeshellarg($tempCnfPath),
                escapeshellarg($connection['database']),
                escapeshellarg($filePath)
            );
        
            // dd($command);
            exec($command, $output, $returnVar);
        
            unlink($tempCnfPath);
        
            if ($returnVar !== 0) {
                throw new \RuntimeException('mysqldump failed: ' . implode("\n", $output));
            }
        
            $this->cleanupOldBackups();
        
            return $filePath;
        
        } catch (\Throwable $e) {
            // error reporting
            Log::error('Database backup failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return null;
        }
        
    }

    protected function makeDir(): void
    {
        if (!is_dir($this->localBackupPath)) {
            mkdir($this->localBackupPath, 0755, true);
        }
    }

    protected function cleanupOldBackups(): void
    {
        if (!is_numeric($this->maxStoredBackups)) return;

        $files = glob(rtrim($this->localBackupPath, '/') . '/backup_*.sql');

        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

        $toDelete = array_slice($files, $this->maxStoredBackups);

        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }
}

