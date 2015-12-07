<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\MediaItemMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_image;

require_once(MODULE_PATH.'/rz_link/module/rz_link.php');

class rz_link_testable_module extends \Rukzuk\Modules\rz_link
{
  public function getUrl($api, $unit)
  {
    return parent::getUrl($api, $unit);
  }
}

class rz_link_RenderTest_ApiMock extends RenderApiMock
{
}

class rz_link_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_link_testable_module';

  /**
   * @dataProvider provider_test_getUrl_success
   */
  public function test_getUrl_success($apiMockConf, $unitConf, $expectedUrl)
  {
    // ARRANGE
    $api = $this->createApiMock($apiMockConf);
    $unit = $this->createUnit($unitConf);
    $module = $this->createModule();

    // ACT
    $url = $module->getUrl($api, $unit);

    // ASSERT
    $this->assertEquals($expectedUrl, $url);
  }

  public function provider_test_getUrl_success()
  {
    return array(
      // internal link without anchor
      array(
        array(
          'navigation' => array(
            'page-id-01' => array(
              'url' => '/path/to/page-id-01/index.html',
              'inNavigation' => true,
              'children' => array()
        ))),
        array(
          'formValues' => array(
            'linkType' => 'page',
            'pageId' => 'page-id-01',
            'pageAnchor' => ''
        )),
        '/path/to/page-id-01/index.html',
      ),
      // internal link with wrong anchor
      array(
        array(
          'navigation' => array(
            'page-id-01' => array(
              'url' => '/path/to/page-id-01/index.html',
              'inNavigation' => true,
              'children' => array()
        ))),
        array(
          'formValues' => array(
            'linkType' => 'page',
            'pageId' => 'page-id-01',
            'pageAnchor' => 'no-#-at-begin'
        )),
        '/path/to/page-id-01/index.html',
      ),
      // internal link with anchor
      array(
        array(
          'navigation' => array(
            'page-id-01' => array(
              'url' => '/path/to/page-id-01/index.html',
              'inNavigation' => true,
              'children' => array()
        ))),
        array(
          'formValues' => array(
            'linkType' => 'page',
            'pageId' => 'page-id-01',
            'pageAnchor' => '#my"anchor'
         )),
        '/path/to/page-id-01/index.html#my&quot;anchor',
      ),
      // external link
      array(
        array(),
        array(
          'formValues' => array(
            'linkType' => 'external',
            'externalUrl' => 'http://www.my.external.url.com/test.html'
        )),
        'http://www.my.external.url.com/test.html',
      ),
      // mdb link
      array(
        array(
          'mediaItems' => array(
            'mdb-id-01' => array(
              'url' => 'url://mdb-id-01.png',
        ))),
        array(
          'formValues' => array(
            'linkType' => 'download',
            'downloadId' => 'mdb-id-01',
        )),
        'url://mdb-id-01.png',
      ),
      // mdb download link
      array(
        array(
          'mediaItems' => array(
            'mdb-id-01' => array(
              'downloadUrl' => 'url://mdb-id-01.png/download',
            ))),
        array(
          'formValues' => array(
            'linkType' => 'download',
            'downloadId' => 'mdb-id-01',
            'downloadSaveDialog' => true,
          )),
        'url://mdb-id-01.png/download',
      ),
      // mdb download link
      array(
        array(),
        array(
          'formValues' => array(
            'linkType' => 'mailto',
            'mailtoEmail' => 'info@rukzuk.com',
          )),
        'mailto:info@rukzuk.com',
      ),
    );
  }

  /**
   * @dataProvider provider_test_getUrl_dataNotExists
   */
  public function test_getUrl_dataNotExists($unitArray)
  {
    // ARRANGE
    $api = $this->createApiMock();
    $unit = $this->createUnit($unitArray);
    $module = $this->createModule();

    // ACT
    $url = $module->getUrl($api, $unit);

    // ASSERT
    $this->assertEquals('#', $url);
  }

  /**
   * @return array
   */
  public function provider_test_getUrl_dataNotExists()
  {
    return array(
      array(
        'formValues' => array(
          'linkType' => 'page',
          'pageId' => 'page-id-not-exists',
        )
      ),
      array(
        'formValues' => array(
          'linkType' => 'external',
          'externalUrl' => '',
        )
      ),
      array(
        'formValues' => array(
          'linkType' => 'download',
          'downloadId' => 'mdb-id-not-exists',
        )
      ),
      array(
        'formValues' => array(
          'linkType' => 'mailto',
          'mailtoEmail' => '',
        )
      ),
      array(
        'formValues' => array(
          'linkType' => 'unknown-type',
        )
      ),
    );
  }

  private function createApiMock($conf = null)
  {
    return new rz_link_RenderTest_ApiMock($conf);
  }

}
