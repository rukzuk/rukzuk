<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140630182832 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // update shortid column for all websites
    $stmt = $this->connection->query("SELECT id, name FROM website");
    $allWebsites = $stmt->fetchAll();
    $allShortIds = array();
    foreach ($allWebsites as $website) {
      $shortId = $this->createShortId($website['id'], $allShortIds);
      $allShortIds[] = $shortId;
      $this->updateShortId($website['id'], $shortId);
    }
  }

  public function down(Schema $schema)
  {
    // do nothing
  }

  protected function createShortId($websiteId, $allShortIds)
  {
    $secCounter = pow(36, 4);
    do {
      $shortId = base_convert(mt_rand(36, pow(36, 4)-1), 10, 36);
      if (!in_array($shortId, $allShortIds)) {
        return $shortId;
      }
    } while (--$secCounter < 0);
    throw new \Exception('error at creating short id for website id '.$websiteId, -1);
  }

  protected function updateShortId($websiteId, $shortId)
  {
    $this->addSql('UPDATE website SET shortid = :shortid WHERE id = :id', array(
      'id' => $websiteId,
      'shortid' => $shortId,
    ));
  }
}
