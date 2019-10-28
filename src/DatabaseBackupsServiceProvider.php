<?php

namespace Medvesekg\DatabaseBackups;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Medvesekg\DatabaseBackups\Commands\DatabaseBackupCommand;
use Medvesekg\DatabaseBackups\Commands\CleanOldDatabaseBackupsCommand;
use Medvesekg\DatabaseBackups\Jobs\DatabaseBackupJob;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;
use Symfony\Component\Process\Process;

class DatabaseBackupsServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(DatabaseBackupCommand::class, function($app) {
            return new DatabaseBackupCommand($app->make(Config::class), $app->make(Filesystem::class), new Process([]));
        });
    }


    public function boot()
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseBackupCommand::class,
                CleanOldDatabaseBackupsCommand::class,
            ]);
        }

        // Register scheduled job
        $this->app->booted(function() {
            $frequency = Str::camel(config('database.backups.frequency'));
            if($frequency) {
                $schedule = app(Schedule::class);
                try {
                    $schedule
                        ->job(DatabaseBackupJob::class)
                        ->$frequency();
                }
                catch (\BadMethodCallException $e) {
                    throw new InvalidArgumentException("$frequency is not a valid database backup frequency.");
                }
            }
        });


       
    }
}