<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * create user_status table
 */
class Version20150130111111 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        // create website_settings table
        $userStatusTable = $schema->createTable('user_status');

        $userStatusTable
          ->addcolumn('userid', 'string', array('length' => 255))
          ->setNotnull(true);

        $userStatusTable
          ->addColumn('lastlogin', 'datetime')
          ->setNotnull(true);

        $userStatusTable
            ->addColumn('authbackend', 'string', array('length' => 100));

        $userStatusTable->setPrimaryKey(array('userid', 'authbackend'));
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        // remove website_settings table
        $schema->dropTable('user_status');
    }
}
