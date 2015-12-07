<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Hinzufuegen der Lock-Tabelle
 *
 * @package      Orm
 * @subpackage   Migrations
 */
class Version20120221135005 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $lockTable = $schema->createTable('locks');
      
      $userIdColumn = $lockTable->addcolumn('userid', 'string', array('length' => 100));
      $userIdColumn->setNotnull(true);
      $runIdColumn = $lockTable->addcolumn('runid', 'string', array('length' => 100));
      $runIdColumn->setNotnull(true);
      $itemIdColumn = $lockTable->addcolumn('itemid', 'string', array('length' => 100));
      $itemIdColumn->setNotnull(true);
      $websiteIdColumn = $lockTable->addcolumn('websiteid', 'string', array('length' => 100));
      $websiteIdColumn->setNotnull(true);
      $typeIdColumn = $lockTable->addcolumn('type', 'string', array('length' => 100));
      $typeIdColumn->setNotnull(true);
      $startTimeColumn = $lockTable->addcolumn('starttime', 'string', array('length' => 20));
      $startTimeColumn->setNotnull(true);
      $lastActivityColumn = $lockTable->addcolumn('lastactivity', 'string', array('length' => 20));
      $lastActivityColumn->setNotnull(true);
      
      $lockTable->setPrimaryKey(array('websiteid', 'itemid', 'type'));
    }

    public function down(Schema $schema)
    {
      $schema->dropTable('locks');
    }
}
