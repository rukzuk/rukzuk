<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Cms\Exception as CmsException;
/**
 * TemplateSnippet CreateTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class CreateTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\TemplateSnippet
   */
  private $service;

  public $sqlFixtures = array('TemplateSnippetService.json');

  public function setUp()
  {
    parent::setUp();

    $this->service = new TemplateSnippetService('TemplateSnippet');
  }
  
  /**
   * @test
   * @group library
   */
  public function createShouldStoreExpectedTemplateSnippet()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $create = array(
      'name' => __METHOD__,
      'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_'.__METHOD__,
      'category' => 'TEMPLATE_SNIPPET_CATEGORY_'.__METHOD__,
      'content' => '[]',
    );
    $snippetId = $this->service->create($websiteId, $create);
    $createdTemplateSnippet = $this->service->getById($websiteId, $snippetId->getId());

    $this->assertInstanceOf('Cms\Data\TemplateSnippet', $createdTemplateSnippet);
    $this->assertSame($create['name'], $createdTemplateSnippet->getName());
    $this->assertSame($create['description'], $createdTemplateSnippet->getDescription());
    $this->assertSame($create['category'], $createdTemplateSnippet->getCategory());
    $this->assertSame($create['content'], $createdTemplateSnippet->getContent());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\TemplateSnippet::ID_PREFIX,
      \Orm\Data\TemplateSnippet::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($createdTemplateSnippet->getId()));

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($createdTemplateSnippet->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $createdTemplateSnippet->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $createdTemplateSnippet->getLastupdate());
  }
}