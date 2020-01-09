<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetByIdTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetByIdTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Template
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
  public function getByIdShouldReturnExpectedTemplate()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $create = array(
      'name' => uniqid(__METHOD__, true),
      'content' => array(array('id' => 'TEST_CONTENT_GET_BY_ID')),
      'pageType' => 'TEST_PAGETYPE_GET_BY_ID',
    );
    $this->service->create($websiteId, $create);

    $templates = $this->service->getAll($websiteId);
    
    foreach ($templates as $template)
    {
      if ($template->getName() === $create['name'])
      {
        $createdTemplate = $template;
        break;
      }
    }
    $idOfCreatedTemplated = $createdTemplate->getId();

    $getByIdTemplate = $this->service->getById($idOfCreatedTemplated, $websiteId);

    $this->assertInstanceOf('Cms\Data\Template', $getByIdTemplate);

    $this->assertSame($create['name'], $getByIdTemplate->getName());
    $this->assertSame(\Seitenbau\Json::encode($create['content']), $getByIdTemplate->getContent());
    $this->assertSame($create['pageType'], $getByIdTemplate->getPageType());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\Template::ID_PREFIX,
      \Orm\Data\Template::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($getByIdTemplate->getId()));
  }
}