<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * add publishingenabled field to website db
 */
class Version20140905125022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

      // create new website column publishingenabled
      $schema->getTable('website')
        ->addcolumn('publishingenabled', 'boolean')
        ->setDefault(0)
        ->setNotnull(false);
    }

    public function down(Schema $schema)
    {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

      // remove publishingenabled column from website table
      $schema->getTable('website')->dropColumn('publishingenabled');
    }
}
