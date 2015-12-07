<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Activate publishing for all websites
 */
class Version20140905130040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

      // activate publishing for all websites
      $this->addSql('UPDATE website SET publishingenabled = :publishingenabled', array(
        'publishingenabled' => 1,
      ));
    }

    public function down(Schema $schema)
    {
        // nothing to do
    }
}
