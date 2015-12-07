<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140630173322 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // create new website column shortid
    $schema->getTable('website')
      ->addcolumn('shortid', 'string', array('length' => 10))
      ->setNotnull(true);
  }

  public function down(Schema $schema)
  {
    // remove shortid column from website table
    $schema->getTable('website')->dropColumn('shortid');
  }
}
