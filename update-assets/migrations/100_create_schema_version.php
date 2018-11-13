<?php

namespace ProVallo\Migrations;

use ProVallo\Components\Database\Migration;

class Migration_100 extends Migration
{
    
    public function up ()
    {
        $this->addSQL('
            CREATE TABLE `schema_version` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `version` int(11) NOT NULL,
              `startDate` datetime DEFAULT NULL,
              `endDate` datetime DEFAULT NULL,
              `error` longtext,
              `pluginID` int(11) NOT NULL DEFAULT \'-1\',
              PRIMARY KEY (`id`),
              UNIQUE KEY `version` (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
    }
    
    public function down ()
    {
        // TODO: Implement down() method.
    }
    
}