# Laravel DB Backup with Google Drive Integration

[![Latest Version](https://img.shields.io/packagist/v/arpanpatoliya/db-backup.svg)](https://packagist.org/packages/arpanpatoliya/db-backup)
[![License](https://img.shields.io/github/license/arpanpatoliya/db-backup)](https://github.com/arpanpatoliya/db-backup/blob/main/LICENSE)

A Laravel package to automate your database backups and upload them directly to **Google Drive**.

---

## ✨ Features

- Easily back up your Laravel application's database
- Automatically upload backups to **Google Drive**
- Run backups via artisan commands
- Configure automatic cleanup of old backups

---

## 📦 Installation

Install the package via Composer:

```bash
composer require arpanpatoliya/db-backup
```

## ⚙️ Configuration

<p> After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="ArpanPatoliya\DbBackup\DbBackupServiceProvider"

```

<p>This will create a config file at:

```base
config/dbbackup.php
```

<p>Update your .env file with the following values:

```env
# Local backup path (customize if needed)
DBBACKUP_LOCAL_PATH=/full/custom/path/if/needed

# Optional: Maximum number of backups to retain (default: 5)
DBBACKUP_MAX_STORED=5

# Database connection (should match config/database.php)
DB_CONNECTION=mysql

# Google Drive API credentials
GOOGLE_DRIVE_CLIENT_ID=your-client-id
GOOGLE_DRIVE_CLIENT_SECRET=your-client-secret
GOOGLE_DRIVE_ACCESS_TOKEN=your-access-token
GOOGLE_DRIVE_REFRESH_TOKEN=your-refresh-token

# Google Drive folder ID to store backups
GOOGLE_DRIVE_FOLDER=your-google-drive-folder-id

```

## 🚀 Usage

### Run Backup Programmatically

<p>You can trigger a backup directly in your code:

```base
use Arpanpatoliya\DbBackup\Backup;

public function triggerBackupDirectly()
{
    $backup = new Backup();
    $success = $backup->run();

    return $success
        ? response()->json(['message' => 'Backup completed successfully.'])
        : response()->json(['message' => 'Backup failed.'], 500);
}

```

### Run Backup via Artisan Command
<p>Useful for cron jobs or scheduled tasks:

```base
php artisan db-backup:run
```


## ✅ Testing
<p>To run the tests:

```php
composer test
```

## 👨‍💻 Credits

- [Arpan Patoliya](https://github.com/arpanpatoliya) - Project creator and maintainer

## 📄 License
MIT License. See [LICENSE](https://github.com/arpanpatoliya/db-backup/blob/main/LICENSE) for details.
