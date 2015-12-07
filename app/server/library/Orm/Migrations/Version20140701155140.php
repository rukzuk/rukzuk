<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140701155140 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // update publish column for all websites; rename mode into protocol
    $allWebsites = $this->getAllWebsites();
    foreach ($allWebsites as $website) {
      $publishConfig = $this->getPublishConfig($website);
      $this->renameProperties($publishConfig, array('mode' => 'protocol'));
      if ($this->isPublishTypeExternal($publishConfig)) {
        $publishConfig->type = 'external';
      } else {
        $publishConfig = (object)array(
          'type' => 'internal',
          'cname' => '',
        );
      }
      $this->updatePublishConfig($website['id'], $publishConfig);
    }
  }

  public function down(Schema $schema)
  {
    // update publish column for all websites; rename protocol into mode
    $allWebsites = $this->getAllWebsites();
    foreach ($allWebsites as $website) {
      $publishConfig = $this->getPublishConfig($website);
      $publishConfig = $this->renameProperties($publishConfig, array('protocol' => 'mode'));
      if (property_exists($publishConfig, 'type')) {
        unset($publishConfig->type);
      }
      if (property_exists($publishConfig, 'cname')) {
        unset($publishConfig->cname);
      }
      $this->updatePublishConfig($website['id'], $publishConfig);
    }
  }

  private function getAllWebsites()
  {
    $stmt = $this->connection->query("SELECT id, name, publish FROM website");
    return $stmt->fetchAll();
  }

  /**
   * @param array $website
   *
   * @return array
   */
  protected function getPublishConfig($website)
  {
    $publishConfig = json_decode($website['publish'], false);
    if (!is_object($publishConfig)) {
      return new \stdClass();
    } else {
      return $publishConfig;
    }
  }

  /*
   * check if protocol is ftp or sftp and host is set
   */
  private function isPublishTypeExternal($publishConfig)
  {
    if (!is_object($publishConfig)) {
      return false;
    }

    if (property_exists($publishConfig, 'type') && $publishConfig->type !== 'external') {
      return false;
    }

    if (!property_exists($publishConfig, 'host') || empty($publishConfig->host)) {
      return false;
    }

    if (!property_exists($publishConfig, 'username') || empty($publishConfig->username)) {
      return false;
    }

    // external type detected
    return true;
  }

  private function renameProperties($publishConfig, array $searchReplace)
  {
    if (!is_object($publishConfig)) {
      return $publishConfig;
    }
    foreach ($searchReplace as $search => $replace) {
      if (property_exists($publishConfig, $search)) {
        $publishConfig->$replace = $publishConfig->$search;
        unset($publishConfig->$search);
      }
    }
    return $publishConfig;
  }

  private function updatePublishConfig($websiteId, $publishConfig)
  {
    $this->addSql('UPDATE website SET publish = :publish WHERE id = :id', array(
      'id' => $websiteId,
      'publish' => json_encode($publishConfig),
    ));
  }
}
