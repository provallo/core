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
        
        $this->addOption('install', null, InputOption::VALUE_NONE, 'Automatically installs the update after download.');
    }

    public function execute (InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        
        $manager = new Manager($this->models());
        $updater = new Updater();
        
        $output->writeln('Please wait...');

        $plugin = $manager->get($name);
        
        if (!($plugin instanceof Bootstrap))
        {
            $output->writeln('Plugin by name not found.');
            return;
        }
        
        $update = $updater->checkForUpdate($plugin->getInstance());
        
        if (!($update instanceof Updater\Update))
        {
            $output->writeln('No updates available.');
            return;
        }
        
        $output->writeln('Downloading version ' . $update->getVersion() . ' ...');
        $filename = $update->download();
        
        $output->writeln('Extracting update files ...');
        $update->extract($filename);
    }

}