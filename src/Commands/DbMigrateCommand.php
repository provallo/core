<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Database\MigrationRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbMigrateCommand extends Command
{
    
    protected $force = false;
    
    public function configure ()
    {
        $this->setName('db:migrate');
        $this->setDescription('Run db migrations');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $directory = realpath(__DIR__ . '/../../update-assets/migrations/');
        $runner    = new MigrationRunner(-1, $directory);
        $runner->setOutput($output);
        
        $result = $runner->run();
        
        if (isSuccess($result))
        {
            $output->writeln($result['total'] . ' migrations were executed');
        }
        else
        {
            throw $result['exception'];
        }
    }
    
}