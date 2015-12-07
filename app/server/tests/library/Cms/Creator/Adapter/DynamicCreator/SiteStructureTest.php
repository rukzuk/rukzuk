<?php
namespace Cms\Creator\Adapter\DynamicCreator;

/**
 * Class SiteStructureTest
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class SiteStructureTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_initFormArrayToArray()
  {
    // ARRANGE
    $input = array(
      'reserveddirectories' => array(),
      'homepageid' => '12',
      'pageurls' => array(
        '1' => array('path' => '', 'fileName' => 'index.php'),
        '2' => array('path' => 'PAGE-URL', 'fileName' => 'index.php'),
      ),
      'pagestructure' => array(),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMock());

    // ACT
    $siteStructure->initFromArray($input);
    $output = $siteStructure->toArray();

    // ASSERT
    $this->assertEquals($input, $output);
  }


  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageUrlUsingGeneratedNavigationName()
  {
    // ARRANGE
    $navArray = array(
      'PAGE-1-PAGE' =>
        array(
          'id' => 'PAGE-1-PAGE',
          'name' => 'Test',
          'description' => '',
          'date' => 0,
          'navigationTitle' => '',
          'inNavigation' => false,
          'mediaId' => '',
          'children' =>
            array(
              'PAGE-2-PAGE' =>
                array(
                  'id' => 'PAGE-2-PAGE',
                  'name' => 'Test Sub 1',
                  'description' => '',
                  'date' => 0,
                  'navigationTitle' => '',
                  'inNavigation' => false,
                  'mediaId' => '',
                  'children' =>
                    array(
                      'PAGE-3-PAGE' =>
                        array(
                          'id' => 'PAGE-3-PAGE',
                          'name' => 'Test Sub Sub 2',
                          'description' => '',
                          'date' => 0,
                          'navigationTitle' => '',
                          'inNavigation' => true,
                          'mediaId' => '',
                        ),
                    ),
                ),
            ),
        ),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMockWithNavigation($navArray));

    $siteStructure->initByWebsiteId('');

    // ACT
    $pageUrl = $siteStructure->getPageUrl('PAGE-3-PAGE', false);

    // ASSERT
    $this->assertEquals('Test-Sub-1/Test-Sub-Sub-2/', $pageUrl);
  }

  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageDepthUsingNavigation()
  {
    // ARRANGE
    $navArray = array(
      'PAGE-1-PAGE' =>
        array(
          'id' => 'PAGE-1-PAGE',
          'name' => 'Test',
          'description' => '',
          'date' => 0,
          'navigationTitle' => '',
          'inNavigation' => false,
          'mediaId' => '',
          'children' =>
            array(
              'PAGE-2-PAGE' =>
                array(
                  'id' => 'PAGE-2-PAGE',
                  'name' => 'Test Sub 1',
                  'description' => '',
                  'date' => 0,
                  'navigationTitle' => '',
                  'inNavigation' => false,
                  'mediaId' => '',
                  'children' =>
                    array(
                      'PAGE-3-PAGE' =>
                        array(
                          'id' => 'PAGE-3-PAGE',
                          'name' => 'Test Sub Sub 2',
                          'description' => '',
                          'date' => 0,
                          'navigationTitle' => '',
                          'inNavigation' => true,
                          'mediaId' => '',
                        ),
                    ),
                ),
            ),
        ),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMockWithNavigation($navArray));

    $siteStructure->initByWebsiteId('');

    // ACT
    $depth = $siteStructure->getPageDepth('PAGE-3-PAGE');

    // ASSERT
    $this->assertEquals(2, $depth);
  }

  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageUrlUsingGeneratedNavigationNameWithSpecialChars()
  {
    // ARRANGE
    $navArray = array(
      'PAGE-1-PAGE' =>
        array(
          'id' => 'PAGE-1-PAGE',
          'name' => 'Test',
          'description' => '',
          'date' => 0,
          'navigationTitle' => '',
          'inNavigation' => false,
          'mediaId' => '',
          'children' =>
            array(
              'PAGE-2-PAGE' =>
                array(
                  'id' => 'PAGE-2-PAGE',
                  'name' => 'Wow $ in % and äöü â á ß 漢字/汉字',
                  'description' => '',
                  'date' => 0,
                  'navigationTitle' => '',
                  'inNavigation' => false,
                  'mediaId' => '',
                ),
            ),
        ),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMockWithNavigation($navArray));

    $siteStructure->initByWebsiteId('');

    // ACT
    $pageUrl = $siteStructure->getPageUrl('PAGE-2-PAGE', false);

    // ASSERT
    $this->assertEquals('Wow-in-and-aeoeue-ss-/', $pageUrl);
  }

  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageDepth()
  {
    // ARRANGE
    $input = array(
      'reserveddirectories' => array(),
      'homepageid' => '12',
      'pageurls' => array(
        '1' => array('path' => '', 'fileName' => 'index.php', 'depth' => 23),
        '2' => array('path' => 'PAGE-URL', 'fileName' => 'index.php', 'depth' => 12),
        '12' => array('path' => 'HOME-PAGE-URL', 'fileName' => 'index.php', 'depth' => 14),
        '33' => array('path' => 'THE-PAGE-URL', 'fileName' => 'someFile.ext', 'depth' => 51),
      ),
      'pagestructure' => array(),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMock());
    $siteStructure->initFromArray($input);

    // ACT
    $depth = $siteStructure->getPageDepth('12');

    $depthNone = $siteStructure->getPageDepth('NONE');

    // ASSERT
    $this->assertEquals(14, $depth);
    $this->assertEquals(0, $depthNone);
  }


  /**
   * @test
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageUrl()
  {
    // ARRANGE
    $input = array(
      'reserveddirectories' => array(),
      'homepageid' => '12',
      'pageurls' => array(
        '1' => array('path' => '', 'fileName' => 'index.php'),
        '2' => array('path' => 'PAGE-URL', 'fileName' => 'index.php'),
        '12' => array('path' => 'HOME-PAGE-URL', 'fileName' => 'index.php'),
        '33' => array('path' => 'THE-PAGE-URL', 'fileName' => 'someFile.ext'),
      ),
      'pagestructure' => array(),
    );

    $siteStructure = new SiteStructure($this->getCreatorContextMock());
    $siteStructure->initFromArray($input);

    // ACT
    $url = $siteStructure->getPageUrl('2');
    $urlNoIndexFile = $siteStructure->getPageUrl('2', false);

    $homeUrl = $siteStructure->getPageUrl('1', true);
    $homeUrlNoIndexFile = $siteStructure->getPageUrl('1', false);

    $nonIndexFileUrl = $siteStructure->getPageUrl('33', false);


    // ASSERT
    $this->assertEquals('PAGE-URL/index.php', $url);
    $this->assertEquals('PAGE-URL/', $urlNoIndexFile);
    $this->assertEquals('', $homeUrlNoIndexFile);
    $this->assertEquals('index.php', $homeUrl);
    $this->assertEquals('THE-PAGE-URL/someFile.ext', $nonIndexFileUrl);
  }


  /**
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContextMock()
  {
    $stub = $this->getMockBuilder('\Cms\Creator\CreatorContext')
      ->disableOriginalConstructor()
      ->getMock();

    return $stub;
  }

  /**
   * @param array $navArray
   *
   *
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContextMockWithNavigation($navArray = array())
  {
    $stub = $this->getMockBuilder('\Cms\Creator\CreatorContext')
      ->disableOriginalConstructor()
      ->getMock();

    $stub->expects($this->any())
      ->method('getNavigation')
      ->will($this->returnCallback(function ($websiteId) use ($navArray) {
        // return faked nav array
        return $navArray;
      }));

    return $stub;
  }

}
 