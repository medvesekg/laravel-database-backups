<?php

namespace Medvesekg\DatabaseBackups\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backups the database';


    protected $config;
    protected $storage;
    protected $process;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Config $config, Filesystem $storage, Process $process, OutputStyle $output=null)
    {
        parent::__construct();

        $this->config = $config;
        $this->storage = $storage;
        $this->process = $process;

        if($output) {
            $this->output = $output;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $connection     =       $this->config->get('database.default');
        $host           =       $this->config->get("database.connections.$connection.host");
        $database       =       $this->config->get("database.connections.$connection.database");
        $username       =       $this->config->get("database.connections.$connection.username");
        $port           =       $this->config->get("database.connections.$connection.port");
        $password       =       $this->config->get("database.connections.$connection.password");

        $timestamp = time();
        $filename = "{$database}_{$timestamp}.sql";
        $disk = $this->config->get('database.backups.disk') ?: $this->config->get('filesystems.default');

        $command = [];
        $env = [];

        if($connection === 'pgsql') {
            $command = 'pg_dump';
            if($host) {
                $command .= " -h $host";
            }
            if($username) {
                $command .= " -U $username";         
            }
            if($port) {
                $command .= " -p $port";
            }
            if($password) {
                $env['PGPASSWORD'] = $password;
            }

            $command .= " $database > $filename";
        }
        else if($connection === 'mysql') {
            $command = 'mysqldump';

            if($host) {
                $command .= " -h $host";
            }
            if($username) {
                $command .= " -u $username";   
            }
            if($port) {
                $command .= " -P $port";
            }
            if($password) {
                $command .= " -p $password";
            }
            
            $command .= " $database > $filename";
        }
        else if($connection === 'sqlite') {
            $this->storage->disk($disk)->put('database_' . time() . '.sqlite', fopen($database, 'r'));
            return;
        }
        else {
            throw new InvalidArgumentException("'$connection' database not supported.");
        }
 
        $this->process->setCommandLine($command);
        $this->process->setWorkingDirectory(base_path());
        $this->process->run(null, $env);
        
        if(!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        
        $this->storage->disk($disk)->put($filename, fopen(base_path($filename), 'r'));

        unlink(base_path($filename));

        $this->info("Created backup");
    }

    
}
