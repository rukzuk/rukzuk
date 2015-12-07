<?php
namespace Cms\Business\Cli;

use Cms\Business\Cli as CliBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Seitenbau\Registry;
use Test\Rukzuk\ConfigHelper;
use Zend_Config;

/**
 * CheckFtpLoginTest
 *
 * @package      Cms
 * @subpackage   Business\Cli
 */
class CheckFtpLoginTest extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @var \Cms\Business\Cli
   */
  private $business;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new CliBusiness('Cli');

    // default config override
    $this->updateConfigModuleEnableDev(true);
  }

  protected function updateConfigModuleEnableDev($enable)
  {
    // set quota in config
    ConfigHelper::mergeIntoConfig(array('quota' => array('module' => array('enableDev' => $enable))));
  }

  /**
   * @test
   * @group library
   */
  public function businessShouldAcceptFtpLogin()
  {
    $username = 'login0@sbcms.de';
    $password = 'TEST09';

    $success = $this->business->checkFtpLogin($username, $password);
    $this->assertTrue($success);
  }
    
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2006
   */
  public function userNotFound()
  {
    $username = 'user_not_exists@sbcms.de';
    $password = 'no_password_exists';

      $this->business->checkFtpLogin($username, $password);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2006
   */
  public function userLoginFaild()
  {
    $username = 'sbcms@seitenbau.com';
    $password = 'wrong_password';

      $this->business->checkFtpLogin($username, $password);
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

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2007
   */
  public function instanceModuleQuotaDisallowsFtpLogin()
  {

    $username = 'login.test1@sbcms.de';
    $password = 'TEST09';

    $this->updateConfigModuleEnableDev(false);

    $this->business->checkFtpLogin($username, $password);
  }

}