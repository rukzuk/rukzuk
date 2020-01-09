<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetAllTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetAllTest extends ServiceTestCase
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
  public function getAllShouldRetrieveExpectedTemplates()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';

    $uniqName = uniqid(__METHOD__, true);
    $creates = array(
      array(
        'name' => $uniqName . '_1',
        'content' => array(array('id' => 'TEST_CONTENT_GET_ALL_1')),
        'pageType' => 'TEST_PAGETYPE_GET_ALL_1',
      ), array(
        'name' => $uniqName . '_2',
        'content' => array(array('id' => 'TEST_CONTENT_GET_ALL_2')),
        'pageType' => 'TEST_PAGETYPE_GET_ALL_2',
      ), array(
        'name' => $uniqName . '_3',
        'content' => array(array('id' => 'TEST_CONTENT_GET_ALL_3')),
      )
    );
    
    foreach ($creates as $create)
    {
      $this->service->create($websiteId, $create);
    }

    $templates = $this->service->getAll($websiteId);

    $this->assertSame(
      count($creates) + count($templates),
      count($templates) + count($creates)
    );

    foreach ($creates as $create)
    {
      foreach ($templates as $template)
      {
        if ($create['name'] === $template->getName())
        {
          $createdTemplates[] = $template;
        }
      }
    }

    $this->assertSame(count($creates), count($createdTemplates));

    foreach ($createdTemplates as $index => $template)
    {
      $this->assertInstanceOf('Cms\Data\Template', $template);
      $this->assertSame($creates[$index]['name'], $template->getName());
      $this->assertSame(\Seitenbau\Json::encode($creates[$index]['content']), $template->getContent());
      if (array_key_exists('pageType', $creates[$index])) {
        $this->assertEquals($creates[$index]['pageType'], $template->getPageType());
      } else {
        $this->assertNull($template->getPageType());
      }

      $uuidValidator = new UniqueIdValidator(
        \Orm\Data\Template::ID_PREFIX,
        \Orm\Data\Template::ID_SUFFIX
      );
      $this->assertTrue($uuidValidator->isValid($template->getId()));
    }
  }
}