<?php
namespace Application\Controller\Feedback;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry;

/**
 * FeedbackController Send Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class SendTest extends ControllerTestCase
{
  private $testFeedbackOutputDir;
  private $testExpectedFeedbackOutputDir;

  protected function setUp()
  {
    parent::setUp();

    $config = Registry::getConfig();
    $this->testFeedbackOutputDir = $config->feedback->file->path;
    $this->testExpectedFeedbackOutputDir = $config->test->output->feedback->directory;
  }
  
  protected function tearDown()
  {
    $this->deleteFilesInOutputDir();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function noParams()
  {
    $this->dispatch('feedback/send/');
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
    
    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('body', $invalidKeys);
  }
  
  /**
   * @test
   * @group integration
   */
  public function success()
  {
    Registry::getConfig()->webhost = 'http://sbcms-test-run';
    Registry::getConfig()->feedback->mail->adress = 'sbcms-testrun-feedback@seitenbau.com';
    
    $params = array(
      'subject' => 'test feedback',
      'body' => 'feedback test content from unittests',
      'email' => 'sbcms-testrun@seitenbau.com',
      'errors' => array(
        array(
          'text' => urlencode('Scriptfehler:\nUncaught Error: chrome.tabs is not supported in content scripts. See the content scripts documentation for more details.\n\n----\n\nZeile 282 in chrome/RendererExtensionBindings'),
          'date' => urlencode('2011-08-12T09:40:22')
        ),
        array(
          'text' => urlencode('Scriptfehler:\nUncaught Error: chrome.tabs is not supported in content scripts. See the content scripts documentation for more details.\n\n----\n\nZeile 282 in chrome/RendererExtensionBindings'),
          'date' => urlencode('2011-08-12T09:40:23')
        ),
      ),
      'userAgent' => urlencode('Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.112 Safari/535.1'),
      'platform' => 'Win32'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('feedback/send/params/' . $paramsAsJson);
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    
    // Erstelltes Feedback einlesen
    $filename = null;
    $handleDir = opendir($this->testFeedbackOutputDir);
    while (false !== ($nextFilename = readdir($handleDir)))
    {
      if ($nextFilename != '' && $nextFilename != '.'
          && $nextFilename != '..' && $nextFilename != '.gitignore')
      {
        $filename = $nextFilename;
        break;
      }
    }
    closedir($handleDir);

    $this->assertNotNull($filename
            , sprintf('Keine Feedbackdatei im Ordner "%s" vorhanden!'
                    , $this->testFeedbackOutputDir)
            );
    
    $feedbackOutputFile = $this->testFeedbackOutputDir . '/' . $filename;
    
    $this->assertFileExists($feedbackOutputFile);
    
    $expectedFile = $this->testExpectedFeedbackOutputDir . '/feedbackSendSuccess.txt';
    
    $this->assertFileEquals($expectedFile, $feedbackOutputFile);;
    
    $this->deleteOutputFile($filename);
  }
  
  private function deleteOutputFile($filename)
  {
    if (file_exists($this->testFeedbackOutputDir . '/' . $filename))
    {
      unlink($this->testFeedbackOutputDir . '/' . $filename);
    }
  }
  
  private function deleteFilesInOutputDir()
  {
    $handleDir = opendir($this->testFeedbackOutputDir);
    while (false !== ($file = readdir($handleDir))) 
    {
      if ($file != '.' && $file != '..' && $file != '.gitignore')
      {
        $this->deleteOutputFile($file);
      }
    }
    closedir($handleDir);
  }
}