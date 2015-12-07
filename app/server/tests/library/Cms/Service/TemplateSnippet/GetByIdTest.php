<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Cms\Exception as CmsException;
/**
 * TemplateSnippet Service GetByIdTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetByIdTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\TemplateSnippet
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new TemplateSnippetService('TemplateSnippet');
  }

  /**
   * @test
   * @group library
   */
  public function getByIdShouldReturnExpectedTemplateSnippet()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $create = array(
      'name' => 'TEMPLATE_SNIPPET_NAME_'.__METHOD__,
      'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_'.__METHOD__,
      'category' => 'TEMPLATE_SNIPPET_CATEGORY_'.__METHOD__,
      'content' => '[]',
    );
    $createdSnippet = $this->service->create($websiteId, $create);
    $idOfCreatedTemplateSnippet = $createdSnippet->getId();

    $getByIdTemplateSnippet = $this->service->getById($websiteId, $idOfCreatedTemplateSnippet);

    $this->assertInstanceOf('Cms\Data\TemplateSnippet', $getByIdTemplateSnippet);

    $this->assertSame($idOfCreatedTemplateSnippet, $getByIdTemplateSnippet->getId());
    $this->assertSame($create['name'], $getByIdTemplateSnippet->getName());
    $this->assertSame($create['description'], $getByIdTemplateSnippet->getDescription());
    $this->assertSame($create['category'], $getByIdTemplateSnippet->getCategory());
    $this->assertSame($create['content'], $getByIdTemplateSnippet->getContent());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\TemplateSnippet::ID_PREFIX,
      \Orm\Data\TemplateSnippet::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($getByIdTemplateSnippet->getId()));
  }

  /**
   * @test
   * @group library
   */
  public function getByIdShouldThrowExceptionIfTemplateSnippetNotExists()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $idOfANotExistingSnippet = 'TPLS-template-snip-pet0-not0-existing0001-TPLS';

    $snippetExists = false;
    $existingTemplateSnippets = $this->service->getAll($websiteId);
    foreach($existingTemplateSnippets as $snippet) {
      if ($idOfANotExistingSnippet == $snippet->getId()) {
        $snippetExists = true;
        break;
      }
    }
    $this->assertFalse($snippetExists, sprintf(
      'The TemplateSnippet (%s) exists.', $idOfANotExistingSnippet
    ));

    try {
      $this->service->getById($websiteId, $idOfANotExistingSnippet);
    } catch(CmsException $e) {
      if ($e->getCode() == 1602) {
        return;
      }
      $this->fail(sprintf(
        'Wrong CmsException (%s) with code "%d" has been raised, code 1602 expected.',
        $e->getMessage(),
        $e->getCode()
      ));
    } catch(\Exception $e) {
      $this->fail(sprintf('Wrong exception (%s) has been raised.', $e->getMessage()));
    }
    
    $this->fail('The expected CmsException with code 1602 has not been raised.');
  }
}