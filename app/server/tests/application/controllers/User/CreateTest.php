<?php
namespace Application\Controller\User;

use Orm\Data\User as DataUser,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class CreateTest extends ControllerTestCase
{
  protected $userAttr = array();
          
  protected function setUp()
  {
    parent::setUp();
    
    $this->userAttr = array(
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
   * @dataProvider invalidEmailsProvider
   */
  public function createUserShouldReturnValidationErrorForInvalidEmails($email)
  {
    $userAttr = $this->userAttr;
    $userAttr['email'] = $email;
    
    $this->dispatchWithParams('/user/create', $userAttr);
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
  public function createUserShouldReturnValidationErrorForInvalidNames($firstname, $lastname)
  {
    $userAttr = $this->userAttr;
    $userAttr['firstname'] = $firstname;
    $userAttr['lastname'] = $lastname;
    
    $this->dispatchWithParams('/user/create', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();

    $expectedErrorFields = array('firstname', 'lastname');
    $actualErrorFields = array();
    foreach ($responseError as $error) {
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
  public function createUserShouldReturnValidationErrorForInvalidGenders($gender)
  {
    $userAttr = $this->userAttr;
    $userAttr['gender'] = $gender;
    
    $this->dispatchWithParams('/user/create', $userAttr);
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
  public function createUserShouldReturnValidationErrorForInvalidLanguages($language)
  {
    $userAttr = $this->userAttr;
    $userAttr['language'] = $language;
    
    $this->dispatchWithParams('/user/create', $userAttr);
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
   */
  public function createUserShouldCreateNonSuperuserAsExpected()
  {
    $userAttr = array(
      'lastname'  => 'create_test_lastname',
      'firstname' => 'create_test_firstname',
      'gender'    => 'f',
      'superuser' => false,
      'email'     => 'test@seitenbau-test.de',
      'language'  => 'en-US',
    );

    $this->dispatchWithParams('/user/create', $userAttr);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $userId = $responseData->id;

    $this->assertTrue($this->validateUniqueId(new DataUser, $userId));

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
      'lastname' => 'create_test_lastname',
      'firstname' => 'create_test_firstname',
      'gender' => 'f',
      'language' => 'en-US',
      'email' => 'test@seitenbau-test.de',
      'superuser' => false,
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
   *
   */
  public function createUserShouldReturnErrorForAlreadyTakenEmail()
  {
    $lastname = 'create_test_lastname';
    $firstname = 'create_test_firstname';
    $gender = 'f';
    $superuser = 'false';
    $email = 'already.taken.email@seitenbau-test.de';

    $requestEndpoint = '/user/create/params/{"email":"%s","lastname":"%s",'
      . '"firstname":"%s","gender":"%s","superuser":"%s"}';

    $createRequest = sprintf(
      $requestEndpoint,
      $email,
      $lastname,
      $firstname,
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
  public function invalidNamesProvider()
  {
    return array(
      array(null, null),
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
      array(null),
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