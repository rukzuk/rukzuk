<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150309172442 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    $pageTable = $schema->getTable('page');
    $pageTable->getColumn('mediaid')->setNotnull(false);
  }

  public function down(Schema $schema)
  {
    // do nothing
  }
}
