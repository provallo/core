<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Job\JobInterface;
use ProVallo\Components\Job\JobRunner;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstallCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:install')
            ->setDescription('Install a plugin.');
        
        $this->addArgument('name', InputArgument::REQUIRED, 'The case-sensitive name of the plugin.');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $name   = trim($input->getArgument('name'));
        $result = Core::plugins()->install($name);
        
        if ($result->isSuccess())
        {
            $output->writeln('The plugin were installed successfully.');
            
            if ($result->hasJobs())
            {
                $output->writeln('Running post jobs ...');
                
                $runner = new JobRunner($output);
                $runner->run($result->getJobs());
            }
        }
        else
        {
            $output->writeln($result->getMessage());
        }
    }
    
}