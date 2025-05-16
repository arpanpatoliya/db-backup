<?php
namespace Tests\Unit;

use Arpanpatoliya\DBBackup\Services\GoogleDriveUploader;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class GoogleDriveUploaderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Log\LogServiceProvider::class,
        ];
    }

    public function testUploadReturnsTrueOnSuccess()
    {
        // Prepare dummy file
        $filePath = sys_get_temp_dir() . '/' . Str::random(10) . '.sql';
        file_put_contents($filePath, 'dummy content');

        // Mock config
        Config::set('dbbackup.google_drive_client_id', 'client-id');
        Config::set('dbbackup.google_drive_client_secret', 'client-secret');
        Config::set('dbbackup.google_drive_access_token', 'access-token');
        Config::set('dbbackup.google_drive_refresh_token', 'refresh-token');
        Config::set('dbbackup.google_drive_folder', 'folder-id');

        // Mock Cache
        Cache::shouldReceive('get')->andReturn('access-token');
        Cache::shouldReceive('put')->andReturn(true);

        // Mock Google Client
        $mockClient = $this->createMock(Google_Client::class);
        $mockClient->method('isAccessTokenExpired')->willReturn(false);
        $mockClient->method('setAccessToken');
        $mockClient->method('setClientId');
        $mockClient->method('setClientSecret');

        // Mock Google Service
        $mockService = $this->createMock(Google_Service_Drive::class);
        $mockService->files = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();
        $mockService->files->method('create')->willReturn(true);

        // Partial mock the uploader
        $uploader = $this->getMockBuilder(GoogleDriveUploader::class)
            ->onlyMethods(['getGoogleClient', 'getGoogleDriveService'])
            ->getMock();

        $uploader->method('getGoogleClient')->willReturn($mockClient);
        $uploader->method('getGoogleDriveService')->willReturn($mockService);

        $result = $uploader->upload($filePath);

        $this->assertTrue($result);

        unlink($filePath);
    }
}
