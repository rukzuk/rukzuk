<?php
namespace Test\Seitenbau;

use \Test\Rukzuk\AbstractTestCase;
use Seitenbau\Registry as Registry;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * This TestCase class makes use of database transaction facilities to
 * speed up the process of resetting the database to a known state at the
 * beginning of each test.
 *
 * A consequence of this, however, is that the effects of transaction commit
 * and rollback cannot be tested by a this TestCase class.
 *
 * @package      Application
 */
abstract class TransactionTestCase extends AbstractTestCase
{
  protected static $entityManager;

  /**
   * @var array
   */
  protected $sqlFixtures = array();

  /**
   * @var array
   */
  protected $sqlFixturesForTestMethod = array();

  public static function setUpBeforeClass()
  {
    self::$entityManager = Registry::getEntityManager();
  }

  protected function setUp()
  {
    parent::setUp();

    $this->resetCmsExceptionStack();

    $this->getDbHelper()->setUp($this->getSqlFixtures($this->getName()));

    MockManager::setUp();
  }

  protected function tearDown()
  {
    $this->getDbHelper()->tearDown();
    MockManager::tearDown();
    parent::tearDown();
  }

  /**
   * @param string $testName
   *
   * @return array
   */
  protected function getSqlFixtures($testName)
  {
    if (isset($this->sqlFixturesForTestMethod[$testName])) {
      $sqlLocalFixtures = $this->sqlFixturesForTestMethod[$testName];
    } else {
      $sqlLocalFixtures = array();
    }

    return array_unique(array_merge($this->sqlFixtures, $sqlLocalFixtures));
  }

  protected function resetCmsExceptionStack()
  {
    \Cms\ExceptionStack::reset();
  }

  protected function getEntityManager()
  {
    return self::$entityManager;
  }
}