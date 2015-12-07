<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * GetIdsByWebsiteIdTest
 *
 * @package      Service
 * @subpackage   Template
 */
class GetIdsByWebsiteIdTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Template
   */
  private $service;

  public $sqlFixtures = array('library_Cms_Service_Template.json');

  public function setUp()
  {
    parent::setUp();

    $this->service = new TemplateService('Template');
  }

  /**
   * @test
   * @group library
   *
   * @dataProvider provider_test_getIdsByWebsiteId
   */
  public function test_getIdsByWebsiteId($websiteId, $expectedIds)
  {
    // ACT
    $actualTemplateIds = $this->service->getIdsByWebsiteId($websiteId);

    // ASSERT
    $this->assertSame($expectedIds, $actualTemplateIds);
  }

  public function provider_test_getIdsByWebsiteId()
  {
    $websiteId = 'SITE-template-getI-DsBy-Webs-iteId0000001-SITE';
    return array(
      array(
        'SITE-template-getI-DsBy-Webs-iteId0000001-SITE',
        array(
          'TPL-10000000-0000-0000-0000-000000000001-TPL',
          'TPL-10000000-0000-0000-0000-000000000002-TPL',
          'TPL-10000000-0000-0000-0000-000000000003-TPL'),
      ),
      array(
        'SITE-template-getI-DsBy-Webs-iteId0000002-SITE',
        array('TPL-20000000-0000-0000-0000-000000000001-TPL'),
      ),
      array(
        'SITE-template-getI-DsBy-Webs-iteId000none-SITE',
        array(),
      ),
    );
  }
}