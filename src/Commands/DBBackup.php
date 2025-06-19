<?php

namespace Arpanpatoliya\DBBackup\Commands;

use Arpanpatoliya\DBBackup\Backup;
use Illuminate\Console\Command;

/**
 * DBBackup Command Class
 * 
 * Laravel Artisan command for executing database backups.
 * Provides a command-line interface to run the complete backup process
 * including database export and optional file upload to Google Drive.
 */
class DBBackup extends Command
{
    /**
     * Command Signature
     * 
     * Defines the command name and any parameters it accepts.
     * This command can be executed using: php artisan db-backup:run
     *
     * @var string
     */
    protected $signature = 'db-backup:run';

    /**
     * Command Description
     * 
     * Provides a clear description of what the command does.
     * This description appears in the command help and list.
     *
     * @var string
     */
    protected $description = 'Exports the database and saves a backup file locally (and optionally uploads to Google Drive)';

    /**
     * Constructor
     * 
     * Creates a new command instance and calls the parent constructor
     * to properly initialize the command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute Command
     * 
     * Main method that executes the backup process when the command is run.
     * Creates a new Backup instance, runs the backup process, and displays
     * the results with appropriate formatting and emojis for better UX.
     * 
     * @return void
     */
    public function handle()
    {
        // Create a new Backup instance to handle the backup process
        $backup = new Backup();
        // Execute the backup process and get the result
        $result = $backup->run();
        // Decode the JSON response to access the data
        $data = json_decode($result, true);
        
        // Check if the backup process was successful
        if ($data['status']) {
            // Display success message with checkmark emoji
            $this->info('✅ ' . $data['message']);
            // Display the backup file path if available
            if (isset($data['file_path'])) {
                $this->line('📁 File: ' . $data['file_path']);
            }
            // Display upload status if the file was uploaded successfully
            if (isset($data['upload_status'])) {
                $this->line('☁️  ' . $data['upload_status']);
            }
        } else {
            // Display error message with X emoji
            $this->error('❌ ' . $data['message']);
            // Display upload error details if upload failed
            if (isset($data['upload_error'])) {
                $this->error('☁️  Upload Error: ' . $data['upload_error']);
            }
        }
    }
}