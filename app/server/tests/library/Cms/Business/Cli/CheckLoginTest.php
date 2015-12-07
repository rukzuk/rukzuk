<?php
namespace Cms\Business\Cli;

use Cms\Business\Cli as CliBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * CheckFtpLoginTest
 *
 * @package      Cms
 * @subpackage   Business\Cli
 */
class CheckLoginTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Cli
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
  public function businessShouldAcceptLogin()
  {
    $username = 'login.test1@sbcms.de';
    $password = 'TEST09';

    $testUser = $this->business->checkLogin($username, $password);

    $this->assertInternalType('array', $testUser);
    $this->assertSame('USER-lo02eaa7-7fc5-464a-bd47-16b3b8af36c0-USER', $testUser['id']);    
    $this->assertSame('login_lastname_1', $testUser['lastname']);    
    $this->assertSame('login_firstname_1', $testUser['firstname']);    
    $this->assertSame('login.test1@sbcms.de', $testUser['email']);    
    $this->assertSame(false, $testUser['superuser']);   
  }
    
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2008
   */
  public function userNotFound()
  {
    $username = 'user_not_exists@sbcms.de';
    $password = 'no_password_exists';

    $this->business->checkLogin($username, $password);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2008
   */
  public function userLoginFaild()
  {
    $username = 'login.test1@sbcms.de';
    $password = 'wrong_password';

    $this->business->checkLogin($username, $password);
  }  

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2007
   */
  public function userHasNoFtpAccess()
  {
    $username = 'login.test1@sbcms.de';
    $password = 'TEST09';

    $this->business->checkFtpLogin($username, $password);
  }  
}