<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130731201026 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $schema->getTable('user')
             ->addColumn('language', 'string', array('length' => 10))
             ->setNotnull(false);
    }

    public function down(Schema $schema)
    {
       $schema->getTable('user')->dropColumn('language');
    }
}
