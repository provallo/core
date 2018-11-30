<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Plugin\Instance;
use ProVallo\Components\Plugin\Manager;
use ProVallo\Components\Plugin\Updater;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateListCommand extends Command
{

    public function configure ()
    {
        $this->setName('plugin:update:list')
            ->setDescription('Checks all plugins for updates and list those.');
    }

    public function execute (InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager($this->models());
        $updater = new Updater();
        $plugins = $manager->list();

        $output->write('Please wait...');

        $availableUpdates = [];

        foreach ($plugins as $plugin)
        {
            $update = $updater->checkForUpdate($plugin);
            
            if ($update !== null)
            {
                $availableUpdates[] = compact('plugin', 'update');
            }
            
            $output->write('.');
        }
        
        $output->write("\n");
        
        if (empty($availableUpdates))
        {
            $output->writeln('No updates available.');
            return;
        }
        
        
    }

}