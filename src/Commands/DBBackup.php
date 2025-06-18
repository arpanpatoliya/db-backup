<?php

namespace Arpanpatoliya\DBBackup\Commands;

use Arpanpatoliya\DBBackup\Backup;
use Illuminate\Console\Command;

class DBBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports the database and saves a backup file locally (and optionally uploads to Google Drive)';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backup = new Backup();
        $result = $backup->run();
        $data = json_decode($result, true);
        
        if ($data['status']) {
            $this->info('✅ ' . $data['message']);
            if (isset($data['file_path'])) {
                $this->line('📁 File: ' . $data['file_path']);
            }
            if (isset($data['upload_status'])) {
                $this->line('☁️  ' . $data['upload_status']);
            }
        } else {
            $this->error('❌ ' . $data['message']);
            if (isset($data['upload_error'])) {
                $this->error('☁️  Upload Error: ' . $data['upload_error']);
            }
        }
    }

}