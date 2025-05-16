<?php

namespace Arpanpatoliya\DBBackup;

class Backup extends BackupManager
{
    public function run(): bool
    {
        // Export the database
        $file = $this->exporter->export();

        if (!$file || !file_exists($file)) {
            return false;
        }

        // Upload the file to Google Drive
        return $this->uploader->upload($file);
    }
}
