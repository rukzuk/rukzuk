<?php

namespace Dual\Media;

use Cms\Service\Website as WebsiteService,
    Dual\Render\CurrentSite as CurrentSite;
use Test\Seitenbau\TransactionTestCase;

class CurrentSiteTest extends TransactionTestCase
{

  public $sqlFixtures = array('MediaDbTest.json');

  /**
   * @test
   * @group library
   *
   * Tests that CurrentSite::getColorById returns null for a missing or unknown ColorID.
   */
  public function test_getColorById_return_id_for_missing_id()
  {
    // Arrange
    $this->initSite('SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE');

    // Act
    $color = CurrentSite::getColorById('missingId');

    // Assert
    $this->assertEquals($color, 'missingId');
  }

  /**
   * @test
   * @group library
   *
   * Tests that CurrentSite::getColorById returns null for a missing or unknown ColorID.
   */
  public function test_getColorById_return_id_for_correct_id()
  {
    // Arrange
    $this->initSite('SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE');

    // Act
    $color = CurrentSite::getColorById('4e8af5a2-48ca-41f6-bccd-21cd98d322eb');

    // Assert
    $this->assertEquals('rgba(66,149,209,1)', $color);
  }

  protected function  initSite($siteId)
  {
    $websiteService = new WebsiteService('Website');
    $missingId = 'SITE-20b2394c-b41c-490f-1111-70bb15968c52-SITE';
    $websiteData = $websiteService->getById($missingId);
    $renderWebsite = new \Dual\Render\Website();
    $renderWebsite->setArray($websiteData->toArray());
    CurrentSite::setSite($renderWebsite);
  }


}
