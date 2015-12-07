<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Added used set id column to website
 */
class Version20150507121237 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    $websiteTable = $schema->getTable('website');

    // create new column usedsetid
    $websiteTable
      ->addcolumn('usedsetid', 'string', array('length' => 100))
      ->setNotnull(false);
  }

  public function down(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    $websiteTable = $schema->getTable('website');

    // remove usedsetid column from website table
    $websiteTable->dropColumn('usedsetid');
  }
}
