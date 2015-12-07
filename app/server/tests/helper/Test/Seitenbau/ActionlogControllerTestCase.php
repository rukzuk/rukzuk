<?php
namespace Test\Seitenbau;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\Cms\Response as Response;

/**
 * ActionLogTestCase
 *
 * @package      Seitenbau
 */
class ActionlogControllerTestCase extends ControllerTestCase
{
  protected function removeWebsiteBuilds()
  {
    $config = Registry::getConfig();
    $buildsDirectory = $config->builds->directory;
    
    DirectoryHelper::removeRecursiv($buildsDirectory);
  }
  /**
   * @param string $websiteId
   * @param string $expectedId
   * @param string $expectedUserlogin 
   * @param string $expectedAction 
   * @param array  $expectedAdditionalinfo 
   */
  protected function assertActionLogEntry($websiteId, $expectedId, 
    $expectedUserlogin, $expectedAction, $expectedAdditionalinfo=array())
  {
    $getLogRequest = sprintf(
      'log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );
    
    $this->dispatch($getLogRequest);
    $response = $this->getResponseBody();
    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
    
    $responseData = $response->getData();
    
    $this->assertInternalType('array', $responseData);
    
    $expectedLogEntriesCount = 1;
    $this->assertSame($expectedLogEntriesCount, count($responseData));
    
    $expectedLogKeys = array(
      'id',
      'name',
      'dateTime',
      'userlogin',
      'action',
      'additionalinfo',
    );
    
    $this->assertInstanceOf('stdClass', $responseData[0]);
    $actualLogKeys = array_keys(get_object_vars($responseData[0]));
    
    sort($actualLogKeys);
    sort($expectedLogKeys);
    
    $this->assertSame($expectedLogKeys, $actualLogKeys);
    
    $this->assertSame($expectedId, $responseData[0]->id);
    $this->assertSame($expectedUserlogin, $responseData[0]->userlogin);
    $this->assertSame($expectedAction, $responseData[0]->action);
    if (count($expectedAdditionalinfo) > 0) {
      $this->assertObjectHasAttribute('additionalinfo',  $responseData[0]);
      $actualAdditionalInfo = json_decode($responseData[0]->additionalinfo, false);
      foreach ($expectedAdditionalinfo as $key => $expectedValue) {
        $this->assertObjectHasAttribute($key, $actualAdditionalInfo);
        $this->assertSame($expectedValue, $actualAdditionalInfo->$key);
      }
    }
    
    return $responseData[0];
  }
}