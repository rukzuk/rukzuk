<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130701121736 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->addSql(
        'ALTER TABLE `user` CHANGE `gender` `gender` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL'
    );
  }

  public function down(Schema $schema)
  {
    $this->addSql(
        'UPDATE `user` SET `gender` = "m" WHERE `gender` IS NULL OR `gender` = ""'
    );
    $this->addSql(
        'ALTER TABLE `user` CHANGE `gender` `gender` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL'
    );
  }
}
