<?php
/**
 * Created by PhpStorm.
 * User: Dewesoft
 * Date: 10/04/2019
 * Time: 09:59
 */

namespace Medvesekg\DatabaseBackups\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;


class CleanOldDatabaseBackupsCommand extends Command
{
    protected $signature = 'db:backup:clean';

    protected $description = 'Delete database backups older than the retention policy';


    protected $config;
    protected $storage;
    protected $process;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Config $config, Filesystem $storage, OutputStyle $output=null)
    {
        parent::__construct();

        $this->config = $config;
        $this->storage = $storage;

        if($output) {
            $this->output = $output;
        }
    }


    public function handle()
    {
        $disk = $this->config->get('database.backups.disk');

        $backups = collect($this->storage->disk($disk)->allFiles());
        $retention = $this->config->get('database.backups.retention');
        $cutOff = Carbon::now()->sub($retention)->timestamp;

        $deleteCount = 0;
        $backups->each(function($file) use ($cutOff, &$deleteCount, $disk) {
            preg_match('/\w+_(\d+)\.sql/', $file, $matches);

            if(!$matches) return;

            $backupTimestamp = $matches[1];
            if($backupTimestamp < $cutOff) {
                $this->storage->disk($disk)->delete($file);
                $deleteCount++;
            };
        });

        if($deleteCount) $this->info("Deleted $deleteCount old backups.");
        else $this->info("No backups older than $retention.");
    }

}