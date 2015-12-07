<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130318115907 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      // create new website resolutions column
      $schemaTo = clone $schema;
      $schemaTo->getTable('website')
        ->addColumn('resolutions', 'text')
        ->setNotnull(false);
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );
    }

    public function down(Schema $schema)
    {
      // delete resolutions column
      $schemaTo = clone $schema;
      $schemaTo->getTable('website')
        ->dropColumn('resolutions');
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );
    }
}
