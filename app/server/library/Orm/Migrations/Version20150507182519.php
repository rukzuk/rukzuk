<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * migrate module repository name to usedsetid
 */
class Version20150507182519 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // update usedsetid column for all websites
    $allWebsites = $this->getAllWebsites();
    foreach ($allWebsites as $website) {
      $usedSetId = $this->getUsedSetIdFromModuleRepository($website);
      $this->updateUsedSetId($website['id'], $usedSetId);
    }
  }

  public function down(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // update modulerepository column for all websites
    $allWebsites = $this->getAllWebsites();
    foreach ($allWebsites as $website) {
      if (!isset($website['usedsetid']) || empty($website['usedsetid'])) {
        continue;
      }
      $moduleRepositoryJson = json_encode(array(
        'id' => $website['usedsetid'],
        'version' => 4,
      ));
      $this->updateModuleRepository($website['id'], $moduleRepositoryJson);
    }
  }

  /**
   * @param array $website
   *
   * @return string|null
   */
  protected function getUsedSetIdFromModuleRepository($website)
  {
    if (!isset($website['modulerepository']) || empty($website['modulerepository'])) {
      return null;
    }
    $repoConfig = json_decode($website['modulerepository'], true);
    if (!is_array($repoConfig) || !isset($repoConfig['id']) || empty($repoConfig['id'])) {
      return null;
    }
    return $repoConfig['id'];
  }

  /**
   * @return array
   */
  private function getAllWebsites()
  {
    $stmt = $this->connection->query("SELECT id, name, modulerepository, usedsetid FROM website");
    return $stmt->fetchAll();
  }

  /**
   * @param string $websiteId
   * @param string $usedSetId
   */
  private function updateUsedSetId($websiteId, $usedSetId)
  {
    $this->addSql('UPDATE website SET usedsetid = :usedsetid WHERE id = :id', array(
      'id' => $websiteId,
      'usedsetid' => $usedSetId,
    ));
  }

  /**
   * @param string $websiteId
   * @param string $moduleRepositoryJson
   */
  private function updateModuleRepository($websiteId, $moduleRepositoryJson)
  {
    $this->addSql('UPDATE website SET modulerepository = :modulerepository WHERE id = :id', array(
      'id' => $websiteId,
      'modulerepository' => $moduleRepositoryJson,
    ));
  }
}
