<?php

namespace application\controllers\Cli;


use Test\Seitenbau\ControllerTestCase;

/**
 * CliController initSystem test
 *
 * Class CreateUserTest
 * @package application\controllers\Cli
 */
class InitSystemTest extends ControllerTestCase
{
  protected function tearDown()
  {
    $this->getDbHelper()->markAsDirty();
    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function test_initSystemSuccess()
  {
    // ACT
    $this->dispatch('/cli/initSystem');

    // ASSERT
    $this->getValidatedSuccessResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function test_initSystemCreateUserAsExpected()
  {
    // ARRANGE
    $expectedUser = array(
      'email' => 'CliControllerInitSystem@rukzuk.com',
      'lastname' => 'CliController',
      'firstname' => 'initSystem',
      'gender' => 'f',
      'language' => 'en',
      'sendregistermail' => false,
    );

    // ACT
    $url = sprintf('/cli/initSystem/params/%s',
      urlencode(json_encode($expectedUser)));
    $this->dispatch($url);

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertInternalType('object', $responseData);
    $this->assertAttributeNotEmpty('id', $responseData);
    $actualUser = $this->getUserBusiness()->getById($responseData->id);
    $this->assertEquals($expectedUser['email'], $actualUser->getEmail());
    $this->assertEquals($expectedUser['lastname'], $actualUser->getLastname());
    $this->assertEquals($expectedUser['firstname'], $actualUser->getFirstname());
    $this->assertEquals($expectedUser['gender'], $actualUser->getGender());
    $this->assertEquals($expectedUser['language'], $actualUser->getLanguage());
    $this->assertTrue($actualUser->isSuperuser());
    $this->assertFalse($actualUser->isDeletable());
    $this->assertFalse($actualUser->isOwner());
  }

  /**
   * @return \Cms\Business\User
   */
  protected function getUserBusiness()
  {
    return new \Cms\Business\User('User');
  }
}
 