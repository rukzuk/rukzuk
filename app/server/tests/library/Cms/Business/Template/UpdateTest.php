<?php
namespace Cms\Business\Template;

use Cms\Business\Template as TemplateBusiness,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * UpdateTest
 *
 * @package      Application
 * @subpackage   Controller
 */

class UpdateTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Template
   */
  private $business;

  public function setUp()
  {
    parent::setUp();

    $this->business = new TemplateBusiness('Template');
  }

  /**
   * @test
   * @group library
   */
  public function updateShouldAlterTemplateAsExpected()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $create = array(
      'name' => uniqid(__METHOD__, true),
      'content' => array(array('id' => 'TEMPLATE_CONTENT_CREATE')),
    );
    $this->business->create($websiteId, $create);

    $templates = $this->business->getAll($websiteId);
    
    foreach ($templates as $template)
    {
      if ($template->getName() === $create['name'])
      {
        $createdTemplate = $template;
        break;
      }
    }
    $idOfCreatedTemplated = $createdTemplate->getId();

    $update = array(
      'name' => $create['name'] . '_EDIT',
      'content' => array(array('id' => 'TEMPLATE_CONTENT_EDIT')),
      'websiteid' => $websiteId
    );
    
    $this->business->update($idOfCreatedTemplated, $websiteId, $update);
    
    $getByIdTemplate = $this->business->getById($idOfCreatedTemplated, $websiteId);
    $this->assertInstanceOf('Cms\Data\Template', $getByIdTemplate);

    $this->assertSame($update['name'], $getByIdTemplate->getName());
    $this->assertSame(\Zend_Json::encode($update['content']), $getByIdTemplate->getContent());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\Template::ID_PREFIX,
      \Orm\Data\Template::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($getByIdTemplate->getId()));
  }
  
  /**
   * @test
   * @group library
   */
  public function updateShouldReparseLinkedPages()
  {
    $pageId = 'PAGE-133d84e8-cc3e-4a1f-a408-b8fa374af75f-PAGE';
    $websiteId = 'SITE-4sz2bve3-1cfg-4836-b847-1ab0571b1e6d-SITE';
    $templateId = 'TPL-0db7eaa7-7fc5-464a-bd47-16b3b8af67eb-TPL';
    $pageIdWithOtherTemplate = 'PAGE-233d84e8-cc3e-4a1f-a408-b8fa374af75f-PAGE';
    
    $template = $this->business->getById($templateId, $websiteId);
    $templateContentOrg = $template->getContent();
    
    $page = $this->business->getService('Page')->getById($pageId, $websiteId);
    $pageTemplateContentOrg = $page->getTemplateContent();
    
    // Page und Template Content zum Start des Tests unterschiedlich
    if ($templateContentOrg == $pageTemplateContentOrg)
    {
      $template = $this->business->update($templateId, $websiteId, array('content' => '[]'));
      $templateContentOrg = $template->getContent();
    }
    $this->assertNotSame($templateContentOrg, $pageTemplateContentOrg);

    // Page mit anderem Template betrifft die Aenderung nicht
    $pageWithOtherTemplate = $this->business->getService('Page')->getById($pageIdWithOtherTemplate, $websiteId);
    $pageWithOtherTemplateContentOrg = $pageWithOtherTemplate->getTemplateContent();
    $this->assertNotSame($pageWithOtherTemplateContentOrg, $templateContentOrg);
    
    // Template neu speichern
    $templateAfterUpdate = $this->business->update($templateId, $websiteId, array());
    $pageAfterTemplateUpdate = $this->business->getService('Page')->getById($pageId, $websiteId);
    
    // Page und TEmplate Content gleich
    $this->assertSame($templateAfterUpdate->getContent(), $pageAfterTemplateUpdate->getTemplateContent());
    
    // Page mit anderen Template nicht betroffen vom Update
    $pageWithOtherTemplate = $this->business->getService('Page')->getById($pageIdWithOtherTemplate, $websiteId);
    $pageWithOtherTemplateContentAfterUpdate = $pageWithOtherTemplate->getTemplateContent();
    $this->assertSame($pageWithOtherTemplateContentOrg, $pageWithOtherTemplateContentAfterUpdate);
  }
}