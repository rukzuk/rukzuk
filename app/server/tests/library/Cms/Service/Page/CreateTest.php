<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer Create Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class CreateTest extends ServiceTestCase
{
  protected $service;

  protected $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PageService('Page');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $attributes = array(
      'templateid' => '',
      'name' => 'PHPUnit Test Page - Create',
      'content' => array(),
      'globalcontent' => array(),
      'mediaid' => 'PHPUnit Test Page - Create - mediaId',
      'pageType' => 'the_page_type_id',
      'pageAttributes' => (object)array(
        'foo' => 'bar',
        'myObject' => new \stdClass(),
        'myArray' => array(),
      ),
    );
    $this->testEntry = $this->service->create($this->websiteId, $attributes);

    $attributes['content'] = \Seitenbau\Json::encode($attributes['content']);
    $attributes['globalcontent'] = \Seitenbau\Json::encode($attributes['globalcontent']);
    
    $this->assertResultSuccess($this->testEntry, $attributes);
  }

  protected function assertResultFalse($result, $expectedData = '')
  {
    $this->assertNull($result);
  }

  /**
   * @param \Cms\Data\Page $result
   * @param array          $expectedData
   */
  protected function assertResultSuccess($result, array $expectedData)
  {
    $this->assertInstanceOf('Cms\Data\Page', $result);
    $this->assertNotNull($result->getId());
    $this->assertSame($this->websiteId, $result->getWebsiteId());
    $this->assertSame($expectedData['templateid'], $result->getTemplateId());
    $this->assertSame($expectedData['name'], $result->getName());
    $this->assertSame($expectedData['content'], $result->getContent());
    $this->assertSame($expectedData['globalcontent'], $result->getGlobalContent());
    $this->assertSame($expectedData['mediaid'], $result->getMediaId());
    $this->assertEquals($expectedData['pageType'], $result->getPageType());
    $this->assertEquals($expectedData['pageAttributes'], json_decode($result->getPageAttributes()));

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($result->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $result->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $result->getLastupdate());
  }
}