<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Added module repository field to website
 */
class Version20140403145034 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // create new template column usedmoduleids
    $schema->getTable('website')
      ->addColumn('modulerepository', 'text')->setNotnull(false);
  }

  public function down(Schema $schema)
  {
    $schema->getTable('website')->dropColumn('modulerepository');
  }
}
