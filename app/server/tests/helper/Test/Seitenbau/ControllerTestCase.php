<?php
namespace Test\Seitenbau;

use \Test\Rukzuk\AbstractControllerTestCase;
use Cms\Validator\UniqueId as UniqueIdValidator,
    Seitenbau\Registry as Registry,
    Seitenbau\FileSystem as FS,
    Seitenbau\File\TransferFactory as TransferFactory,
    Seitenbau\Validate\File\UploadFactory as UploadValidatorFactory,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Response\HttpTestCase as HttpTestCaseResponse,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager;
use Test\Rukzuk\DBHelper;

/**
 * ControllerTestCase
 *
 * @package      Seitenbau
 */
abstract class ControllerTestCase extends AbstractControllerTestCase
{
  /**
   *
   * @var \Zend_Application
   */
  protected $application;

  /**
   *
   * @var string
   */
  protected $requestParameterName;

  /**
   * @var array
   */
  protected $sqlFixtures = array();

  /**
   * @var array
   */
  protected $sqlFixturesForTestMethod = array();


  protected function setUp()
  {
    $this->resetBootstrap();

    parent::setUp();

    $this->resetResponse();
    $this->resetRequest();

    $this->resetCmsExceptionStack();

    $this->getDbHelper()->setUp($this->getSqlFixtures($this->getName()));

    MockManager::setUp();
  }

  protected function tearDown()
  {
    $this->getDbHelper()->tearDown();
    MockManager::tearDown();
    parent::tearDown();
  }

  protected function resetBootstrap()
  {
    $this->application = new \Zend_Application(
      APPLICATION_ENV,
      Registry::getConfig()
    );
    $this->bootstrap = array($this, 'qsBootstrap');
  }

  /**
   * @param string $testName
   *
   * @return array
   */
  protected function getSqlFixtures($testName)
  {
    if (isset($this->sqlFixturesForTestMethod[$testName])) {
      $sqlLocalFixtures = $this->sqlFixturesForTestMethod[$testName];
    } else {
      $sqlLocalFixtures = array();
    }

    return array_unique(array_merge($this->sqlFixtures, $sqlLocalFixtures));
  }

  public function qsBootstrap()
  {
    $this->application->bootstrap();
    $bootstrap = $this->application->getBootstrap();
    $frontController = $bootstrap->getResource('FrontController');
    $frontController->setParam('bootstrap', $bootstrap);

    $configApplication = $this->application->bootstrap('config');
    $requestConfig = $configApplication->getOption('request');
    $this->requestParameterName = $requestConfig['parameter'];
  }

  public function dispatchWithParams($action, array $params)
  {
    $this->dispatch($action.'/params/'.json_encode($params));
  }

  /**
   * @return string
   */
  public function getResponseBody()
  {
    return $this->getResponse()->getBody();
  }

  /**
   * Assert response body
   *
   * @param  string $responseBody
   * @param  string $message
   * @return void
   */
  public function assertResponseBody($responseBody, $message = '')
  {
    $this->assertSame($responseBody, $this->getResponseBody(), $message);
  }

  /**
   * Prueft ein uebergebes Response-Object einer erfolgreichen Abfrage
   *
   * @param stdClass  $response
   */
  protected function assertResponseBodySuccess(\stdClass $response)
  {
    $this->assertNotNull($response);
    $this->assertResponseValid($response);
    // Success Abschnitt muss true sein
    $this->assertTrue($response->success, "Erfolgreiche Responses müssen 'success' als true zurückgeben:\n".json_encode($response));
    // Error Abschnitt muss leer sein
    $this->assertInternalType('array', $response->error);
    $this->assertSame(0, count($response->error));
  }

  /**
   * @return Response
   */
  protected function getValidatedSuccessResponse()
  {
    $responseRawBody = $this->getResponseBody();
    $this->assertNotNull($responseRawBody);
    $this->assertInternalType('string', $responseRawBody);
    $this->assertResponseBodySuccess(json_decode($responseRawBody, false));
    return new Response($responseRawBody);
  }

