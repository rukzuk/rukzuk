<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20111208145245 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->addSql(
        'ALTER TABLE user_opt_in DROP INDEX UNIQ_49682529F132696E'
    );
  }

  public function down(Schema $schema)
  {
    $this->addSql(
        'ALTER TABLE `user_opt_in` ADD INDEX UNIQ_49682529F132696E (`userid`)'
    );
  }
}
