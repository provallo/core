<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Plugin\Instance;
use ProVallo\Components\Plugin\Manager;
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

        $plugins = $manager->list();
        $table   = new Table($output);
        $table->setHeaders([
            'Name',
            'Label',
            'Version',
            'Created',
            'Updated',
            'Active/Installed'
        ]);

        /** @var Instance $plugin */
        foreach ($plugins as $plugin)
        {
            $table->addRow([
                $plugin->getName(),
                $plugin->getInfo()->getLabel(),
                $plugin->getModel()->version,
                $plugin->getModel()->created,
                $plugin->getModel()->changed,
                $plugin->getModel()->active ? 'Yes' : 'No'
            ]);
        }

        $output->writeln(count($plugins) . ' records found');
        $table->render();
    }

}