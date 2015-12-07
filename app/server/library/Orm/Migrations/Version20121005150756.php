<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20121005150756 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      // Bisherige Ticket-Tabelle entfernen
      $schema->dropTable('ticket');

      // Neue Ticket-Tabelle erstellen
      $ticketTable = $schema->createTable('ticket');
      
      $ticketTable
          ->addcolumn('id', 'string', array('length' => 100))
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('timestamp', 'integer', array('length' => 11))
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('websiteid', 'string', array('length' => 100))
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('isredirect', 'boolean')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('isget', 'boolean')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('requestconfig', 'text')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('ticketlifetime', 'integer')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('remainingcalls', 'integer')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('sessionlifetime', 'integer')
          ->setNotnull(false);
      $ticketTable
          ->addcolumn('credentials', 'text')
          ->setNotnull(false);

      $ticketTable->setPrimaryKey(array('id'));
      
      $this->addSql('TRUNCATE TABLE ticket');
    }

    public function down(Schema $schema)
    {
      // Bisherige Ticket-Tabelle entfernen
      $schema->dropTable('ticket');

      // Neue Ticket-Tabelle erstellen
      $ticketTable = $schema->createTable('ticket');
      
      $ticketTable
          ->addcolumn('id', 'integer')
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('websiteid', 'string', array('length' => 100))
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('timestamp', 'integer', array('length' => 100))
          ->setNotnull(true);
      $ticketTable
          ->addcolumn('url', 'string', array('length' => 255))
          ->setNotnull(true);
      
      $ticketTable->setPrimaryKey(array('id', 'websiteid'));
      
      $this->addSql('TRUNCATE TABLE ticket');
    }
}
