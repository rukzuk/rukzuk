<?php
namespace Cms\Business\User;

use Cms\Business\User as UserBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetAllSuperusersTest
 *
 * @package      Test
 * @subpackage   Business
 */
class GetAllSuperusersTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Album
   */
  protected $business;
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new UserBusiness('User');
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