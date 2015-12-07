<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * DeleteTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class DeleteTest extends ServiceTestCase
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
  public function deleteShouldDeleteExpectedTemplate()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';

    $create = array(
      'name' => uniqid(__METHOD__, true),
      'content' => array(array('id' => 'TEST_CONTENT_DELETE_BY_ID')),
    );
    $this->service->create($websiteId, $create);

    $afterCreateTemplates = $this->service->getAll($websiteId);

    foreach ($afterCreateTemplates as $template)
    {
      if ($template->getName() === $create['name'] && $template->getWebsiteId() === $websiteId) {
        $createdTemplate = $template;
      }
    }
    $idOfCreatedTemplated = $createdTemplate->getId();

    $this->service->delete($idOfCreatedTemplated, $websiteId);

    $afterDeleteTemplates = $this->service->getAll($websiteId);

    $this->assertSame(
      (count($afterCreateTemplates) - 1),
      count($afterDeleteTemplates)
    );
  }
  
  /**
   * @test
   * @group library
   * @expectedException Cms\Exception
   */
  public function deleteShouldRejectDeleteWhenTemplateAssociatedToPage()
  {
    $this->service->delete('TPL-17rap53f-2bf8-4g4f-a47b-4a97223fbdea-TPL',
      'SITE-25rapd17-fbc4-489e-9b1d-905608acedf7-SITE');
  }
}