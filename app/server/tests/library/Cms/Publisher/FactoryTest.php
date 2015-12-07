<?php
namespace Cms\Publisher;

/**
 * FactoryTest
 *
 * @package      Cms
 * @subpackage   Publisher
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function factoryThrowsExpectedExceptionWhenPublisherDoesNotExist()
  {
    Factory::get('Gitx');
  }
  /**
   * @test
   * @group library
   */
  public function factoryReturnsChildExtendingCmsPublisher()
  {
    $publisher = Factory::get('Externalrukzukservice');
    $this->assertSame('Cms\Publisher\Publisher', get_parent_class($publisher));
  }
}