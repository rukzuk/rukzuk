<?php
namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Hinzufuegen last_update-Spalte mit automatischen Timestamp bei Tabellen,
 * welche Units abbilden
 *
 * @package      Orm
 * @subpackage   Migrations
 */
class Version20120119112843 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $schema->getTable('album')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('media_item')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('modul')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('page')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('template')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('user')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('user_group')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
    $schema->getTable('website')
           ->addColumn('lastupdate', 'timestamponupdate')
           ->setNotnull(true);
  }

  public function down(Schema $schema)
  {
    $schema->getTable('album')->dropColumn('lastupdate');
    $schema->getTable('media_item')->dropColumn('lastupdate');
    $schema->getTable('modul')->dropColumn('lastupdate');
    $schema->getTable('page')->dropColumn('lastupdate');
    $schema->getTable('template')->dropColumn('lastupdate');
    $schema->getTable('user')->dropColumn('lastupdate');
    $schema->getTable('user_group')->dropColumn('lastupdate');
    $schema->getTable('website')->dropColumn('lastupdate');
  }
}
