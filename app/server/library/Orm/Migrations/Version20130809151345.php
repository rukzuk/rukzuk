<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130809151345 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->addSql('UPDATE `user` SET `language` = "de"');
  }

  public function down(Schema $schema)
  {
    $this->addSql('UPDATE `user` SET `language` = NULL');
  }
}
