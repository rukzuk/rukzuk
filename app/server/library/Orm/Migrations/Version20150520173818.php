<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * switch fields lastupdate from type timestamponupdate to bigint
 */
class Version20150520173818 extends AbstractMigration
{

  protected $tablesWithLastupdateColumn = array(
    'album', 'user_group', 'media_item', 'page', 'template', 'template_snippet', 'user',
    'website', 'website_settings',
  );

  /**
   * @param Schema $schema
   */
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // change lastupdate columns to bigint
    $schemaTo = clone $schema;
    foreach ($this->tablesWithLastupdateColumn as $tableName) {
      $schemaTo->getTable($tableName)->changeColumn('lastupdate', array(
        'type' => Type::getType('bigint'),
        'default' => 0,
      ));
    }
    $this->addSql($schemaTo->getMigrateFromSql($schema, $this->platform));

    // update lastupdate columns to now
    foreach ($this->tablesWithLastupdateColumn as $tableName) {
      $this->addSql(sprintf('UPDATE `%s` SET `lastupdate` = UNIX_TIMESTAMP(NOW())', $tableName));
    }
  }

  /**
   * @param Schema $schema
   */
  public function down(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // change lastupdate columns to timestamp
    $schemaTo = clone $schema;
    foreach ($this->tablesWithLastupdateColumn as $tableName) {
      $schemaTo->getTable($tableName)->changeColumn('lastupdate', array(
        'type' => Type::getType('datetime'),
        'columnDefinition' => "timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP",
      ));
    }
    $this->addSql($schemaTo->getMigrateFromSql($schema, $this->platform));

    // update lastupdate columns to now
    foreach ($this->tablesWithLastupdateColumn as $tableName) {
      $this->addSql(sprintf('UPDATE `%s` SET `lastupdate` = NOW()', $tableName));
    }
  }
}
