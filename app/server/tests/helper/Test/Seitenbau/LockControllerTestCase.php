<?php
namespace Test\Seitenbau;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * LockControllerTestCase
 *
 * @package      Seitenbau
 */
class LockControllerTestCase extends ControllerTestCase
{
  /**
   * Benutzer am System anmelden
   */
  protected function doLogin($userNr, $logout)
  {
    if ($logout) {
      \Cms\Access\Manager::singleton()->logout();
    }
    $userName = sprintf('lock_test_user_%d@sbcms.de', $userNr);
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->activateGroupCheck();
  }

  /**
   * Existiert ein bestimmter Lock
   */
  protected function lockExists($runId, $userId, $websiteId, $id, $type, $returnLock=false)
  {
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId);
    $request = '/lock/getAll/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseJsonObj = json_decode($response);
    $this->assertResponseBodySuccess($responseJsonObj);
    $response = new Response($response);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('locks', $responseData);
    $this->assertInternalType('array', $responseData->locks);
    foreach ($responseData->locks as $nextLock)
    {
      if ($websiteId == $nextLock->websiteid && $id == $nextLock->id
          && $type == $nextLock->type && $userId == $nextLock->userid
          && $runId == $nextLock->runid)
      {
        // Lock existiert
        return ($returnLock === true ? $nextLock : true);
      }
    }

    // Lock existiert nicht
    return false;
  }
}