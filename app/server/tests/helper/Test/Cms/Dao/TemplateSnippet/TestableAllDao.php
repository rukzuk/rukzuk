<?php


namespace Test\Cms\Dao\TemplateSnippet;

use Cms\Dao\TemplateSnippet\All as TemplateSnippetAllDao;

/**
 * This class is only for easier mocking while unit testing
 */
class TestableAllDao extends TemplateSnippetAllDao
{
  public function phpunit_tearDown()
  {
  }

  public function phpunit_setDoctrineDao($daoDoctrine)
  {
    $this->daoDoctrine = $daoDoctrine;
  }

  public function phpunit_setFilesystemDao($daoFilesystem)
  {
    $this->daoFilesystem = $daoFilesystem;
  }
}