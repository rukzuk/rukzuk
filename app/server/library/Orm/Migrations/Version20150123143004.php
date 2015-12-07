<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * set default pageType 'page' into all pages and templates
 */
class Version20150123143004 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // set pageType 'page' to all templates
    $this->addSql('UPDATE template SET pagetype = :pagetype', array(
      'pagetype' => 'page',
    ));
    // set pageType 'page' to all pages
    $this->addSql('UPDATE page SET pagetype = :pagetype', array(
      'pagetype' => 'page',
    ));
  }

  public function down(Schema $schema)
  {
    // no migration needed
  }
}
