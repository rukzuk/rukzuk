<?php


namespace Test\Cms\Dao\TemplateSnippet;


use Cms\Dao\Base\SourceItem;
use Test\Seitenbau\TransactionTestCase;
use Cms\Dao\TemplateSnippet\TemplateSnippetSource;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;


abstract class AbstractDaoTestCase extends TransactionTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @var string
   */
  protected $jsonStorageDirectory;

  protected function setUp()
  {
    parent::setUp();
    $this->jsonStorageDirectory = Registry::getConfig()->test->templatesnippet->storage->directory;
  }

  /**
   * @param string|null $subDirectory
   *
   * @return string
   */
  protected function getBaseDirectory($subDirectory = null)
  {
    if (is_string($subDirectory)) {
      return FS::joinPath($this->jsonStorageDirectory, $subDirectory);
    } else {
      return $this->jsonStorageDirectory;
    }
  }

  /**
   * @param string $websiteId
   * @param array  $baseInfo
   *
   * @return TemplateSnippetSource
   */
  protected function getTemplateSnippetSource($websiteId = '', array $baseInfo = null)
  {
    $sources = array();
    if (is_array($baseInfo)) {
      foreach ($baseInfo as $data) {
        if ($data instanceof SourceItem) {
          $sources[] = $data;
        } else {
          $baseDirectory = $data[0];
          $subDirectory = $data[1];
          $sourceType = (isset($data[2]) ? $data[2] : SourceItem::SOURCE_UNKNOWN);
          $sources[] = new SourceItem($subDirectory, FS::joinPath($baseDirectory, $subDirectory),
            '/url/to/templateSnippet/'.$subDirectory, $sourceType, true);
        }
      }
    }
    return new TemplateSnippetSource($websiteId, $sources);
  }

  /**
   * @param \Cms\Dao\TemplateSnippet|null $daoDoctrine
   * @param \Cms\Dao\TemplateSnippet|null $daoFilesystem
   *
   * @return \Test\Cms\Dao\TemplateSnippet\TestableAllDao
   */
  protected function getAllDao($daoDoctrine = null, $daoFilesystem = null)
  {
    $allDao = new TestableAllDao();
    if (!is_null($daoDoctrine)) {
      $allDao->phpunit_setDoctrineDao($daoDoctrine);
    }
    if (!is_null($daoFilesystem)) {
      $allDao->phpunit_setFilesystemDao($daoFilesystem);
    }
    return $allDao;
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Doctrine|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getDoctrineDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\TemplateSnippet\Doctrine')->getMock();
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getFilesystemDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\TemplateSnippet\Filesystem')->getMock();
  }

  /**
   * @param string $count
   * @param string $suffix
   * @param array  $attributes
   *
   * @return \Cms\Data\TemplateSnippet[]
   */
  protected function createDataSnippets($count, $suffix, $attributes = array())
  {
    $snippets = array();
    for ($i = 1; $i <= $count; $i++) {
      $snippet = new DataTemplateSnippet();
      if (array_key_exists('websiteid', $attributes)) {
        $snippet->setWebsiteid($attributes['websiteid']);
      }
      if (array_key_exists('id', $attributes)) {
        $snippet->setId($attributes['id']);
      } else {
        $snippet->setNewGeneratedId();
      }
      if (array_key_exists('name', $attributes)) {
        $snippet->setName($attributes['name']);
      } else {
        $snippet->setName(sprintf('name_%04d%s', $i, $suffix));
      }
      if (array_key_exists('description', $attributes)) {
        $snippet->setDescription($attributes['description']);
      } else {
        $snippet->setDescription(sprintf('description_%04d%s', $i, $suffix));
      }
      if (array_key_exists('category', $attributes)) {
        $snippet->setCategory($attributes['category']);
      } else {
        $snippet->setCategory(sprintf('category_%04d%s', $i, $suffix));
      }
      if (array_key_exists('content', $attributes)) {
        $snippet->setContent($attributes['content']);
      } else {
        $snippet->setCategory(json_encode(
          array(array('attribute' => sprintf('content_%04d%s', $i, $suffix)))
        ));
      }
      if (array_key_exists('readonly', $attributes)) {
        $snippet->setReadonly($attributes['readonly']);
      } else {
        $snippet->setReadonly(false);
      }
      if (array_key_exists('sourcetype', $attributes)) {
        $snippet->setSourceType($attributes['sourcetype']);
      } else {
        $snippet->setSourceType($snippet::SOURCE_LOCAL);
      }
      if (array_key_exists('overwritten', $attributes)) {
        $snippet->setOverwritten($attributes['overwritten']);
      } else {
        $snippet->setOverwritten(false);
      }
      if (array_key_exists('lastupdate', $attributes)) {
        $snippet->setLastupdate($attributes['lastupdate']);
      } else {
        $snippet->setLastupdate(time());
      }
      $snippets[] = $snippet;
    }
    return $snippets;
  }
}