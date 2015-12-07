<?php


namespace Dual\Media;

use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Cms\Service\Media as MediaService;
use Dual\Render\RenderContext;
use Dual\Render\MediaDb;
use Render\IconHelper\SimpleIconHelper;
use Render\ImageToolFactory\SimpleImageToolFactory;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaContext;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\SecureFileValidationHelper;
use Seitenbau\Registry;
use Test\Seitenbau\TransactionTestCase;
use Cms\Service\Website as WebsiteService;
use \Cms\Request\Base as CmsRequestBase;

class MediaDbTest extends TransactionTestCase
{

  public $sqlFixtures = array('MediaDbTest.json');

  public function setUp()
  {
    parent::setUp();
    RenderContext::reset();
    // Render-Typ des RenderContext auf Dynamisch setzen ( muss ganz am Anfang kommen)
    RenderContext::setRenderType(RenderContext::RENDER_DYNAMIC);

  }

  /**
   * @test
   * @group library
   *
   * Tests that MediaDb::getImage returns null for a missing or unknown ID.
   */
  public function test_getById_return_null_for_missing_id()
  {
    // Arrange
    $this->initSite('SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE');

    // Act
    $image = MediaDb::getImage('missingImageId');

    // Assert
    $this->assertNull($image);
  }

  /**
   * @test
   * @group library
   *
   * Tests that MediaDb::getImage returns null for an empty ID.
   */
  public function test_getById_return_null_for_empty_id()
  {
    // Arrange
    $this->initSite('SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE');

    // Act
    $image = MediaDb::getImage('');

    // Assert
    $this->assertNull($image);
  }

  /**
   * @test
   * @group library
   *
   * Tests that MediaDb::getImage returns the image for a correct ID.
   */
  public function test_getById_return_image_for_a_valid_id()
  {
    // Arrange
    $this->initSite('SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE');

    // Act
    $image = MediaDb::getImage('MDB-exp0d0ec-cb0f-4961-bbbb-765d4aa581n2-MDB');

    // Assert
    $this->assertInstanceOf('Dual\Media\Image', $image);
  }

  protected function  initSite($siteId)
  {
    RenderContext::setWebsiteId($siteId);

    /** @var $newRenderContextMock \Render\RenderContext */
    $newRenderContextMock = $this->getMockBuilder('\Render\RenderContext')
      ->disableOriginalConstructor()->getMock();
    $newRenderContextMock->expects($this->any())
      ->method('getMediaContext')->will($this->returnValue($this->createMediaContext($siteId)));

    RenderContext::setNewRenderContext($newRenderContextMock);
  }


  /**
   * @param $websiteId
   *
   * @return MediaContext
   */
  protected function createMediaContext($websiteId)
  {
    $mediaCacheBaseDirectory = Registry::getConfig()->media->cache->directory;
    $mediaCache = new MediaCache($mediaCacheBaseDirectory);
    $validationHelper = new SecureFileValidationHelper($mediaCache, true);
    $cdnUrl = Registry::getConfig()->server->url . '/cdn/get';
    $urlHelper = new CDNMediaUrlHelper($validationHelper, $cdnUrl,
      CmsRequestBase::REQUEST_PARAMETER);
    $mediaService = new MediaService('Media');
    $mediaDirectory = Registry::getConfig()->media->files->directory;
    $mediaDirectory .= DIRECTORY_SEPARATOR . $websiteId;
    $iconHelper = new SimpleIconHelper(
      Registry::getConfig()->file->types->icon->directory,
      'icon_fallback.png'
    );
    $mediaInfoStorage = new ServiceBasedMediaInfoStorage($websiteId,
      $mediaDirectory, $mediaService, $urlHelper, $iconHelper);
    $imageToolFactory = new SimpleImageToolFactory(APPLICATION_PATH . '/../library');
    $mediaContext = new MediaContext($mediaInfoStorage, $imageToolFactory);
    return $mediaContext;
  }


}
