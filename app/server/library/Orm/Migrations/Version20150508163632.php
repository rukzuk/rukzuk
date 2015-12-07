<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * remove modulerepository column from website table
 */
class Version20150508163632 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // remove modulerepository column
    $schema->getTable('website')->dropColumn('modulerepository');
  }

  public function down(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // create column modulerepository
    $schema->getTable('website')->addColumn('modulerepository', 'text')->setNotnull(false);
  }
}
