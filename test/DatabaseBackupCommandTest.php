<?php

namespace Medvesekg\DatabaseBackups\Commands;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use \Mockery as Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Process\Exception\ProcessFailedException;

function fopen($path) {
    return;
}
function unlink($path) {
    return;
}

class DatabaseBackupCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $command;
    private $config;
    private $filesystem;
    private $output;
    private $process;

    public function setUp() : void
    {

        $this->config = Mockery::mock('Illuminate\Contracts\Config\Repository');
        $this->config->shouldReceive('get')->andReturn('mysql', 'host', 'database', 'username', 'port', 'password', 'disk')->byDefault();

        $this->filesystem = Mockery::mock('Illuminate\Contracts\Filesystem\Factory');
        $this->filesystem->shouldReceive('disk->put')->byDefault();

        $this->process = Mockery::mock('Symfony\Component\Process\Process')->shouldIgnoreMissing()->byDefault();
        $this->process->shouldReceive('isSuccessful')->andReturn(true)->byDefault();
   

        $this->output = Mockery::mock('Illuminate\Console\OutputStyle');
        $this->output->shouldReceive('writeln')->byDefault();

        $this->command = new DatabaseBackupCommand($this->config, $this->filesystem, $this->process, $this->output);
    }


    public function testMysql()
    {
        $this->config->shouldReceive('get')->andReturn('mysql', 'host', 'database', 'username', 'port', 'password', 'disk');
        $this->process->shouldReceive('setCommandLine')->once()->with('mysqldump -h host -u username -P port -p password database > database_' . time() . '.sql');
        
        $this->command->handle();
    
    }

    public function testPostgres()
    {
        $this->config->shouldReceive('get')->andReturn('pgsql', 'host', 'database', 'username', 'port', 'password', 'disk');
        $this->process->shouldReceive('setCommandLine')->once()->with('pg_dump -h host -U username -p port database > database_' . time() . '.sql');
        $this->process->shouldReceive('run')->once()->with(null, ['PGPASSWORD' => 'password']);
        
        $this->command->handle();
    
    }

    public function testSqlite()
    {
        $this->config->shouldReceive('get')->andReturn('sqlite', 'host', 'database', 'username', 'port', 'password', 'disk');
        $this->filesystem->shouldReceive('disk->put')->once()->with('database_' . time() . '.sqlite', Mockery::any());
        
        $this->command->handle();
    
    }

    public function testInvalidConnection()
    {
        $this->config->shouldReceive('get')->andReturn('krneki', 'host', 'database', 'username', 'port', 'password', 'disk');
        
        $this->expectException(InvalidArgumentException::class);
        
        $this->command->handle();
    }

    public function testProcessFail()
    {
        $this->process->shouldReceive('isSuccessful')->andReturn(false);

        $this->expectException(ProcessFailedException::class);

        $this->command->handle();
    }
}