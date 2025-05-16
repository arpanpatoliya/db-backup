<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Backup Path
    |--------------------------------------------------------------------------
    |
    | Local directory to store backups before upload. You can customize it
    | using the DBBACKUP_LOCAL_PATH variable or use the default path.
    |
    */
    'local_backup_path' => env('DBBACKUP_LOCAL_PATH', storage_path('app/backups')),

    /*
    |--------------------------------------------------------------------------
    | Stored Backup Limit
    |--------------------------------------------------------------------------
    |
    | Number of recent backups to keep. Older ones will be deleted automatically.
    | Set to null to keep all backups.
    |
    */
    'max_stored_backups' => env('DBBACKUP_MAX_STORED', 5),

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Specify the database connection name to back up (e.g., mysql, pgsql).
    | This should match one defined in config/database.php.
    |
    */
    'db_connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Google Drive API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are required to authenticate with Google Drive.
    |
    */
    'google_drive_client_id'     => env('GOOGLE_DRIVE_CLIENT_ID'),
    'google_drive_client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'google_drive_redirect_uri'  => env('GOOGLE_DRIVE_REDIRECT_URI'),
    'google_drive_access_token'  => env('GOOGLE_DRIVE_ACCESS_TOKEN'),
    'google_drive_refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Google Drive Folder ID
    |--------------------------------------------------------------------------
    |
    | The ID of the folder in Google Drive where backups will be stored.
    |
    */
    'google_drive_folder' => env('GOOGLE_DRIVE_FOLDER'),
];
