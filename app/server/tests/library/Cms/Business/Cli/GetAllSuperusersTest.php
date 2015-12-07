<?php
namespace Cms\Business\Cli;

use Cms\Business\Cli as CliBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetAllSuperusersTest
 *
 * @package      Cms
 * @subpackage   Business\Cli
 */
class GetAllSuperusersTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Album
   */
  private $business;
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new CliBusiness('Cli');
  }
  /**
   * @test
   * @group library
   */
  public function businessGetAllSuperusersShouldReturnExpectedSuperusers()
  {
    $allSuperusers = $this->business->getAllSuperusers();
    $this->assertTrue((count($allSuperusers) > 0));
    foreach ($allSuperusers as $superuser)
    {
      $this->assertInstanceOf('Cms\Data\User', $superuser);
      $this->assertTrue($superuser->isSuperuser());
    }
  }
}