<?php

namespace Medvesekg\DatabaseBackups\Commands;

use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use \Mockery as Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;


class CleanOldDatabaseBackupsCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $command;
    private $config;
    private $filesystem;
    private $output;

    public function setUp() : void
    {

        $this->config = Mockery::mock('Illuminate\Contracts\Config\Repository');
        $this->config->shouldReceive('get')->andReturn('disk', 'retention')->byDefault();

        $this->filesystem = Mockery::mock('Illuminate\Contracts\Filesystem\Factory');
        $this->filesystem->shouldReceive('disk->allFiles')->byDefault();


        $this->output = Mockery::mock('Illuminate\Console\OutputStyle');
        $this->output->shouldReceive('writeln')->byDefault();

        $this->command = new CleanOldDatabaseBackupsCommand($this->config, $this->filesystem, $this->output);
    }


    public function testDeleteOldBackups()
    {

        $this->config->shouldReceive('get')->andReturn('disk', '1 week');

        $now = time();
        $twoWeeksAgo = Carbon::now()->subWeeks(2)->getTimestamp();

        $this->filesystem->shouldReceive('disk->allFiles')->andReturn(["db_$now.sql", "db_$twoWeeksAgo.sql"]);
        $this->filesystem->shouldReceive('disk->delete')->once();

        $this->command->handle();
    
    }

    public function testDeleteIfNoOldBackups()
    {

        $this->config->shouldReceive('get')->andReturn('disk', '1 week');

        $now = time();

        $this->filesystem->shouldReceive('disk->allFiles')->andReturn(["db_$now.sql"]);
        $this->filesystem->shouldNotReceive('disk->delete');

        $this->command->handle();
    
    }

    public function testNotDeleteOtherFiles()
    {

        $this->config->shouldReceive('get')->andReturn('disk', '1 week');

        $twoWeeksAgo = Carbon::now()->subWeeks(2)->getTimestamp();

        $this->filesystem->shouldReceive('disk->allFiles')->andReturn(["db_$twoWeeksAgo.sql", "someotherfile.jpg", 'something.php']);
        $this->filesystem->shouldReceive('disk->delete')->once();

        $this->command->handle();
    
    }

}