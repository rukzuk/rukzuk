<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Cms\Exception as CmsException;

/**
 * GetAllTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetAllTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\TemplateSnippet
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
  public function getAllShouldRetrieveExpectedTemplatesSnippets()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';

    $expectedSnippets = array(
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000001-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_1',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_1',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_1',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000002-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_2',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_2',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_2',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000003-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_3',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_3',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_3',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
    );
    
    $actualSnippets = $this->service->getAll($websiteId);

    $this->assertSame(count($expectedSnippets), count($actualSnippets));

    foreach ($actualSnippets as $index => $snippet)
    {
      $this->assertInstanceOf('Cms\Data\TemplateSnippet', $snippet);
      $snippetAsArray = $snippet->toArray();
      foreach ($expectedSnippets[$index] as $attributeName => $expectedValue) {
        $this->assertEquals($expectedValue, $snippetAsArray[$attributeName]);
      }
      $uuidValidator = new UniqueIdValidator(
        \Orm\Data\TemplateSnippet::ID_PREFIX,
        \Orm\Data\TemplateSnippet::ID_SUFFIX
      );
      $this->assertTrue($uuidValidator->isValid($snippet->getId()));
    }
  }
  
  /**
   * @test
   * @group library
   */
  public function getAllDescShouldRetrieveExpectedTemplatesSnippets()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';

    $expectedSnippets = array(
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000001-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_1',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_1',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_1',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000002-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_2',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_2',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_2',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
      array(
        'id' => 'TPLS-template-snip-pet0-test-000000000003-TPLS',
        'name' => 'TEMPLATE_SNIPPET_NAME_3',
        'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_3',
        'category' => 'TEMPLATE_SNIPPET_CATEGORY_3',
        'baselayout' => false,
        'pagetypes' => array(),
        'content' => '[]',
      ),
    );
    
    $actualSnippets = $this->service->getAll($websiteId, 'DESC');

    $this->assertSame(count($expectedSnippets), count($actualSnippets));

    foreach ($actualSnippets as $actualIndex => $snippet)
    {
      $index = count($expectedSnippets) - $actualIndex - 1;
      $this->assertInstanceOf('Cms\Data\TemplateSnippet', $snippet);
      $snippetAsArray = $snippet->toArray();
      foreach ($expectedSnippets[$index] as $attributeName => $expectedValue) {
        $this->assertEquals($expectedValue, $snippetAsArray[$attributeName]);
      }

      $uuidValidator = new UniqueIdValidator(
        \Orm\Data\TemplateSnippet::ID_PREFIX,
        \Orm\Data\TemplateSnippet::ID_SUFFIX
      );
      $this->assertTrue($uuidValidator->isValid($snippet->getId()));
    }
  }
}