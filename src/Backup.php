<?php

namespace Arpanpatoliya\DBBackup;

/**
 * Backup Class
 * 
 * Main backup orchestration class that extends BackupManager.
 * Handles the complete backup process including database export and file upload.
 * Coordinates between the exporter and uploader services.
 */
class Backup extends BackupManager
{
    /**
     * Run Backup Process
     * 
     * Executes the complete backup workflow:
     * 1. Exports the database using the configured exporter
     * 2. Uploads the backup file using the configured uploader
     * 3. Returns detailed status information about the process
     * 
     * @return string JSON encoded response with backup status and details
     */
    public function run(): string
    {
        // Step 1: Export the database using the configured exporter service
        $exportResult = $this->exporter->export();
        $exportData = json_decode($exportResult, true);

        // Check if database export was successful
        if (!$exportData['status']) {
            // Return error response if export failed
            return json_encode([
                'status' => false,
                'message' => $exportData['message']
            ]);
        }

        // Extract the file path from successful export
        $filePath = $exportData['file_path'];

        // Step 2: Upload the backup file to Google Drive using the configured uploader service
        $uploadResult = $this->uploader->upload($filePath);
        $uploadData = json_decode($uploadResult, true);

        // Check if file upload was successful
        if ($uploadData['status']) {
            // Return success response with both export and upload details
            return json_encode([
                'status' => true,
                'message' => 'Backup completed successfully',
                'file_path' => $filePath,
                'upload_status' => $uploadData['message']
            ]);
        } else {
            // Return partial success response (export worked, upload failed)
            return json_encode([
                'status' => false,
                'message' => 'Backup created but upload failed',
                'file_path' => $filePath,
                'upload_error' => $uploadData['message']
            ]);
        }
    }
}
