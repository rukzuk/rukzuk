<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130731181324 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $schema->getTable('action_log')
             ->addColumn('additionalinfo', 'text')
             ->setNotnull(false);
    }

    public function down(Schema $schema)
    {
      $schema->getTable('action_log')->dropColumn('additionalinfo');
    }
}