  /**
   * @param bool $dataEmpty
   *
   * @return Response
   */
  protected function getValidatedErrorResponse($dataEmpty = true, $expectedErrorParams = null)
  {
    $responseBody = $this->getResponseBody();
    $this->assertNotNull($responseBody);
    $this->assertInternalType('string', $responseBody);
    $response = new Response($responseBody);
    $this->assertFalse($response->getSuccess(), $responseBody);

    foreach ($response->getError() as $error) {
      $this->assertObjectHasAttribute('code', $error, $responseBody);
      $this->assertObjectHasAttribute('logid', $error, $responseBody);
      $this->assertObjectHasAttribute('param', $error, $responseBody);
      $this->assertObjectHasAttribute('text', $error, $responseBody);
      $this->assertNotNull($error->text, 'Fehlerbeschreibung muss bei Errors ausgegeben werden');
      if (is_array($expectedErrorParams)) {
        $this->assertArrayHasKey($error->param->field, $expectedErrorParams, sprintf(
          'Failed asserting that parameter "%s" is invalid', $error->param->field
        ));
        $this->assertEquals($expectedErrorParams[$error->param->field], $error->code);
        unset($expectedErrorParams[$error->param->field]);
      }
    }

    if (is_array($expectedErrorParams)) {
      $this->assertCount(0, $expectedErrorParams, sprintf(
        'Failed asserting that params "%s" valid.', implode(', ', array_keys($expectedErrorParams))
      ));
    }

    // Data Abschnitt muss leer sein
    if ($dataEmpty)
    {
      $this->assertNull($response->data, $responseBody);
    }

    return $response;
  }

