<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20131112182749 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // create new template column usedmoduleids
    $schema->getTable('template')
           ->addColumn('usedmoduleids', 'text');
  }

  public function down(Schema $schema)
  {
    $schema->getTable('template')->dropColumn('usedmoduleids');
  }
}
