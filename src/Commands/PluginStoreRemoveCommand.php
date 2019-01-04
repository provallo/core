<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;

class PluginStoreRemoveCommand extends Command
{

    protected function configure ()
    {
        $this->setName('plugin:store:remove');
        $this->setDescription('Uninstalls and removes a plugin completely.');
    }
    
}