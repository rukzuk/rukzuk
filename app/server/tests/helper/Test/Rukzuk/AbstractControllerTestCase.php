<?php


namespace Test\Rukzuk;

use Seitenbau\Cache\StaticCache;

abstract class AbstractControllerTestCase extends \Zend_Test_PHPUnit_ControllerTestCase
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
   * resets the caches
   */
  protected function resetCaches()
  {
    $staticCache = new StaticCache('TESTING');
    $staticCache->resetCache(true);
  }
}