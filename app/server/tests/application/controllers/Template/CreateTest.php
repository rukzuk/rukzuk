<?php
namespace Application\Controller\Template;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateController CreateTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class CreateTest extends ControllerTestCase
{
  protected $actionEndpoint = 'template/create';

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function createShouldReturnValidationErrorForInvalidParams($params, $expectedErrorParams)
  {
    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    foreach ($response->getError() as $error) {
      $this->assertObjectHasAttribute('param', $error);
      $this->assertInstanceOf('stdClass', $error->param);
      $this->assertObjectHasAttribute('field', $error->param);
      $this->assertInternalType('string', $error->param->field);
      $this->assertArrayHasKey($error->param->field, $expectedErrorParams, sprintf(
        'Failed asserting that parameter "%s" is invalid', $error->param->field
      ));
      $this->assertEquals($expectedErrorParams[$error->param->field], $error->code);
      unset($expectedErrorParams[$error->param->field]);
    }
    $this->assertCount(0, $expectedErrorParams, sprintf(
      'Failed asserting that params "%s" valid.', implode(', ', array_keys($expectedErrorParams))
    ));
  }

  /**
   * @test
   * @group integration
   */
  public function checkRequiredParams()
  {
    // ACT
    $this->dispatchWithParams($this->actionEndpoint, array());

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    $invalidParams = array();
    foreach ($response->getError() as $error)
    {
      $this->assertSame(3, $error->code);
      $this->assertObjectHasAttribute('field', $error->param);
      $invalidParams[] = $error->param->field;
    }
    $this->assertContains('websiteid', $invalidParams);
    $this->assertContains('name', $invalidParams);
    $this->assertContains('pagetype', $invalidParams);
  }

  /**
   * @test
   * @group integration
   */
  public function createShouldCreateExpectedTemplate()
  {
    // ARRANGE
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $allTemplates = $this->getAllTemplatesByWebsiteId($websiteId);
    $templateCountBeforeCreate = count($allTemplates);
    $this->assertGreaterThan(0, $templateCountBeforeCreate);

    $name = 'Template_Create_Via_Integration_Test';
    $pageTypeId = 'the_page_type_id';

    // ACT
    $this->dispatchWithParams($this->actionEndpoint, array(
      'websiteid' => $websiteId,
      'name' => $name,
      'pageType' => $pageTypeId,
      'content' => array(),
    ));

    // ASSERT
    $newTemplate = $this->getValidatedSuccessResponse()->getData();
    $this->assertNotNull($newTemplate);
    $this->assertObjectHasAttribute('id', $newTemplate);
    $this->assertNotNull($newTemplate->id);

    $allTemplatesAfterCreate = $this->getAllTemplatesByWebsiteId($websiteId);
    $this->assertGreaterThan(0, $allTemplatesAfterCreate);
    $this->assertGreaterThan($templateCountBeforeCreate, count($allTemplatesAfterCreate));

    $newTemplateData = null;
    foreach ($allTemplatesAfterCreate as $template) {
      $this->assertInstanceOf('stdClass', $template);
      $this->assertObjectHasAttribute('id', $template);
      $this->assertObjectHasAttribute('name', $template);
      $this->assertObjectHasAttribute('content', $template);
      if ($template->id == $newTemplate->id) {
        $newTemplateData = $template;
      }
    }
    $this->assertNotNull($newTemplateData);
    $this->assertEquals($name, $newTemplateData->name);
    $this->assertEquals($pageTypeId, $newTemplateData->pageType);
    $this->assertEquals(array(), $newTemplateData->content);

  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    $validWebsiteId = 'SITE-valid0id-0000-0000-0000-000000000000-SITE';
    $validName = 'This is a valid name';
    $validPageType = 'this_is_a_valid_page_type';
    $textLongerThat255Chars = 'sometextlongerthan255characterssometextlonger'
      . 'than255characterssometextlongerthan255characterssometextlongerthan255'
      . 'characterssometextlongerthan255characterssometextlongerthan255charact'
      . 'erssometextlongerthan255characterssometextlongerthan255characterssome'
      . 'textlongerthan255characters';

    $gueltigeUnits = array(array(
      'id' => '123456789',
      'name' => 'name der unit',
      'description' => 'beschreibung der unit',
      'moduleId' => '1233455',
      'formValues' => '',
      'deletable' => false,
      'readonly' => true,
      'ghostContainer' => true,
      'visibleFormGroups' => array('abc', 'def'),
      'expanded' => true,
      'children' => array()
    ));
    $ungueltigeUnits = array(array(
      'id' => '123456',
      'name' => 'unit_name',
      'ungueltigerKey' => 'abc',
    ));

    return array(
      array(
        array(
          'websiteid' => '123456879',
          'name' => null,
          'content' => '"abc"',
          'pagetype' => '%not_valid_id%',
        ),
        array('websiteid' => 3, 'name' => 3, 'content' => 3, 'pagetype' => 3),
      ),
      array(
        array(
          'websiteid' => null,
          'name' => $validName,
          'content' => null,
          'pagetype' => $validPageType,
        ),
        array('websiteid' => 3)
      ),
      array(
        array(
          'websiteid' => $validWebsiteId,
          'name' => $textLongerThat255Chars,
          'content' => null,
          'pagetype' => $validPageType,
        ),
        array('name' => 3)
      ),
      array(
        array(
          'websiteid' => $validWebsiteId,
          'name' => $validName,
          'content' => $ungueltigeUnits,
          'pagetype' => $validPageType,
        ),
        array('content' => 3)
      ),
      array(
        array(
          'websiteid' => $validWebsiteId,
          'name' => $validName,
          'content' => null,
          'pagetype' => $textLongerThat255Chars,
        ),
        array('pagetype' => 3)
      ),
      array(
        array(
          'websiteid' => $validWebsiteId,
          'name' => $validName,
          'content' => null,
          'pagetype' => null,
        ),
        array('pagetype' => 3)
      ),
      array(
        array(
          'websiteid' => $validWebsiteId,
          'name' => $validName,
          'content' => null,
          'pagetype' => '',
        ),
        array('pagetype' => 3)
      ),
      array(
        array(),
        array('websiteid' => 3, 'name' => 3, 'pagetype' => 3),
      )
    );
  }

  protected function getAllTemplatesByWebsiteId($websiteId)
  {
    $this->dispatchWithParams('template/getall', array(
      'websiteid' => $websiteId,
    ));
    $responseData = $this->getValidatedSuccessResponse()->getData();
    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertInternalType('array', $responseData->templates);
    return $responseData->templates;
  }
}