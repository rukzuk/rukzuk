<?php
namespace Cms\Service\Modul;

use Cms\Data\Modul as DataModul;
use Cms\Service\Modul as ModuleService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Seitenbau\Json as SbJson,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;

/**
 * CreateTest
 *
 * @package      Application
 * @subpackage   Controller
 */

class CreateTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Modul
   */
  protected $service;

  private $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';

  protected function setUp()
  {
    parent::setUp();

    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Module', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });
    
    $this->service = new ModuleService('Modul');
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
  public function createShouldStoreExpectedModule()
  {
    // ARRANGE
    $moduleCountBefore = count($this->service->getAll($this->websiteId));

    $create = new DataModul();
    $create->setName(__METHOD__ . '_1');
    $create->setDescription('TEST_DESCRIPTION_CREATE_1');
    $create->setVersion('TEST_VERSION_CREATE_1');
    $create->setIcon('TEST_ICON_CREATE_1');
    $create->setForm(SbJson::decode(SbJson::encode(
      array(array('name' => 'Titel und Beschreibung', 'formGroupData' => array('params' => array('name' => 'Cmsvar', 'value' => 'title'))))
    )));
    $create->setFormvalues(SbJson::decode(SbJson::encode(array(array('key' => 'value')))));
    $create->setCategory('TEST_CATEGORY_CREATE_1');
    $create->setModuletype('root');
    $create->setAllowedchildmoduletype('extension');
    $create->setRerenderrequired('rerenderrequired');

    // ACT
    $createdModul = $this->service->create($this->websiteId, $create);

    // ASSERT
    $modules = $this->service->getAll($this->websiteId);
    $this->assertTrue(count($modules) > $moduleCountBefore);
    /** @var \Cms\Data\Modul[] $modules */
    foreach ($modules as $module) {
      if ($module->getName() === $create->getName()) {
        $createdModule = $module;
      }
    }
    
    $this->assertInstanceOf('Cms\Data\Modul', $createdModule);
    $this->assertEquals($create->getName(), $createdModule->getName());
    $this->assertEquals($this->websiteId, $createdModule->getWebsiteId());
    $this->assertEquals($create->getDescription(), $createdModule->getDescription());
    $this->assertEquals($create->getVersion(), $createdModule->getVersion());
    $this->assertEquals($create->getIcon(), $createdModule->getIcon());
    $this->assertEquals($create->getForm(), $createdModule->getForm());
    $this->assertEquals($create->getFormvalues(), $createdModule->getFormvalues());
    $this->assertEquals($create->getCategory(), $createdModule->getCategory());
    $this->assertEquals($create->getModuletype(), $createdModule->getModuletype());
    $this->assertEquals($create->getAllowedchildmoduletype(), $createdModule->getAllowedchildmoduletype());
    $this->assertEquals($create->getRerenderrequired(), $createdModule->getReRenderRequired());
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($createdModule->getLastupdate());
    $maxAlter = date('Y-m-d H:i:s', (time()-2));
    $this->assertGreaterThan($maxAlter, $createdModule->getLastupdate());
    
    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\Modul::ID_PREFIX,
      \Orm\Data\Modul::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($createdModule->getId()));

    // this call throws exception on error occured
    $this->service->getAssetsPath($this->websiteId, $createdModule->getId());
  }
}