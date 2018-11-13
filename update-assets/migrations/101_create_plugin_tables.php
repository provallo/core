<?php

namespace ProVallo\Migrations;

use ProVallo\Components\Database\Migration;

class Migration_101 extends Migration
{
    
    public function up ()
    {
        $this->addSQL('
            CREATE TABLE `plugin` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `active` tinyint(11) NOT NULL DEFAULT \'0\',
              `namespace` varchar(32) NOT NULL,
              `name` varchar(255) NOT NULL,
              `label` varchar(255) NOT NULL,
              `description` varchar(255) DEFAULT NULL,
              `version` varchar(32) DEFAULT NULL,
              `author` varchar(255) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `website` varchar(255) DEFAULT NULL,
              `changed` datetime NOT NULL,
              `created` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
        
        $this->addSQL('
            CREATE TABLE `plugin_dependency` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `pluginID` int(11) NOT NULL,
              `name` varchar(32) NOT NULL,
              `version` varchar(32) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
    }
    
    public function down ()
    {
        // TODO: Implement down() method.
    }
    
}