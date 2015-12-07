<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130614120447 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // create new website creationmode and ismarkedfordeletion column
    $schema->getTable('website')
           ->addColumn('share', 'text')
           ->setNotnull(false);
    $schema->getTable('website')
           ->addColumn('creationmode', 'string', array('length' => 20, 'default' => 'full'));
    $schema->getTable('website')
           ->addColumn('ismarkedfordeletion', 'boolean', array('default' => '0'));
  }

  public function down(Schema $schema)
  {
    // remove website creationmode and ismarkedfordeletion column
    $schema->getTable('website')->dropColumn('share');
    $schema->getTable('website')->dropColumn('creationmode');
    $schema->getTable('website')->dropColumn('ismarkedfordeletion');
  }
}
