# Database backups for Laravel

Easily perform database backups in Laravel.

## Quickstart

`composer require medvesekg/laravel-database-backups`

`php artisan db:backup`

Assuming a default Laravel installation this will create a backup in the `storage/app` directory

## Requirements
- mysqldump for mysql
- pgdump for postgres

## Configuration
In `config/database.php` at the root level add:

```php
'backups' => [
    'disk' => 'local',
    'frequency' => 'daily',
    'retention' => 'two weeks'
]
```

### Disk
The Laravel disk where you want to store the backups. I recommend creating a seperate disk for backups in `config/filesystems.php`. You can use any driver supported by Laravel.

### Frequency
How frequently do you want to perform backups. Accepted values are names of Laravel's schedule methods e.g. *daily*, *weekly*, *monthly* see https://laravel.com/docs/5.8/scheduling#schedule-frequency-options for reference.

For automatic backups to work you need to set up Laravel's scheduler. Refer to Laravel documentation.

### Retention
How long do you want to keep old backups for. Accepted values are anything Carbon datetime library can parse. See https://carbon.nesbot.com/docs/#api-addsub for reference. Examples: *two weeks*

For automatic cleaning of old backups you need to set up Laravel's scheduler. Refer to Laravel documentation.

## Artisan commands
The package adds two Artisan commands
- `php artisan db:backup` Creates a new backup
- `php artisan db:backup:clean` Deletes backups older than the retention policy


## Supported database drivers
- Mysql
- Postgres
- Sqlite
