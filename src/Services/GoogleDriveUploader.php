<?php
namespace Arpanpatoliya\DBBackup\Services;

use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * GoogleDriveUploader Class
 * 
 * Handles file uploads to Google Drive using the Google Drive API.
 * Implements the UploaderInterface to provide standardized upload functionality.
 * Manages OAuth2 authentication, token refresh, and file cleanup operations.
 */
class GoogleDriveUploader implements UploaderInterface
{
    /**
     * Upload File to Google Drive
     * 
     * Uploads a file to Google Drive with automatic token management and cleanup.
     * Handles OAuth2 authentication, token refresh, file metadata creation,
     * and cleanup of old files to maintain storage limits.
     * 
     * @param string $filePath Path to the file to be uploaded
     * @return string JSON encoded response with upload status and message
     */
    public function upload(string $filePath): string
    {
        try {
            // Extract the filename from the file path
            $fileName = basename($filePath);

            // Initialize Google Client and set up authentication
            $client = $this->getGoogleClient();
            // Get access token from cache or configuration
            $token = Cache::get('google_drive_access_token', config('dbbackup.google_drive_access_token'));
            $client->setAccessToken($token);

            // Check if access token is expired and refresh if necessary
            if ($client->isAccessTokenExpired()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken(config('dbbackup.google_drive_refresh_token'));
                if (!empty($newToken['access_token'])) {
                    // Cache the new access token with expiration time
                    Cache::put(
                        'google_drive_access_token',
                        $newToken['access_token'],
                        now()->addSeconds($newToken['expires_in'] ?? 3600)
                    );
                    $client->setAccessToken($newToken['access_token']);
                }
            }

            // Get Google Drive service instance
            $service = $this->getGoogleDriveService($client);

            // Create file metadata for Google Drive
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => [config('dbbackup.google_drive_folder')],
            ]);

            // Read the file content for upload
            $content = file_get_contents($filePath);

            // Upload the file to Google Drive
            $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/sql',
                'uploadType' => 'multipart',
            ]);

            // Clean up old files to maintain storage limits
            $this->cleanupOldFiles($service);
            
            // Return success response
            return json_encode([
                'status' => true,
                'message' => 'Backup uploaded to Google Drive successfully'
            ]);

        } catch (\Exception $e) {
            // Return error response if upload fails
            return json_encode([
                'status' => false,
                'message' => 'Google Drive upload failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup Old Files
     * 
     * Removes old backup files from Google Drive to maintain the configured
     * maximum number of stored backups. Files are sorted by creation time
     * and excess files are deleted.
     * 
     * @param Google_Service_Drive $service Google Drive service instance
     * @return void
     */
    protected function cleanupOldFiles(Google_Service_Drive $service): void
    {
        try {
            // List all files in the configured Google Drive folder
            $files = $service->files->listFiles([
                'q' => "'" . config('dbbackup.google_drive_folder') . "' in parents and trashed = false",
                'fields' => 'files(id, name, createdTime)',
                'orderBy' => 'createdTime desc',
            ]);

            $driveFiles = $files->getFiles();

            // Check if we have more files than the maximum allowed
            if (count($driveFiles) > config('dbbackup.max_stored_backups')) {
                // Get files to delete (files beyond the maximum limit)
                $filesToDelete = array_slice($driveFiles, config('dbbackup.max_stored_backups'));

                // Delete excess files
                foreach ($filesToDelete as $file) {
                    try {
                        $service->files->delete($file->getId());
                    } catch (\Exception $e) {
                        // Silently skip if deletion fails for a specific file
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently skip cleanup if it fails entirely
        }
    }

    /**
     * Get Google Client
     * 
     * Creates and configures a Google Client instance with the necessary
     * OAuth2 credentials from the configuration.
     * 
     * @return Google_Client Configured Google Client instance
     */
    protected function getGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        // Set OAuth2 client credentials
        $client->setClientId(config('dbbackup.google_drive_client_id'));
        $client->setClientSecret(config('dbbackup.google_drive_client_secret'));
        return $client;
    }

    /**
     * Get Google Drive Service
     * 
     * Creates a Google Drive service instance using the provided client.
     * This service is used for all Google Drive API operations.
     * 
     * @param Google_Client $client Authenticated Google Client instance
     * @return Google_Service_Drive Google Drive service instance
     */
    protected function getGoogleDriveService(Google_Client $client): Google_Service_Drive
    {
        return new Google_Service_Drive($client);
    }
}
