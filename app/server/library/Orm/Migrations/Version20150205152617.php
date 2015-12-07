<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * set last login users
 */
class Version20150205152617 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

    // get owner id and auth backend
    list($ownerId, $authBackend)  = $this->getOwnerIdAndAuthBackend();

    // insert last login
    $this->addSql('INSERT INTO user_status SET userid = :userId, authbackend = :authBackend, lastlogin = NOW() ON DUPLICATE KEY UPDATE lastlogin = NOW();', array(
      ':userId' => $ownerId,
      ':authBackend' => $authBackend,
    ));

  }

  public function down(Schema $schema)
  {
    // do nothing
  }

  /**
   * @return array ['<USER_ID>', '<AUTH_BACKEND>']
   */
  protected function getOwnerIdAndAuthBackend()
  {
    $ownerId = $this->getOwnerIdFromMetaFile();
    if (empty($ownerId) || !is_string($ownerId)) {
      return array('fake-user-id-from-migration', 'Fake');
    }
    return array($ownerId, 'Cms');
  }

  /**
   * @return string|null
   */
  protected function getOwnerIdFromMetaFile()
  {
    $metaFile = DOCUMENT_ROOT . '/../meta.json';
    if (!is_readable($metaFile)) {
      return null;
    }

    $metaDataAsJson = @file_get_contents($metaFile);
    $metaData = @json_decode($metaDataAsJson, true);

    if (!isset($metaData['owner']) || !isset($metaData['owner']['id'])) {
      return null;
    }

    return $metaData['owner']['id'];
  }
}
