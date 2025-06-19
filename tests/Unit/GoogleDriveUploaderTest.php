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

/**
 * GoogleDriveUploaderTest Class
 * 
 * Unit tests for the GoogleDriveUploader service.
 * Tests Google Drive upload functionality, authentication, and file handling.
 * Uses Orchestra Testbench for Laravel package testing with mocked Google services.
 */
class GoogleDriveUploaderTest extends TestCase
{
    /**
     * Get Package Providers
     * 
     * Returns an array of service providers to be loaded during testing.
     * Includes cache, filesystem, and log providers for comprehensive testing.
     * 
     * @param mixed $app Laravel application instance
     * @return array Array of service provider classes
     */
    protected function getPackageProviders($app)
    {
        return [
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Log\LogServiceProvider::class,
        ];
    }

    /**
     * Test Upload Returns True On Success
     * 
     * Tests that the Google Drive upload process returns success status
     * when all components work correctly. Uses comprehensive mocking
     * to simulate the Google Drive API without actual API calls.
     * 
     * @return void
     */
    public function testUploadReturnsTrueOnSuccess()
    {
        // Create a temporary test file with dummy content
        $filePath = sys_get_temp_dir() . '/' . Str::random(10) . '.sql';
        file_put_contents($filePath, 'dummy content');

        // Configure Google Drive API settings for testing
        Config::set('dbbackup.google_drive_client_id', 'client-id');
        Config::set('dbbackup.google_drive_client_secret', 'client-secret');
        Config::set('dbbackup.google_drive_access_token', 'access-token');
        Config::set('dbbackup.google_drive_refresh_token', 'refresh-token');
        Config::set('dbbackup.google_drive_folder', 'folder-id');

        // Mock the Cache facade to return a valid access token
        Cache::shouldReceive('get')->andReturn('access-token');
        Cache::shouldReceive('put')->andReturn(true);

        // Create a mock Google Client with required methods
        $mockClient = $this->createMock(Google_Client::class);
        $mockClient->method('isAccessTokenExpired')->willReturn(false);
        $mockClient->method('setAccessToken');
        $mockClient->method('setClientId');
        $mockClient->method('setClientSecret');

        // Create a mock Google Drive Service with file creation capability
        $mockService = $this->createMock(Google_Service_Drive::class);
        $mockService->files = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();
        $mockService->files->method('create')->willReturn(true);

        // Create a partial mock of the GoogleDriveUploader
        // This allows us to mock specific methods while testing the main upload logic
        $uploader = $this->getMockBuilder(GoogleDriveUploader::class)
            ->onlyMethods(['getGoogleClient', 'getGoogleDriveService'])
            ->getMock();

        // Configure the mock to return our mocked Google services
        $uploader->method('getGoogleClient')->willReturn($mockClient);
        $uploader->method('getGoogleDriveService')->willReturn($mockService);

        // Execute the upload and get the result
        $result = $uploader->upload($filePath);

        // Assert that the upload was successful
        $this->assertTrue($result);

        // Clean up the temporary test file
        unlink($filePath);
    }
}
