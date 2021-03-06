<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Plugin\Instance;
use ProVallo\Components\Plugin\Manager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginListCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:list')
            ->setDescription('Lists all available plugins.');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager($this->models());
        
        $manager->synchronize();
        
        $plugins = $manager->list();
        $table   = new Table($output);
        $table->setHeaders([
            'Name',
            'Current Version',
            'Installed'
        ]);
        
        /** @var Instance $plugin */
        foreach ($plugins as $plugin)
        {
            $table->addRow([
                $plugin->getName(),
                $plugin->getModel()->version,
                $plugin->getModel()->active ? 'Yes' : 'No'
            ]);
        }
        
        $output->writeln(count($plugins) . ' records found');
        $table->render();
    }
    
}