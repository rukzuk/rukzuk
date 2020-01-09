<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * CreateTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class CreateTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Template
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new TemplateService('Template');
  }
  /**
   * @test
   * @group library
   */
  public function createShouldStoreExpectedTemplate()
  {
    // ARRANGE
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $create = array(
      'name' => uniqid(__METHOD__, true),
      'content' => array(array('id' => 'TEST_CONTENT_CREATE')),
      'pageType' => 'the_page_type_id',
    );

    // ACT
    $this->service->create($websiteId, $create);

    // ASSERT
    $templates = $this->service->getAll($websiteId);
    foreach ($templates as $template) {
      if ($template->getName() === $create['name']) {
        $createdTemplate = $template;
        break;
      }
    }

    $this->assertInstanceOf('Cms\Data\Template', $createdTemplate);
    $this->assertSame($create['name'], $createdTemplate->getName());
    $this->assertSame($create['pageType'], $createdTemplate->getPageType());
    $this->assertSame(\Seitenbau\Json::encode($create['content']), $createdTemplate->getContent());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\Template::ID_PREFIX,
      \Orm\Data\Template::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($createdTemplate->getId()));

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($createdTemplate->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $createdTemplate->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $createdTemplate->getLastupdate());
  }
}