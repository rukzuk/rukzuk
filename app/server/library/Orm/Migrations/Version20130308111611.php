<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130308111611 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // MED items with file extension svg should have media type 'image'
        $this->addSql(
            "UPDATE `media_item` mdb SET mdb.`type` = 'image' WHERE mdb.`extension` = 'svg'"
        );
        
    }

    public function down(Schema $schema)
    {
        // do nothing
    }
}
