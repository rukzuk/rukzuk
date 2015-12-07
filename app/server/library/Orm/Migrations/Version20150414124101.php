<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * remove share column from website table
 */
class Version20150414124101 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // remove share column
    $schema->getTable('website')->dropColumn('share');
  }

  public function down(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // create column share
    $schema->getTable('website')->addColumn('share', 'text')->setNotnull(false);
  }
}
