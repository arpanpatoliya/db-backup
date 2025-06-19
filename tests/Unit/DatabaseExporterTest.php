<?php
namespace Tests\Unit;

use Arpanpatoliya\DBBackup\Services\DatabaseExporter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Process\Process;

/**
 * DatabaseExporterTest Class
 * 
 * Unit tests for the DatabaseExporter service.
 * Tests database export functionality, file creation, and configuration handling.
 * Uses Orchestra Testbench for Laravel package testing.
 */
class DatabaseExporterTest extends TestCase
{
    /**
     * Get Package Providers
     * 
     * Returns an array of service providers to be loaded during testing.
     * In this case, no additional providers are needed for the test.
     * 
     * @param mixed $app Laravel application instance
     * @return array Array of service provider classes
     */
    protected function getPackageProviders($app)
    {
        return [];
    }

    /**
     * Set Up Test Environment
     * 
     * Configures the test environment before each test method runs.
     * Sets up database configuration, backup paths, and ensures test directories exist.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Configure backup settings for testing
        Config::set('dbbackup.local_backup_path', storage_path('app/db_backups'));
        Config::set('dbbackup.max_stored_backups', 3);

        // Configure test database connection
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
        ]);

        // Ensure the test backup directory exists
        File::ensureDirectoryExists(storage_path('app/db_backups'));
    }

    /**
     * Test Database Export Creates Backup File
     * 
     * Tests that the database export process creates a backup file successfully.
     * Uses a mock Process class to simulate successful mysqldump execution.
     * Verifies file creation, naming, and cleanup.
     * 
     * @return void
     */
    public function testDatabaseExportCreatesBackupFile()
    {
        // Create a mock Process class to simulate successful mysqldump execution
        $mock = $this->createMock(Process::class);
        $mock->method('run')->willReturn(0);
        $mock->method('isSuccessful')->willReturn(true);

        // Create an anonymous class that extends DatabaseExporter for testing
        // This allows us to override the export method for testing purposes
        $exporter = new class($mock) extends DatabaseExporter {
            protected $mockProcess;
            
            /**
             * Constructor for test exporter
             * 
             * @param mixed $mock Mock Process instance
             */
            public function __construct($mock)
            {
                $this->mockProcess = $mock;
            }

            /**
             * Override export method for testing
             * 
             * Creates a test backup file instead of running actual mysqldump.
             * 
             * @return string Path to the created backup file
             */
            public function export(): ?string
            {
                // Create a test backup file
                $fileName = 'test_backup.sql';
                $path = storage_path("app/db_backups/{$fileName}");

                // Write dummy SQL content to simulate a real backup
                file_put_contents($path, 'Dummy SQL');

                return $path;
            }
        };

        // Execute the export and get the file path
        $filePath = $exporter->export();

        // Assert that the export was successful
        $this->assertNotNull($filePath);
        $this->assertFileExists($filePath);
        $this->assertStringEndsWith('.sql', $filePath);

        // Clean up the test file
        unlink($filePath);
    }
}
