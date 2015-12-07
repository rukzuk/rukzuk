<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * remove all now global snippets
 */
class Version20141015143712 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");


    // remove snippets
    $oldTplSnipIds = array(
      'TPLS-04b6a810-ebf5-4133-92c1-3390cbe3b29f-TPLS',
      'TPLS-478ec3ff-0a5b-42ea-9fb2-f95b9de2383b-TPLS',
      'TPLS-5d7302d9-ca60-4838-83c5-700c08b35383-TPLS',
      'TPLS-662168a3-9a04-4b20-ab2c-e55d00c0c34a-TPLS',
      'TPLS-73e29120-8c25-40a9-a6fa-97b066861b09-TPLS',
      'TPLS-75425a38-580f-40a9-bc7a-665ee8adf4bc-TPLS',
      'TPLS-7b3f0615-3523-4cb4-a3a8-f7c1296a2eeb-TPLS',
      'TPLS-80d0a855-7257-4e25-b6ea-1fc7818964b7-TPLS',
      'TPLS-88ce4825-cc80-4e5c-be90-17f0d8578b73-TPLS',
      'TPLS-8f63e622-8a24-4170-b196-5bc99898a5f5-TPLS',
      'TPLS-955566d0-f7b8-45fb-a6f5-009609a68c0d-TPLS',
      'TPLS-9f6c0053-ef4c-45e4-8a9f-5a270f7e429c-TPLS',
      'TPLS-bcb0e520-653e-44ca-83a5-a802832f8202-TPLS',
      'TPLS-cae118e6-def4-4975-9952-93584c052fac-TPLS',
      'TPLS-cfd7e89c-4add-41a6-80f0-e8ee69b826fb-TPLS',
      'TPLS-f16fdde6-3b9a-4d66-956d-471d4fef2e2a-TPLS');

    foreach ($oldTplSnipIds as $id) {
      $this->addSql('DELETE FROM template_snippet WHERE id=:id', array(
        'id' => $id,
      ));
    }

  }

  public function down(Schema $schema)
  {
    // Do nothing
  }
}
