<?php

namespace Medvesekg\DatabaseBackups\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

class DatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $artisan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Kernel $artisan)
    {
        $this->artisan = $artisan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->artisan->call('db:backup');
        $this->artisan->call('db:backup:clean');
    }
}
