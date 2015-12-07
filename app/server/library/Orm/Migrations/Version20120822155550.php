<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120822155550 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      // Neue Modul-Attribute aufnehmen
      $schemaTo = clone $schema;
      $schemaTo->getTable('modul')
        ->addColumn('version', 'string', array('length' => 255))
        ->setNotnull(false);
      $schemaTo->getTable('modul')
        ->addColumn('moduletype', 'string', array('length' => 255))
        ->setNotnull(true)
        ->setDefault('default');
      $schemaTo->getTable('modul')
        ->addColumn('allowedchildmoduletype', 'string', array('length' => 255))
        ->setNotnull(true)
        ->setDefault('*');
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );


      // Basismodule-Typ uebernehmen
      $this->addSql(
          "UPDATE `modul` m SET m.`moduletype` = 'root' WHERE m.`isrootmodule` = 1"
      );

      
      // Nicht mehr benoetigte Felder entfernen
      $schemaTo = clone $schema;
      $schemaTo->getTable('modul')
        ->dropColumn('isrootmodule')
        ->dropColumn('allowedchildmodules');
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );
    }

    public function down(Schema $schema)
    {
      // Alte Modul-Attribute aufnehmen
      $schemaTo = clone $schema;
      $schemaTo->getTable('modul')
        ->addColumn('isrootmodule', 'boolean')
        ->setNotnull(false);
      $schemaTo->getTable('modul')
        ->addColumn('allowedchildmodules', 'text')
        ->setNotnull(false);
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );

      // Basismodule Flag uebernehmen und verfuegbare Kind-Module setzen
      $this->addSql(
          array(
          "UPDATE `modul` m SET m.`isrootmodule` = 0",
          "UPDATE `modul` m SET m.`isrootmodule` = 1 WHERE m.`moduletype` = 'root'",
          "UPDATE `modul` m SET m.`allowedchildmodules` = '[]'",
          )
      );
      
      // Neue Felder entfernen
      $schemaTo = clone $schema;
      $schemaTo->getTable('modul')
        ->dropColumn('version')
        ->dropColumn('moduletype')
        ->dropColumn('allowedchildmoduletype');
      $this->addSql(
          $schemaTo->getMigrateFromSql($schema, $this->platform)
      );
    }
}