  /**
   * Prueft ein uebergebenes Response-Object einer fehlerhaften Abfrage
   *
   * @param \stdClass $response
   * @param boolean   $dataEmpty    Muss der Data Abschnitt leer sein
   */
  protected function assertResponseBodyError(\stdClass $response, $dataEmpty = true)
  {
    $this->assertNotNull($response);
    $this->assertResponseValid($response);
    // Success Abschnitt muss false sein
    $this->assertFalse($response->success);
    // Error Abschnitt muss Fehlermeldungen enthalten
    $this->assertNotNull($response->error);
    $this->assertInternalType('array', $response->error);

    $errors = $response->error;
    foreach ($errors as $error)
    {
      $this->assertObjectHasAttribute('code', $error);
      $this->assertObjectHasAttribute('logid', $error);
      $this->assertObjectHasAttribute('param', $error);
      $this->assertObjectHasAttribute('text', $error);
      $this->assertNotNull($error->text,
        'Fehlerbeschreibung muss bei Errors ausgegeben werden');
    }

    // Data Abschnitt muss leer sein
    if ($dataEmpty)
    {
      $this->assertNull($response->data);
    }
  }
  /**
   * Prueft, ob der Response gueltig aufgebaut ist
   *
   * @param stdClass  $response
   */
  private function assertResponseValid(\stdClass $response)
  {
    $this->assertObjectHasAttribute('success', $response);
    $this->assertObjectHasAttribute('error', $response);
    $this->assertObjectHasAttribute('data', $response);
  }
  /**
   * @param  string $websiteId
   * @param  array  $medias
   * @return boolean
   */
  protected function createTestMedias($websiteId, array $medias)
  {
    $config = Registry::getConfig();
    $testMediaDirectory = $config->media->files->directory;
    if (is_dir($testMediaDirectory))
    {
      $testWebsiteMediaDirectory = $testMediaDirectory
        . DIRECTORY_SEPARATOR . $websiteId;
      if (!is_dir($testWebsiteMediaDirectory))
      {
        mkdir($testWebsiteMediaDirectory);
      }

      foreach ($medias as $name)
      {
        $testMediaFile = $testWebsiteMediaDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testMediaFile, '');
      }
      return true;
    }
    return false;
  }
  /**
   * @param \Orm\Iface\Data\Uuidable $uuidable
   * @param string $id
   * @return boolean
   */
  protected function validateUniqueId($uuidable, $id)
  {
    $uniqueIdValidator = new UniqueIdValidator(
      $uuidable::ID_PREFIX,
      $uuidable::ID_SUFFIX
    );

    if (!$uniqueIdValidator->isValid($id))
    {
      return false;
    }
    return true;
  }

  /**
   * @param string $fileInputname
   * @param string $testUploadFile
   * @param string $testTmpFile
   */
  protected function assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile=null)
  {
    if (is_null($testTmpFile)) {
      $testTmpFile = FS::joinPath(sys_get_temp_dir(), 'php'.md5(time().mt_rand()));
    }

    $_FILES = array(
      $fileInputname => array(
        'name' => basename($testUploadFile),
        'filename' => basename($testUploadFile),
        'tmp_name' => $testTmpFile,
        'error' => 0,
        'size' => 22
      )
    );
    $this->assertTrue(copy($testUploadFile, $testTmpFile));

    UploadValidatorFactory::setValidator(
      new \Test\Seitenbau\Validate\File\UploadMock()
    );

    $fileTransfer = new \Test\Seitenbau\File\Transfer\Adapter\HttpMock();
    $fileTransfer->removeValidator('Upload');
    TransferFactory::setAdapter($fileTransfer);
  }

  /**
   * Fake upload entfernen
   */
  protected function clearFakeUpload()
  {
    TransferFactory::clearAdapter();
    UploadValidatorFactory::clearValidator();
  }
  /**
   * Benutzer anmelden
   *
   * @param string $username
   * @param string $password
   */
  protected function assertSuccessfulLogin($username, $password)
  {
    $this->resetRequest();
    $this->resetResponse();

    $loginRequest = sprintf(
      '/user/login/params/{"username":"%s","password":"%s"}',
      $username,
      $password
    );
    $this->dispatch($loginRequest);
    $response = $this->getResponseBody();
    $response = new Response($response);
    $assertionMessage = sprintf(
      "Loging with username '%s' and password '%s' failed",
      $username,
      $password
    );
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $this->resetRequest();
    $this->resetResponse();
  }

  /**
   * Benutzer abmelden
   */
  protected function assertSuccessfulLogout()
  {
    $this->resetRequest();
    $this->resetResponse();

    $loginRequest = '/user/logout';
    $this->dispatch($loginRequest);
    $response = $this->getResponseBody();
    $response = new Response($response);
    $assertionMessage = "Logout failed";
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $this->resetRequest();
    $this->resetResponse();
  }

  /**
   * Page/Template/Modul oder Website sperren
   *
   * @param string $username
   * @param string $password
   */
  protected function assertSuccessfulLock($runId, $itemId, $websiteId, $type)
  {
    $this->resetRequest();
    $this->resetResponse();

    $params = array('runid' => $runId, 'id' => $itemId,
                    'websiteid' => $websiteId, 'type' => $type);
    $lockRequest = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($lockRequest);
    $response = $this->getResponseBody();
    $response = new Response($response);
    $assertionMessage = sprintf(
      "Lock der/des '%s' mit der Id '%s' failed (%s / %s)!",
      $type,
      $itemId,
      $websiteId,
      $runId
    );
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $this->resetRequest();
    $this->resetResponse();
  }

  /**
   * Spreeung einer/eines Page/Template/Modul oder Website entfernen
   *
   * @param string $username
   * @param string $password
   */
  protected function assertSuccessfulUnlock($runId, $itemId, $websiteId, $type)
  {
    $this->resetRequest();
    $this->resetResponse();

    $params = array('runid' => $runId,
                    'items' => array( array(  'id' => $itemId,
                                              'websiteId' => $websiteId,
                                              'type' => $type) ) );
    $lockRequest = '/lock/unlock/params/'.json_encode($params);
    $this->dispatch($lockRequest);
    $responseBody = $this->getResponseBody();
    $response = new Response($responseBody);
    $assertionMessage = sprintf(
      "Unlock der/des '%s' mit der Id '%s' failed (%s / %s):\n%s",
      $type,
      $itemId,
      $websiteId,
      $runId,
      $responseBody
    );
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $this->resetRequest();
    $this->resetResponse();
  }

  /**
   * Prueft ob die angegebenen Pflichtparameter in den uebergebenen Errors
   * vorhanden sind
   *
   * @param array $requiredParams
   * @param array $errors
   */
  protected function checkRequiredParamsInErrorList(array $requiredParams,
    array $errors
  ){
    foreach ($errors as $error)
    {
      $this->assertSame(3, $error->code, 'Fehler-Code falsch');
      $this->assertObjectHasAttribute('field', $error->param);
      $invalidParams[] = $error->param->field;
    }

    foreach ($requiredParams as $requiredParam)
    {
      $this->assertContains($requiredParam, $invalidParams);
      $requiredParamKey = array_search($requiredParam, $invalidParams);
      unset($invalidParams[$requiredParamKey]);
    }

    $this->assertSame(0, count($invalidParams), '"' .
      implode(', ', $invalidParams) . '" darf/duerfen keine Pflichtparamter sein');
  }

  protected function activateGroupCheck()
  {
    $this->mergeIntoConfig(array('group' => array('check' =>array('activ' => true))));
    $this->assertTrue(Registry::getConfig()->group->check->activ);
  }

  protected function deactivateGroupCheck()
  {
    $this->mergeIntoConfig(array('group' => array('check' =>array('activ' => false))));
    $this->assertFalse(Registry::getConfig()->group->check->activ);
  }

  protected function activateScreenshot()
  {
    $this->mergeIntoConfig(array('screens' => array('activ' => 'yes')));
    $this->assertSame('yes', Registry::getConfig()->screens->activ);
  }

  protected function deactivateScreenshot()
  {
    $this->mergeIntoConfig(array('screens' => array('activ' => 'no')));
    $this->assertSame('no', Registry::getConfig()->screens->activ);
  }

  protected function activateLockCheck()
  {
    $this->mergeIntoConfig(array('lock' => array('check' =>array('activ' => true))));
    $this->assertTrue(Registry::getConfig()->lock->check->activ);
  }

  protected function deactivateLockCheck()
  {
    $this->mergeIntoConfig(array('lock' => array('check' =>array('activ' => false))));
    $this->assertFalse(Registry::getConfig()->lock->check->activ);
  }
  /**
   * @param integer $days
   */
  protected function alterActionLogLifetime($days)
  {
    $this->mergeIntoConfig(array('action' => array('logging' =>array('db' => array('lifetime' => $days)))));
    $this->assertSame($days, Registry::getConfig()->action->logging->db->lifetime);
  }

  protected function deactivateActionLogLifetime()
  {
    $this->mergeIntoConfig(array('action' => array('logging' =>array('db' => array('lifetime' => 0)))));
    $this->assertSame(0, Registry::getConfig()->action->logging->db->lifetime);
  }

  /**
   * @param  array $pagesInNavigation
   * @param  array $flattenPages
   * @return array
   */
  protected function flattenPagesInNavigation($pagesInNavigation, $flattenPages = array())
  {
    foreach ($pagesInNavigation as $index => $page)
    {
      if ($page instanceof \stdClass) {
        $pageArrayObject = new \ArrayObject($page);
        $page = $pageArrayObject->getArrayCopy();
      }

      if (isset($page['children'])
          && count($page['children']) > 0)
      {
        $copiedPage = $page;
        unset($copiedPage['children']);
        $flattenPages[] = $copiedPage;
        $flattenPages = $this->flattenPagesInNavigation($page['children'], $flattenPages);
      } else {
        $flattenPages[] = $page;
        unset($pagesInNavigation[$index]);
      }
    }

    return $flattenPages;
  }

  /**
   * Test Case Response Objekt zurueckgeben
   *
   * @return HttpTestCaseResponse
   */
  public function getResponse()
  {
      if (null === $this->_response) {
          $this->_response = new HttpTestCaseResponse;
      }
      return parent::getResponse();
  }

  /**
   * Liefert den Namen des Request-Parameters, ueber welchen
   * die JSON-Daten uebergeben werden, zurueck
   *
   * @return string
   */
  public function getRequestParameterName()
  {
    return $this->requestParameterName;
  }

  /**
   * Erstellt die Request-URL fuer die uebergebenen Angaben
   *
   * @param  string $controller
   * @param  string $action
   * @param  array  $params
   * @return string
   */
  public function createRequestUrl($controller, $action, $params)
  {
    return sprintf(
      '/%s/%s/%s/%s',
      $controller,
      $action,
      $this->requestParameterName,
      json_encode($params)
    );
  }

  protected function resetCmsExceptionStack()
  {
    \Cms\ExceptionStack::reset();
  }
}