<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;

class PluginStoreInstallCommand extends Command
{

    protected function configure ()
    {
        $this->setName('plugin:store:install');
        $this->setDescription('Installs a new plugin from the store.');
    }
    
}