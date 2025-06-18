<?php

namespace Arpanpatoliya\DBBackup;

class Backup extends BackupManager
{
    public function run(): string
    {
        // Export the database
        $exportResult = $this->exporter->export();
        $exportData = json_decode($exportResult, true);

        // If export failed, return the error message
        if (!$exportData['status']) {
            return json_encode([
                'status' => false,
                'message' => $exportData['message']
            ]);
        }

        $filePath = $exportData['file_path'];

        // Upload the file to Google Drive
        $uploadResult = $this->uploader->upload($filePath);
        $uploadData = json_decode($uploadResult, true);

        if ($uploadData['status']) {
            return json_encode([
                'status' => true,
                'message' => 'Backup completed successfully',
                'file_path' => $filePath,
                'upload_status' => $uploadData['message']
            ]);
        } else {
            return json_encode([
                'status' => false,
                'message' => 'Backup created but upload failed',
                'file_path' => $filePath,
                'upload_error' => $uploadData['message']
            ]);
        }
    }
}
