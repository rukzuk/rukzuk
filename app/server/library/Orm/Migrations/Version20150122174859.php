<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * add pagetype and pageattributes fields to page table
 */
class Version20150122174859 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $pageTable = $schema->getTable('page');

        // create new column pagetype
        $pageTable
          ->addcolumn('pagetype', 'string', array('length' => 255))
          ->setNotnull(false);
        // create new column pageattributes
        $pageTable
          ->addcolumn('pageattributes', 'text')
          ->setNotnull(false);
    }

    public function down(Schema $schema)
    {
        $pageTable = $schema->getTable('page');

        // remove pagetype column from page table
        $pageTable->dropColumn('pagetype');
        // remove pageattributes column from page table
        $pageTable->dropColumn('pageattributes');
    }
}
