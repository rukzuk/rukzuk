<?php
namespace Application\Controller\User;

use Orm\Data\User as DataUser,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * EditTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends ControllerTestCase
{
  protected $userAttr = array();
          
  protected function setUp()
  {
    parent::setUp();
    
    $this->userAttr = array(
      'id' => 'USER-0a67e2c2-1175-45e9-bcbd-a25cee8a74c1-USER',
      'email' => 'test@seitenbau-test.de',
      'lastname' => 'last',
      'firstname' => 'first',
      'gender' => 'm',
      'language' => 'en-US',
      'password' => '****************',
      'superuser' => false,
    );
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidUserIds($userId)
  {
    $userAttr = $this->userAttr;
    $userAttr['id'] = $userId;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('id', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidEmailsProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidEmails($email)
  {
    $userAttr = $this->userAttr;
    $userAttr['email'] = $email;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('email', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidNamesProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidNames($firstname, $lastname)
  {
    $userAttr = $this->userAttr;
    $userAttr['firstname'] = $firstname;
    $userAttr['lastname'] = $lastname;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();

    $expectedErrorFields = array('firstname', 'lastname');
    $actualErrorFields = array();
    foreach ($responseError as $error)
    {
      $actualErrorFields[] = $error->param->field;
    }

    sort($expectedErrorFields);
    sort($actualErrorFields);

    $this->assertSame($expectedErrorFields, $actualErrorFields);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidGendersProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidGenders($gender)
  {
    $userAttr = $this->userAttr;
    $userAttr['gender'] = $gender;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('gender', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidLanguageProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidLanguage($language)
  {
    $userAttr = $this->userAttr;
    $userAttr['language'] = $language;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('language', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidPasswordsProvider
   */
  public function editUserShouldReturnValidationErrorForInvalidPasswords($password)
  {
    $userAttr = $this->userAttr;
    $userAttr['password'] = $password;
    
    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('password', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function editUserShouldDeleteAssociatedOptinAsExpectedWhenPasswordIsSet()
  {
    $userId = 'USER-de67e2cf-1175-45e9-bcbd-a25cee8a74e0-USER';
    
    $optinService = new \Cms\Service\Optin('Optin');
    $registerOptin = $optinService->getDao()->getByUserId($userId);
    $this->assertInstanceOf('\Orm\Entity\OptIn', $registerOptin);
    $this->assertEquals(\Orm\Entity\OptIn::MODE_REGISTER, $registerOptin->getMode());
    
    $lastname = 'edit_user_optin_lastname_0_edit';
    $firstname = 'edit_user_optin_firstname_0_edit';
    $gender = 'm';
    $superuser = 'false';
    $email = 'edit.user.optin0.edit@sbcms.de';
    $password = 'something';
    
    $requestEndpoint = '/user/edit/params/{"id":"%s","email":"%s","lastname":"%s",'
      . '"firstname":"%s","gender":"%s","password":"%s","superuser":"%s"}';
    
    $createRequest = sprintf(
      $requestEndpoint,
      $userId,
      $email,
      $lastname,
      $firstname,
      $gender,
      $password,
      $superuser
    );
    
    $this->dispatch($createRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertTrue($response->getSuccess());
    
    try {
      $optinService->getDao()->getByUserId($userId);
      $this->fail('An expected exception has not been raised.');
    } catch (\Exception $e) {
      $this->assertInstanceOf('Cms\Exception', $e);
    }
  }
  /**
   * @test
   * @group integration
   */
  public function editUserShouldNotDeleteAssociatedOptinAsExpectedWhenPasswordIsNotSet()
  {
    $userId = 'USER-de67e2cf-1175-45e9-bcbd-a25cee8a74e0-USER';
    
    $optinService = new \Cms\Service\Optin('Optin');
    $registerOptinPre = $optinService->getDao()->getByUserId($userId);
    $this->assertInstanceOf('\Orm\Entity\OptIn', $registerOptinPre);
    $this->assertEquals(\Orm\Entity\OptIn::MODE_REGISTER, $registerOptinPre->getMode());
    
    $lastname = 'edit_user_optin_lastname_0_edit';
    $firstname = 'edit_user_optin_firstname_0_edit';
    $gender = 'm';
    $superuser = 'false';
    $email = 'edit.user.optin0.edit@sbcms.de';
    
    $requestEndpoint = '/user/edit/params/{"id":"%s","email":"%s","lastname":"%s",'
      . '"firstname":"%s","gender":"%s","superuser":"%s"}';
    
    $createRequest = sprintf(
      $requestEndpoint,
      $userId,
      $email,
      $lastname,
      $firstname,
      $gender,
      $superuser
    );
    
    $this->dispatch($createRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertTrue($response->getSuccess());
    
    $registerOptinPost = $optinService->getDao()->getByUserId($userId);
    $this->assertInstanceOf('\Orm\Entity\OptIn', $registerOptinPost);
    $this->assertEquals(\Orm\Entity\OptIn::MODE_REGISTER, $registerOptinPost->getMode());
    $this->assertEquals($registerOptinPre, $registerOptinPost);
  }
  /**
   * @test
   * @group integration
   */
  public function editUserShouldAlterUserAsExpected()
  {
    $userAttr = array(
      'id'        => 'USER-0a67e2c2-1175-45e9-bcbd-a25cee8a74c1-USER',
      'lastname'  => 'edit_test_lastname',
      'firstname' => 'edit_test_firstname',
      'gender'    => 'm',
      'language'  => 'en-US',
      'superuser' => false,
      'email'     => 'test@seitenbau-test.de',
      'password'  => '*******',
    );

    $this->dispatchWithParams('/user/edit', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->dispatchWithParams('/user/getbyid', array('id' => $userAttr['id']));
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualUserData = $response->getData();
    $this->assertNotEmpty($actualUserData);


    $expectedUserValues = $userAttr;
    $expectedUserValues['groups'] = null;
    $expectedUserValues['isdeletable'] = false;
    $expectedUserValues['owner'] = false;
    $expectedUserValues['readonly'] = false;
    unset($expectedUserValues['password']);

    $actualUserFields = array_keys(get_object_vars($actualUserData));
    sort($actualUserFields);
    $expectedUserFields = array_keys($expectedUserValues);
    sort($expectedUserFields);
    $this->assertEquals($expectedUserFields, $actualUserFields);
    
    foreach ($actualUserData as $key => $value)
    {
      $this->assertSame($expectedUserValues[$key], $value);
    }
  }
  /**
   * @test
   * @group integration
   */
  public function editUserShouldAlterOnlyEmailGenderAndSuperuserStatus()
  {
    $userId = 'USER-ed6r9sub-1175-45e9-bcbd-a25cee8a74c1-USER';
    $gender = 'f';
    $superuser = 'true';
    $email = 'test.edit@seitenbau-test.de';

    $requestEndpoint = '/user/edit/params/{"id":"%s","email":"%s","gender":"%s",'
      . '"superuser":"%s"}';

    $createRequest = sprintf(
      $requestEndpoint,
      $userId,
      $email,
      $gender,
      $superuser
    );

    $this->dispatch($createRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $getByIdRequest = sprintf('/user/getbyid/params/{"id":"%s"}',
      $userId
    );

    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualUserData = $response->getData();
    $this->assertNotEmpty($actualUserData);

    $actualUserFields = array_keys(get_object_vars($actualUserData));

    $expectedUserValues = array(
      'id' => $userId,
      'lastname' => 'lastname_edit_subset',
      'firstname' => 'firstname_edit_subset',
      'gender' => $gender,
      'email' => $email,
      'language' => null,
      'superuser' => true,
      'isdeletable' => true,
      'groups' => null,
      'owner' => false,
      'readonly' => false,
    );

    foreach ($actualUserData as $key => $value)
    {
      $this->assertSame($expectedUserValues[$key], $value);
    }
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-458
   */
  public function editUserShouldAlterEmailWhenNotAlreadyTaken()
  {
    $userId = 'USER-ed6r9sub-1175-45e9-bcbd-a25cee8a74c1-USER';
    $gender = 'f';
    $superuser = 'true';
    $email = 'sbcms.test.edit@seitenbau.com';

    $requestEndpoint = '/user/edit/params/{"id":"%s","email":"%s","gender":"%s",'
      . '"superuser":"%s"}';

    $createRequest = sprintf(
      $requestEndpoint,
      $userId,
      $email,
      $gender,
      $superuser
    );

    $this->dispatch($createRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $getByIdRequest = sprintf('/user/getbyid/params/{"id":"%s"}',
      $userId
    );

    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualUserData = $response->getData();
    $this->assertNotEmpty($actualUserData);

    $actualUserFields = array_keys(get_object_vars($actualUserData));

    $expectedUserValues = array(
      'id' => $userId,
      'lastname' => 'lastname_edit_subset',
      'firstname' => 'firstname_edit_subset',
      'gender' => $gender,
      'email' => $email,
      'language' => null,
      'superuser' => true,
      'isdeletable' => true,
      'groups' => null,
      'owner' => false,
      'readonly' => false,
    );

    foreach ($actualUserData as $key => $value)
    {
      $this->assertSame($expectedUserValues[$key], $value);
    }
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-458
   */
  public function editUserShouldReturnErrorForAlreadyTakenEmail()
  {
    $userId = 'USER-ed6r9sub-1175-45e9-bcbd-a25cee8a74c1-USER';
    $gender = 'f';
    $superuser = 'true';
    $email = 'already.taken.email@seitenbau-test.de';

    $requestEndpoint = '/user/edit/params/{"id":"%s","email":"%s","gender":"%s",'
      . '"superuser":"%s"}';

    $createRequest = sprintf(
      $requestEndpoint,
      $userId,
      $email,
      $gender,
      $superuser
    );

    $this->dispatch($createRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();

    $assertionMessage = 'Error code liegt nicht zwischen 1000 <> 1100';
    $this->assertGreaterThanOrEqual(1000, $responseError[0]->code, $assertionMessage);
    $this->assertLessThan(1100, $responseError[0]->code, $assertionMessage);
  }

  /**
   * @return array
   */
  public function invalidUserIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
  }
  /**
   * @return array
   */
  public function invalidGendersProvider()
  {
    return array(
      array(array()),
      array(15),
      array('b'),
    );
  }
  /**
   * @return array
   */
  public function invalidPasswordsProvider()
  {
    return array(
      array('', ''),
      array(1, 2),
      array('a', 'c'),
      array(str_repeat('toolongpassword', 24), str_repeat('toolongpassword', 24)),
    );
  }
  /**
   * @return array
   */
  public function invalidNamesProvider()
  {
    return array(
      array('', ''),
      array(1, 2),
      array('a', 'c'),
      array(str_repeat('toolongname', 24), str_repeat('toolongname', 24)),
    );
  }
  /**
   * @return array
   */
  public function invalidEmailsProvider()
  {
    return array(
      array(''),
      array(15),
      array('www.seitenbau.com'),
      array('test.name'),
    );
  }
  /**
   * @return array
   */
  public function invalidLanguageProvider()
  {
    return array(
      array(array()),
      array('NO_LANGUAGE'),
      array('"'),
    );
  }
}