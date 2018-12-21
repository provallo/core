<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Plugin\Bootstrap;
use ProVallo\Components\Plugin\Manager;
use ProVallo\Components\Plugin\Updater;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:update')
            ->setDescription('Updates a single plugin.');
        
        $this->addArgument('name', InputArgument::REQUIRED, 'The plugin name');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        
        $manager = new Manager($this->models());
        $result  = $manager->update($name);
        
        if (isSuccess($result))
        {
            $output->writeln('The plugin were updated successfully.');
        }
        else
        {
            $output->writeln($result['message']);
        }
    }
    
}