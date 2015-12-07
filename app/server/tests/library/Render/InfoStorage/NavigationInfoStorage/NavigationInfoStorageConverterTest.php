<?php
namespace Render\InfoStorage\NavigationInfoStorage;


use Cms\Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageConverter;
use Render\PageUrlHelper\NonePageUrlHelper;

/**
 * Class NavigationInfoStorageConverterTest
 * @package Render\InfoStorage\NavigationInfoStorage
 */
class NavigationInfoStorageConverterTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group render
   * @group small
   * @group dev
   */
  public function test_converterCompatibilityToArrayBasedInfoStorage()
  {
    // ARRANGE
    $navArrayIn = array(
      'PAGE-1-PAGE' =>
        array(
          'id' => 'PAGE-1-PAGE',
          'pageType' => 'home',
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
                  'pageType' => 'page',
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
                          'pageType' => 'page',
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

    // ACT
    $navInfoStorage = new ArrayBasedNavigationInfoStorage($navArrayIn, null, new NonePageUrlHelper());
    $helper = new NavigationInfoStorageConverter($navInfoStorage);
    $navArrayOut = $helper->extractNavigationArray();

    // ASSERT
    $this->assertEquals($navArrayOut, $navArrayIn);
  }

  /**
   * @test
   * @group render
   * @group small
   * @group dev
   */
  public function test_extractPageAttributes_success()
  {
    // ARRANGE
    $pageId = 'PAGE-1-PAGE';
    $navArrayIn = array(
      $pageId =>
        array(
          'id' => 'PAGE-1-PAGE',
          'pageType' => 'home',
          'name' => 'Test',
          'description' => '',
          'date' => 0,
          'navigationTitle' => '',
          'inNavigation' => false,
          'mediaId' => '',
          'children' => array(),
          'pageAttributes' => array(
            'foo' => 'bar',
            'myArray' => array('foo', 'bar'),
            'myObject' => array(
              'foo' => 'bar',
            )
          )
        ),
    );

    // ACT
    $navInfoStorage = new ArrayBasedNavigationInfoStorage($navArrayIn, null, new NonePageUrlHelper());
    $helper = new NavigationInfoStorageConverter($navInfoStorage);
    $pageAttributesOut = $helper->extractPageAttributes($pageId);

    // ASSERT
    $this->assertEquals($pageAttributesOut, $navArrayIn[$pageId]['pageAttributes']);
  }
}
