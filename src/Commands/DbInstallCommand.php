<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Database\MigrationRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbInstallCommand extends Command
{
    
    protected $force = false;
    
    public function configure ()
    {
        $this->setName('db:install');
        $this->setDescription('Installs the required database tables');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $directory = realpath(__DIR__ . '/../../update-assets/migrations/');
        $runner    = new MigrationRunner(-1, $directory);
        $runner->setOutput($output);
        $runner->setNamespace('ProVallo\\Migrations\\');
        
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