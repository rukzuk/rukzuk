<?php


namespace Test\Rukzuk;

use Cms\Dao\Base\SourceItem;
use Seitenbau\Cache\StaticCache;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Dao\Module\Source as ModuleSOurce;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
  const BACKUP_CONFIG = true;

  protected function setUp()
  {
    $this->initDb();
    $this->setUpGcFix();
    $this->resetCaches();
    parent::setUp();
  }

  protected function tearDown()
  {
    // restore config
    if (static::BACKUP_CONFIG) {
      ConfigHelper::restoreConfig();
    }

    $this->fixMemoryUsage();
    parent::tearDown();
    $this->tearDownGcFix();
  }

  protected function initDb()
  {
    $dbHelper = $this->getDbHelper();
    if ($dbHelper->isDirty()) {
      $dbHelper->resetDb();
    }
  }

  /**
   * @return DBHelper
   */
  protected function getDbHelper()
  {
    return new DBHelper();
  }

  /**
   * fixing memory usage
   */
  protected function fixMemoryUsage()
  {
    $refl = new \ReflectionObject($this);
    foreach ($refl->getProperties() as $prop) {
      if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
        $prop->setAccessible(true);
        $prop->setValue($this, null);
      }
    }
  }

  /**
   * fixing GC segmentation fault (disable GC before test)
   */
  protected function setUpGcFix()
  {
    gc_collect_cycles();
    gc_disable();
  }

  /**
   * fixing GC segmentation fault (enable GC after test)
   */
  protected function tearDownGcFix()
  {
    gc_enable();
  }

  /**
   * @param array|\Zend_Config $config
   *
   * @throws \Exception
   */
  protected function mergeIntoConfig($config)
  {
    ConfigHelper::mergeIntoConfig($config);
  }

  /**
   * enables global sets support
   *
   * @param string|null $baseDirectory
   */
  protected function enableGlobalSets($baseDirectory = null)
  {
    ConfigHelper::enableGlobalSets($baseDirectory);
  }

  /**
   * disables global sets support
   */
  protected function disableGlobalSets()
  {
    ConfigHelper::disableGlobalSets();
  }

  /**
   * @param string $globalSetId
   *
   * @return string
   */
  protected function getGlobalSetDirectory($globalSetId)
  {
    $config = Registry::getConfig()->item->sets;
    return FS::joinPath($config->directory, $globalSetId);
  }

  /**
   * @param string $globalSetId
   * @param string $packageId
   *
   * @return \Cms\Dao\Base\SourceItem[]
   */
  protected function getModuleSourceItemsFromGlobalSetAndPackage($globalSetId, $packageId)
  {
    $config = Registry::getConfig()->item->sets;
    $baseDirectory = FS::joinPath($config->directory, $globalSetId, $packageId, 'modules');
    $baseUrl = $config->url . '/' . $globalSetId . '/' . $packageId . '/modules';
    return $this->getSourceItemsFromDirectory($baseDirectory, $baseUrl,
      SourceItem::SOURCE_REPOSITORY, true, false);
  }

  /**
   * @param object $obj
   * @param string $methodName
   * @param array  $args
   *
   * @return mixed
   */
  protected function callMethod($obj, $methodName, array $args = array())
  {
    $class = new \ReflectionClass($obj);
    $method = $class->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($obj, $args);
  }

  /**
   * @param object $obj
   * @param string $propertyName
   * @param mixed  $value
   */
  protected function setObjectProperty($obj, $propertyName, $value)
  {
    $class = new \ReflectionClass($obj);
    $property = $class->getProperty($propertyName);
    $property->setAccessible(true);
    $property->setValue($obj, $value);
  }

  /**
   * @param string  $baseDirectory
   * @param string  $baseUrl
   * @param string  $type
   * @param bool    $isReadonly
   * @param bool    $isExportable
   *
   * @return \Cms\Dao\Base\SourceItem[]
   */
  protected function getSourceItemsFromDirectory($baseDirectory, $baseUrl,
                                                 $type = SourceItem::SOURCE_UNKNOWN,
                                                 $isReadonly = true, $isExportable = false)
  {
    if (empty($baseDirectory) || !is_dir($baseDirectory)) {
      return array();
    }
    $sources = array();
    $iterator = new \DirectoryIterator($baseDirectory);
    foreach ($iterator as $entry) {
      if ($entry->isDot() || !$entry->isDir()) {
        continue;
      }
      $sources[] = new SourceItem($entry->getFilename(), $entry->getPathname(),
        $baseUrl . '/' . $entry->getFilename(), $type, $isReadonly, $isExportable);

    }
    return $sources;
  }

  /**
   * resets the caches
   */
  protected function resetCaches()
  {
    $staticCache = new StaticCache('TESTING');
    $staticCache->resetCache(true);
  }
}