<?php
namespace Cms\Service\Modul;

use Cms\Service\Modul as ModulService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Seitenbau\Json as SbJson,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
use Cms\Data\Modul as DataModule;

/**
 * GetAll
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetAllTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Modul
   */
  private $service;

  private $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';

  public function setUp()
  {
    parent::setUp();
    
    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Module', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });

    $this->service = new ModulService('Modul');
  }

  public function tearDown()
  {
    ModuleWriteableMock::tearDown();
    
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function getAllOnlyRetrieveModulesFromWebsite()
  {
    // ARRANGE
    $expectedModuleIds = array(
      'MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-afac-90ae9d96d3c2-MODUL',
      'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-4mrap53m-2af9-4g2f-a4rb-4a93in3f70mu-MODUL',
    );

    // ACT
    $modules = $this->service->getAll($this->websiteId);

    // ASSERT
    $this->assertInternalType('array', $modules);
    $this->assertCount(count($expectedModuleIds), $modules);
    foreach ($modules as $module) {
      $this->assertContains($module->getId(), $expectedModuleIds);
    }
  }

  /**
   * @test
   * @group library
   */
  public function getAllShouldRetrieveExpectedModules()
  {
    $sortedIndex = array(1, 2, 0);

    /** @var \Cms\Data\Modul[] $creates */
    $creates = array();

    $newModule = new DataModule();
    $newModule->setName(__METHOD__ . '_3');
    $newModule->setDescription('TEST_DESCRIPTION_GET_ALL_3');
    $newModule->setVersion('TEST_VERSION_GET_ALL_3');
    $newModule->setIcon('TEST_ICON_GET_ALL_3');
    $newModule->setForm(array());
    $newModule->setFormvalues(SbJson::decode(SbJson::encode(array('foo' => 'bar')), SbJson::TYPE_OBJECT));
    $newModule->setCategory('TEST_CATEGORY_GET_ALL_3');
    $newModule->setModuletype('default');
    $newModule->setAllowedchildmoduletype('extension');
    $creates[] = $newModule;

    $newModule = new DataModule();
    $newModule->setName(__METHOD__ . '_1');
    $newModule->setDescription('TEST_DESCRIPTION_GET_ALL_1');
    $newModule->setVersion('TEST_VERSION_GET_ALL_1');
    $newModule->setIcon('TEST_ICON_GET_ALL_1');
    $newModule->setForm(SbJson::decode(SbJson::encode(
      array(array('id' => 'abcdefg', 'name' => 'Titel und Beschreibung'))
    ), SbJson::TYPE_OBJECT));
    $newModule->setFormvalues(new \stdClass());
    $newModule->setCategory('TEST_CATEGORY_GET_ALL_1');
    $newModule->setModuletype('root');
    $newModule->setAllowedchildmoduletype('*');
    $creates[] = $newModule;

    $newModule = new DataModule();
    $newModule->setName(__METHOD__ . '_2');
    $newModule->setDescription('TEST_DESCRIPTION_GET_ALL_2');
    $newModule->setVersion('TEST_VERSION_GET_ALL_2');
    $newModule->setIcon('TEST_ICON_GET_ALL_2');
    $newModule->setForm(array());
    $newModule->setFormvalues(SbJson::decode(SbJson::encode(array('asdf' => 0)), SbJson::TYPE_OBJECT));
    $newModule->setCategory('TEST_CATEGORY_GET_ALL_2');
    $newModule->setModuletype('default');
    $newModule->setAllowedchildmoduletype('extension');
    $creates[] = $newModule;

    $moduleIds = array();
    foreach ($creates as $create)
    {
      $createdModul = $this->service->create($this->websiteId, $create);
      $moduleIds[] = $createdModul->getId();
    }

    /** @var \Cms\Data\Modul[] $modules */
    $modules = $this->service->getAll($this->websiteId);
    /** @var \Cms\Data\Modul[] $createdModules */
    $createdModules = array();
    foreach ($modules as $module)  {
      if ($module->getName() === $creates[0]->getName()
          || $module->getName() === $creates[1]->getName()
          || $module->getName() === $creates[2]->getName()
      ) {
        $createdModules[] = $module;
      }
    }

    foreach ($createdModules as $returnedIndex => $module) {
      $index = $sortedIndex[$returnedIndex];
      $this->assertInstanceOf('Cms\Data\Modul', $module);
      $this->assertSame($creates[$index]->getName(), $module->getName());
      $this->assertSame($creates[$index]->getDescription(), $module->getDescription());
      $this->assertSame($creates[$index]->getVersion(), $module->getVersion());
      $this->assertSame($creates[$index]->getIcon(), $module->getIcon());
      $this->assertEquals($creates[$index]->getForm(), $module->getForm());
      $this->assertEquals($creates[$index]->getFormvalues(), $module->getFormvalues());
      $this->assertSame($creates[$index]->getCategory(), $module->getCategory());
      $this->assertSame($creates[$index]->getModuletype(), $module->getModuletype());
      $this->assertSame($creates[$index]->getAllowedchildmoduletype(), $module->getAllowedchildmoduletype());

      $uuidValidator = new UniqueIdValidator(
        \Orm\Data\Modul::ID_PREFIX,
        \Orm\Data\Modul::ID_SUFFIX
      );
      $this->assertTrue($uuidValidator->isValid($module->getId()));
    }
  }

}