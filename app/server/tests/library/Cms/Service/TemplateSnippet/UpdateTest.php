<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\TemplateSnippet
 *
 * @package      Cms
 * @subpackage   Service\TemplateSnippet
 */

class UpdateTest extends ServiceTestCase
{
  protected $service;

  protected $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new TemplateSnippetService('TemplateSnippet');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $create = array(
      'name' => 'TEMPLATE_SNIPPET_NAME_'.__METHOD__,
      'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_'.__METHOD__,
      'category' => 'TEMPLATE_SNIPPET_CATEGORY_'.__METHOD__,
      'content' => '[]',
    );
    $createdSnippet = $this->service->create($this->websiteId, $create);
    
    
    $attributes = array(
      'name' => 'new name',
    );
    
    $this->assertNotSame($attributes['name'], $createdSnippet->getName());

    $lastUpdateBeforUpdate = $createdSnippet->getLastupdate();

    // kurz warten, damit updateTime geprueft werden kann (sonst ist Zeit zu kurz)
    sleep(1);

    $this->service->update($this->websiteId, $createdSnippet->getId(), $attributes);

    $templateSnippet = $this->service->getById($this->websiteId, $createdSnippet->getId());

    $newAttributes = array_merge($create, $attributes);
    $this->assertSame($newAttributes['name'], $templateSnippet->getName());
    $this->assertSame($newAttributes['description'], $templateSnippet->getDescription());
    $this->assertSame($newAttributes['category'], $templateSnippet->getCategory());
    $this->assertSame($newAttributes['content'], $templateSnippet->getContent());

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($templateSnippet->getLastupdate());
    $this->assertNotEquals($lastUpdateBeforUpdate, $templateSnippet->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $templateSnippet->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $templateSnippet->getLastupdate());
  }
}