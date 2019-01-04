<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginStoreSearchCommand extends Command
{
    
    protected function configure ()
    {
        $this->setName('plugin:store:search');
        $this->setDescription('Searches for available plugins in the store.');
        
        $this->addArgument('phrase', InputArgument::OPTIONAL, 'The phrase to search for.');
    }
    
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $phrase = $input->getArgument('phrase');
        $store  = Core::di()->get('store');
        
        if (empty($phrase))
        {
            // List all available and supported plugins
            // Yet Savas do not support requirement checks (but this feature is required for optimal performance)
            // Requirements should be saved per file (because a file can be related to different platforms)
            // The requirements should probably automatically be read (by vallo publish) for easier and faster deployment
            // This is also required for the plugin:store:install, plugin:store:remove and of course plugin:update command
            // So we can make sure plugin dependencies always are correct
            
            // Step 1: Request requirements from the store
            // Step 2: Check requirements locally
            // Step 3: Send requirement-check to the store and receive supported plugins
            $items = $store->list();
        }
        else
        {
            // List all matching and supported plugins
    
            // Step 1: Request requirements from the store (by phrase)
            // Step 2: Check requirements locally
            // Step 3: Send requirement-check to the store and receive supported plugins
            $items = $store->search($phrase);
        }
        
        var_dump($items);
    }
    
}