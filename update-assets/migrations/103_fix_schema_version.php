<?php

namespace ProVallo\Migrations;

use ProVallo\Components\Database\Migration;

class Migration_103 extends Migration
{
    
    public function up ()
    {
        $this->addSQL('
            ALTER TABLE `schema_version` DROP INDEX `version`;
        ');
    }
    
    public function down ()
    {
        // TODO: Implement down() method.
    }
    
}