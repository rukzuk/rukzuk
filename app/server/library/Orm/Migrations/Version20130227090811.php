<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Hinzufuegen der TemplateSnippet-Tabelle
 *
 * @package      Orm
 * @subpackage   Migrations
 */
class Version20130227090811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $snippetTable = $schema->createTable('template_snippet');
      
      $snippetTable
          ->addcolumn('websiteid', 'string', array('length' => 100))
          ->setNotnull(true);
      $snippetTable
          ->addcolumn('id', 'string', array('length' => 100))
          ->setNotnull(true);
      $snippetTable
          ->addcolumn('name', 'string', array('length' => 255))
          ->setNotnull(true);
      $snippetTable
          ->addcolumn('description', 'text')
          ->setNotnull(false);
      $snippetTable
          ->addcolumn('category', 'text')
          ->setNotnull(false);
      $snippetTable
          ->addcolumn('content', 'text')
          ->setNotnull(false);
      $snippetTable
          ->addColumn('lastupdate', 'timestamponupdate')
          ->setNotnull(true);
      
      $snippetTable->setPrimaryKey(array('websiteid', 'id'));
    }

    public function down(Schema $schema)
    {
      $schema->dropTable('template_snippet');
    }
}
