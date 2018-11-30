<?php

namespace ProVallo\Migrations;

use ProVallo\Components\Database\Migration;

class Migration_102 extends Migration
{
    
    public function up ()
    {
        $this->addSQL('
          ALTER TABLE `plugin` DROP COLUMN `namespace`;
        ');
    }
    
    public function down ()
    {
        // TODO: Implement down() method.
    }
    
}