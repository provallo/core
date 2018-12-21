<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Plugin\Bootstrap;
use ProVallo\Components\Plugin\Instance;
use ProVallo\Components\Plugin\Manager;
use ProVallo\Components\Plugin\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PluginUpdateListCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:updates')
            ->setDescription('Checks all plugins for updates and list those.');
        
        $this->addOption('apply', null, InputOption::VALUE_REQUIRED, 'Apply updates');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager($this->models());
        $updater = new Updater();
        $plugins = $manager->list();
        
        $output->write('Please wait...');
        
        /** @var \ProVallo\Components\Plugin\Updater\Update $availableUpdates */
        $availableUpdates = [];
        
        foreach ($plugins as $plugin)
        {
            $update = $updater->checkForUpdate($plugin);
            
            if ($update instanceof Updater\Update)
            {
                $availableUpdates[] = $update;
            }
            
            $output->write('.');
        }
        
        $output->write("\n");
        
        if (empty($availableUpdates))
        {
            $output->writeln('No updates available.');
            
            return;
        }
        
        if ($pluginName = $input->getOption('apply'))
        {
            $this->updatePlugin($pluginName, $output);
            return;
        }
        
        /**
         * @var integer                                    $i
         * @var \ProVallo\Components\Plugin\Updater\Update $update
         */
        foreach ($availableUpdates as $i => $update)
        {
            $name           = $update->getPlugin()->getName();
            $currentVersion = $update->getPlugin()->getInfo()->getVersion();
            $newVersion     = $update->getVersion();
            
            $output->writeln(sprintf(
                '  %d. %s (%s => %s)',
                $i + 1,
                $name,
                $currentVersion,
                $newVersion
            ));
        }

    }
    
    protected function updatePlugin ($name, OutputInterface $output)
    {
        $manager = new Manager($this->models());
        $updater = new Updater();
    
        $plugin = $manager->loadInstance($name);
    
        if (!($plugin instanceof Instance))
        {
            $output->writeln('Plugin by name not found.');
        
            return;
        }
    
        $update = $updater->checkForUpdate($plugin);
    
        if (!($update instanceof Updater\Update))
        {
            $output->writeln('No updates available.');
        
            return;
        }
    
        $output->writeln('Downloading version ' . $update->getVersion() . ' ...');
        $filename = $update->download();
    
        $output->writeln('Extracting update files ...');
        $update->extract($filename);
        
        $output->writeln('Installing update...');
        $manager->update($name);
    }
    
}