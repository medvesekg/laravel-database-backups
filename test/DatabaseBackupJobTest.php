<?php

use Illuminate\Foundation\Console\Kernel;
use Medvesekg\DatabaseBackups\Jobs\DatabaseBackupJob;
use PHPUnit\Framework\TestCase;
use \Mockery as Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;


class DatabaseBackupJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $artisan;

    public function setUp() : void
    {
        $this->artisan = Mockery::mock(Kernel::class);
        $this->job = new DatabaseBackupJob($this->artisan);
    }


    public function testExample()
    {
        $this->artisan->shouldReceive('call')->once()->with('db:backup');
        $this->artisan->shouldReceive('call')->once()->with('db:backup:clean');

        $this->job->handle($this->artisan);
    }

}