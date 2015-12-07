<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Cms\Service\Media\File as FileService,
    Cms\Service\Media\Cache as CacheService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * Delete
 *
 * @package      Application
 * @subpackage   Controller
 */
class DeleteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
  }
  /**
   * @test
   * @group library
   */
  public function deleteShouldDeleteExpectedMediasWhenNotReferencedByPageModulOrTemplate()
  { 
    $deletableIds = array(
      'MDB-0991d0ec-cb0f-4961-92bd-765d4aa581a3-MDB',
      'MDB-12824b9d-426d-4998-af19-959c76d46aaa-MDB',
      'MDB-43789100-f48a-4b52-afaa-912505d548ff-MDB'
    );
    $siteId = 'SITE-ra10e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $this->service->delete($deletableIds, $siteId);
  }
}