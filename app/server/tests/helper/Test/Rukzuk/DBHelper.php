<?php


namespace Test\Rukzuk;


use Seitenbau\Registry;
use Test\Seitenbau\Sql\Reader as SqlReader;

class DBHelper
{
  /**
   * @var bool
   */
  static private $dirty = true;

  /**
   * mark db as dirty
   */
  public function markAsDirty()
  {
    self::$dirty = true;
  }

  /**
   * @return bool
   */
  public function isDirty()
  {
    return (self::$dirty == true);
  }

  /**
   * @param array $sqlFixtures
   * @param bool  $beginTransaction
   */
  public function setUp(array $sqlFixtures, $beginTransaction = true)
  {
    if ($beginTransaction) {
      $this->beginTransaction();
    }
    $this->loadSqlFixtures($sqlFixtures);
  }

  public function tearDown($rollbackTransaction = true)
  {
    if ($rollbackTransaction) {
      $this->rollbackTransaction();
    }
  }

  /**
   * @throws \Exception
   */
  public function resetDb()
  {
    $this->dropAndCreateSchema();

    $resetDbFixtures = array(
      'Album.json',
      'Group.json',
      'Locks.json',
      'Media.json',
      'Page.json',
      'Template.json',
      'TemplateSnippet.json',
      'User.json',
      'UserOptIn.json',
      'Website.json',
    );
    $testSqlDirectory = Registry::getConfig()->test->sql->storage->directory;

    $pdo = $this->getDbAdapter()->getConnection();
    $pdo->beginTransaction();
    try {
      $this->getFixturesLoader()->loadSqlFixtures($resetDbFixtures, $testSqlDirectory);
    } catch (\Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
    $pdo->commit();

    self::$dirty = false;
  }

  protected function dropAndCreateSchema()
  {
    $em = $this->getEntityManager();
    $metadatas = $em->getMetadataFactory()->getAllMetadata();
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());
    $schemaTool->dropSchema($metadatas);
    $schemaTool->createSchema($metadatas);
  }

  protected function beginTransaction()
  {
    $this->getEntityManager()->getConnection()->beginTransaction();
  }

  protected function rollbackTransaction()
  {
    $this->getEntityManager()->getConnection()->rollback();
  }

  /**
   * Loads all SQL fixtures from the given fixtures files
   *
   * @param array $sqlFixtures -- array of sql fixtures files
   */
  protected function loadSqlFixtures(array $sqlFixtures)
  {
    $this->getFixturesLoader()->loadSqlFixtures($sqlFixtures);
  }

  /**
   * @return FixturesLoaderTestCaseHelper
   */
  protected function getFixturesLoader()
  {
    return new FixturesLoaderTestCaseHelper($this->getDbAdapter());
  }

  /**
   * @return \Zend_Db_Adapter_Pdo_Mysql
   */
  protected function getDbAdapter()
  {
    return Registry::getDbAdapter();
  }

  /**
   * @return \Doctrine\ORM\EntityManager
   */
  protected function getEntityManager()
  {
    return Registry::getEntityManager();
  }
}