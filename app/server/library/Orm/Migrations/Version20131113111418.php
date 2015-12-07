<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20131113111418 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // update usedmoduleids column from all templates
    $stmt = $this->connection->query("SELECT * FROM template");
    while ($template = $stmt->fetch()) {
      $this->updateUsedModule($template);
    }
  }

  public function down(Schema $schema)
  {
     // do nothing
  }
  
  protected function updateUsedModule($template)
  {
    $usedModuleIdsString = $this->getUsedModuleIdsStringFromContent(
        $this->convertContentStringToArray($template['content'])
    );
    $this->connection->executeUpdate(
        'UPDATE template SET usedmoduleids = :usedmoduleids WHERE websiteid = :websiteid AND id = :id',
        array(
        'usedmoduleids' => $usedModuleIdsString,
        'websiteid' => $template['websiteid'],
        'id' => $template['id'],
        )
    );
  }
  
  protected function getUsedModuleIdsStringFromContent($content)
  {
    $usedModuleIds = array();
    $this->getUsedModuleIdsFromContent($content, $usedModuleIds);
    return json_encode(array_keys($usedModuleIds));
  }
  
  protected function convertContentStringToArray($contentString)
  {
    if (empty($contentString)) {
      return array();
    }
    return json_decode($contentString, true);
  }
  
  protected function getUsedModuleIdsFromContent($content, &$usedModuleIds)
  {
    if (!is_array($content)) {
      return;
    }
    foreach ($content as $unit) {
      if (is_array($unit) && isset($unit['moduleId'])) {
        $usedModuleIds[$unit['moduleId']] = true;
      }
      if (is_array($unit) && isset($unit['children'])) {
        $this->getUsedModuleIdsFromContent($unit['children'], $usedModuleIds);
      }
    }
  }
}
