<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Job\JobRunner;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUninstallCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:uninstall')
            ->setDescription('Uninstall a plugin.');
        
        $this->addArgument('name', InputArgument::REQUIRED, 'The case-sensitive name of the plugin.');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $name   = trim($input->getArgument('name'));
        $result = Core::plugins()->uninstall($name);
        
        if ($result->isSuccess())
        {
            $output->writeln('The plugin were uninstalled successfully.');
    
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