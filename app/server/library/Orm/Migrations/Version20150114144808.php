<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * create website_settings table
 */
class Version20150114144808 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        // create website_settings table
        $websiteSettingsTable = $schema->createTable('website_settings');

        $websiteSettingsTable
          ->addcolumn('websiteid', 'string', array('length' => 100))
          ->setNotnull(true);
        $websiteSettingsTable
          ->addcolumn('id', 'string', array('length' => 255))
          ->setNotnull(true);
        $websiteSettingsTable
          ->addcolumn('formValues', 'text')
          ->setNotnull(false);
        $websiteSettingsTable
          ->addColumn('lastupdate', 'timestamponupdate')
          ->setNotnull(true);

        $websiteSettingsTable->setPrimaryKey(array('websiteid', 'id'));
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        // remove website_settings table
        $schema->dropTable('website_settings');
    }
}
