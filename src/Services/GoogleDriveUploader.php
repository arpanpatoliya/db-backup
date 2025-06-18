<?php
namespace Arpanpatoliya\DBBackup\Services;

use Arpanpatoliya\DBBackup\Contracts\UploaderInterface;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDriveUploader implements UploaderInterface
{
    public function upload(string $filePath): string
    {
        try {
            $fileName = basename($filePath);

            $client = $this->getGoogleClient();
            $token = Cache::get('google_drive_access_token', config('dbbackup.google_drive_access_token'));
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken(config('dbbackup.google_drive_refresh_token'));
                if (!empty($newToken['access_token'])) {
                    Cache::put(
                        'google_drive_access_token',
                        $newToken['access_token'],
                        now()->addSeconds($newToken['expires_in'] ?? 3600)
                    );
                    $client->setAccessToken($newToken['access_token']);
                }
            }

            $service = $this->getGoogleDriveService($client);

            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => [config('dbbackup.google_drive_folder')],
            ]);

            $content = file_get_contents($filePath);

            $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/sql',
                'uploadType' => 'multipart',
            ]);

            $this->cleanupOldFiles($service);
            
            return json_encode([
                'status' => true,
                'message' => 'Backup uploaded to Google Drive successfully'
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'status' => false,
                'message' => 'Google Drive upload failed: ' . $e->getMessage()
            ]);
        }
    }

    protected function cleanupOldFiles(Google_Service_Drive $service): void
    {
        try {
            $files = $service->files->listFiles([
                'q' => "'" . config('dbbackup.google_drive_folder') . "' in parents and trashed = false",
                'fields' => 'files(id, name, createdTime)',
                'orderBy' => 'createdTime desc',
            ]);

            $driveFiles = $files->getFiles();

            if (count($driveFiles) > config('dbbackup.max_stored_backups')) {
                $filesToDelete = array_slice($driveFiles, config('dbbackup.max_stored_backups'));

                foreach ($filesToDelete as $file) {
                    try {
                        $service->files->delete($file->getId());
                    } catch (\Exception $e) {
                        // Do nothing, just skip
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing, just skip
        }
    }

    protected function getGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(config('dbbackup.google_drive_client_id'));
        $client->setClientSecret(config('dbbackup.google_drive_client_secret'));
        return $client;
    }

    protected function getGoogleDriveService(Google_Client $client): Google_Service_Drive
    {
        return new Google_Service_Drive($client);
    }
}
