<?php
namespace application\controllers\Cli;


use Test\Seitenbau\ControllerTestCase;

/**
 * CliController last test
 *
 * Class LastLoginTest
 * @package application\controllers\Cli
 */
class LastLoginTest extends ControllerTestCase
{
  public $sqlFixturesForTestMethod = array(
    'test_lastlogin_success' => array('UserStatus.json'),
  );

  /**
   * @test
   * @group integration
   */
  public function test_lastlogin_null()
  {
    // ACT
    $this->dispatchWithParams('cli/lastlogin', array());

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('lastlogin', $responseData);
    $this->assertNull($responseData->lastlogin);
  }

  /**
   * @test
   * @group integration
   */
  public function test_lastlogin_success()
  {
    // ACT
    $this->dispatchWithParams('cli/lastlogin', array());

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('lastlogin', $responseData);
    $this->assertEquals($responseData->lastlogin, "2015-06-07T06:09:10+00:00");
  }
}
 