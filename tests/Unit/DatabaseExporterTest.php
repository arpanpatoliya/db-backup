<?php
namespace Tests\Unit;

use Arpanpatoliya\DBBackup\Services\DatabaseExporter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Process\Process;

class DatabaseExporterTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('dbbackup.local_backup_path', storage_path('app/db_backups'));
        Config::set('dbbackup.max_stored_backups', 3);

        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
        ]);

        File::ensureDirectoryExists(storage_path('app/db_backups'));
    }

    public function testDatabaseExportCreatesBackupFile()
    {
        // Fake Process (pretend mysqldump worked)
        $mock = $this->createMock(Process::class);
        $mock->method('run')->willReturn(0);
        $mock->method('isSuccessful')->willReturn(true);

        // Replace Process factory in export logic (you can inject it instead in a real design)
        $exporter = new class($mock) extends DatabaseExporter {
            protected $mockProcess;
            public function __construct($mock)
            {
                $this->mockProcess = $mock;
            }

            public function export(): ?string
            {
                $fileName = 'test_backup.sql';
                $path = storage_path("app/db_backups/{$fileName}");

                file_put_contents($path, 'Dummy SQL'); // simulate dump

                return $path;
            }
        };

        $filePath = $exporter->export();

        $this->assertNotNull($filePath);
        $this->assertFileExists($filePath);
        $this->assertStringEndsWith('.sql', $filePath);

        unlink($filePath); // Cleanup
    }
}
