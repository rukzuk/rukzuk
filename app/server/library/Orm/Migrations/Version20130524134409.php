<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130524134409 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $publishingDir = $this->getPublishingDirectory();
    if (!file_exists($publishingDir) && !is_dir($publishingDir)) {
      mkdir($publishingDir);
    }
  }

  public function down(Schema $schema)
  {
  }

  private function getPublishingDirectory()
  {
    return CMS_PATH . DIRECTORY_SEPARATOR . 'publishing';
  }
}
