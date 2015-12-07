<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * add pagetype field to template table
 */
class Version20150121191044 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // create new column pagetype
    $schema->getTable('template')
      ->addcolumn('pagetype', 'string', array('length' => 255))
      ->setNotnull(false);
  }

  public function down(Schema $schema)
  {
    // create pagetype column from template table
    $schema->getTable('template')->dropColumn('pagetype');
  }
}
