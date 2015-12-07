<?php
namespace Cms\Business\Cli;

use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Seitenbau\Registry as Registry,
    Cms\Business\Cli as CliBusiness,
    Cms\Business\Website as WebsiteBusiness,
    Cms\Business\Group as GroupBusiness,
    Cms\Business\User as UserBusiness,
    Test\Seitenbau\Optin as OptinTestHelper,
    Seitenbau\FileSystem as FS;

/**
 * Cli business initSystem Test
 *
 * @package      Cms
 * @subpackage   Business\Cli
 */
class InitSystemTest extends ServiceTestCase
{
  private $mailsFromFileTransportDirectory = '/tmp';

  protected function setUp()
  {
    parent::setUp();

    OptinTestHelper::clearMailsFromFileTransports(
      $this->mailsFromFileTransportDirectory
    );
  }
 
  protected function tearDown()
  {
    OptinTestHelper::clearMailsFromFileTransports(
      $this->mailsFromFileTransportDirectory
    );

    $this->getDbHelper()->markAsDirty();
    parent::tearDown();
  }
  
  /**
   * @test
   * @group library
   */
  public function test_initSystemSuccess()
  {
    ConfigHelper::removeOwner();
    $this->getCliBusiness()->initSystem();
    $this->assertNoUsersExists();
    $this->assertNoGroupsExists();
    $this->assertMigrationsCreated();
  }

  protected function assertNoUsersExists()
  {
    $userBusiness = new UserBusiness('User');
    $users = $userBusiness->getAll();
    $this->assertInternalType('array', $users);
    $this->assertEquals(0, count($users));
  }
  
  protected function assertNoGroupsExists()
  {
    $websiteBusiness = new WebsiteBusiness('Website');
    $groupBusiness = new GroupBusiness('Group');
    $websites = $websiteBusiness->getAll();
    foreach($websites as $website) {
      $groups = $groupBusiness->getAllByWebsiteId($website->getId());
      $this->assertInternalType('array', $groups);
      $this->assertEquals(0, count($groups));
    }
  }

  protected function assertMigrationsCreated()
  {
    // get all available migrations
    $allMigrations = array_keys($this->getAllMigrations());
    $this->assertInternalType('array', $allMigrations);
    $this->assertNotEmpty($allMigrations, 'doctrine migrations missing');

    // get migrations from DB
    $migratedMigrations = $this->getMigrationFromDb();
    $this->assertInternalType('array', $migratedMigrations);
    $this->assertNotEmpty($migratedMigrations, 'migrated migrations missing');

    // check all versions
    $this->assertCount(count($allMigrations), $migratedMigrations);
    foreach($allMigrations as $expectedVersion) {
      $this->assertContains($expectedVersion, $migratedMigrations);
    }
  }

  /**
   * @return \Doctrine\DBAL\Migrations\Version[]
   */
  protected function getAllMigrations()
  {
    return $this->getMigrationConfiguration()->getMigrations();
  }

  /**
   * @return \Doctrine\DBAL\Migrations\Version[]
   */
  protected function getMigrationFromDb()
  {
    return $this->getMigrationConfiguration()->getMigratedVersions();
  }

  /**
   * @return \Doctrine\DBAL\Migrations\Configuration\Configuration
   */
  protected function getMigrationConfiguration()
  {
    $config = Registry::getConfig();
    $entityManager = Registry::getEntityManager();
    $configuration = new \Doctrine\DBAL\Migrations\Configuration\Configuration($entityManager->getConnection());
    $configuration->setName($config->migration->doctrine->name);
    $configuration->setMigrationsNamespace($config->migration->doctrine->migrations_namespace);
    $configuration->setMigrationsTableName($config->migration->doctrine->table_name);
    $configuration->setMigrationsDirectory($config->migration->doctrine->migrations_directory);
    $configuration->registerMigrationsFromDirectory($configuration->getMigrationsDirectory());
    return $configuration;
  }

  protected function getCliBusiness()
  {
    return new \Cms\Business\Cli('Cli');
  }
}