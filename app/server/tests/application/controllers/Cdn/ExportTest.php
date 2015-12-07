<?php
namespace Application\Controller\Cdn;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * CdnController Export Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class ExportTest extends ControllerTestCase
{
  const EXPORT_FILE_EXTENSION = \Cms\Business\Export::EXPORT_FILE_EXTENSION;

  /**
   * @test
   * @group integration
   */
  public function exportWithIncompleteRequestErrorShouldResondWithHtmlAndIframe()
  {
    $this->dispatch('/cdn/export');

    $response = $this->getResponse();
    $this->assertSame(200, $response->getHttpResponseCode());
    $this->assertEquals(0, $response->getTestCallbackCallCount());
    $this->assertStringStartsWith('<html language="de">', $response->getBody());
    
    $this->assertGreaterThan(0, strpos($response->getBody(), 'iframe'));
    
    $html = new \SimpleXMLElement($response->getBody());
    
    $foo = $html->xpath('///div');
    $errorJson = null;
    while (list( , $node) = each($foo)) {
      $errorJson = json_decode($node, true);
    }
    $expectedError['success'] = false;
    $expectedError['errors'] = array(
      'code' => 1, 
      'message' => 'Unvollstaendiger Request'
    );
    $this->assertEquals($expectedError, $errorJson);
  }
  /**
   * @test
   * @group integration
   */
  public function exportShouldShouldResondWithHtmlAndIframeOnNonExistingExportDirectory()
  {
    $exportName = 'test_export_404';
    $requestUri = sprintf(
      '/cdn/export/params/{"name":"%s"}',
      $exportName
    );
    $this->dispatch($requestUri);

    $response = $this->getResponse();
    $this->assertSame(200, $response->getHttpResponseCode());
    $this->assertEquals(0, $response->getTestCallbackCallCount());
    $this->assertStringStartsWith('<html language="de">', $response->getBody());
    
    $this->assertGreaterThan(0, strpos($response->getBody(), 'iframe'));
    
    $html = new \SimpleXMLElement($response->getBody());
    
    $foo = $html->xpath('///div');
    $errorJson = null;
    while (list( , $node) = each($foo)) {
      $errorJson = json_decode($node, true);
    }
    
    unset($errorJson['errors']['message']);
    
    $expectedError['success'] = false;
    $expectedError['errors'] = array(
      'code' => 1, 
    );
    $this->assertEquals($expectedError, $errorJson);
  }
  /**
   * @test
   * @group integration 
   */
  public function exportShouldDeliverExportAsExpected()
  {
    $config = Registry::getConfig();
    $exportName = 'test_export_cdn_delivery';
    $expectedExportFileName = $exportName . '.' . self::EXPORT_FILE_EXTENSION;
    $exportBaseDirectory = $config->export->directory;
    $exportZipFile = $exportBaseDirectory 
      . DIRECTORY_SEPARATOR . md5($exportName)
      . DIRECTORY_SEPARATOR . md5($exportName)
      . '.' . self::EXPORT_FILE_EXTENSION;
    $this->copyExpectedExport($exportZipFile);
    $this->assertFileExists($exportZipFile);
    $expectedContent = file_get_contents($exportZipFile);

    $requestUri = sprintf(
      '/cdn/export/params/{"name":"%s"}',
      $exportName
    );
    $this->dispatch($requestUri);

    if (file_exists($exportZipFile)) {
      $this->fail('Export file "'.$exportZipFile.'" exists after request!');
    }

    $response = $this->getResponse();
    $this->assertSame(200, $response->getHttpResponseCode());
    $this->assertEquals(1, $response->getTestCallbackCallCount());
    $reponseHeaders = $response->getHeaders();

    $expectedHeaders = array(
      'Content-Type' => 'application/octet-stream',
      'Content-Disposition' => 'attachment; filename="' . $expectedExportFileName . '"',
      'Last-Modified' => 'Wed, 2 Mar 2011 10:51:20 GMT',
      'Content-Length' => '300'
    );

    $actualHeadersLeaned = array();
    foreach ($reponseHeaders as $reponseHeader) {
      $actualHeadersLeaned[$reponseHeader['name']] = $reponseHeader['value'];
    }

    foreach ($expectedHeaders as $expectedHeaderName => $expectedHeaderValue) {
      $this->assertArrayHasKey($expectedHeaderName, $actualHeadersLeaned);
      if ($expectedHeaderName !== 'Last-Modified') {
        $this->assertSame(
          $expectedHeaderValue,
          $actualHeadersLeaned[$expectedHeaderName]
        );
      }
    }

    $callbackOutput = $response->getTestCallbackOutput();
    $this->assertEquals($expectedContent, $callbackOutput[0]);
  }
  /**
   * @param string $exportZipFile
   */
  private function copyExpectedExport($exportZipFile)
  {
    $exportDirectory = dirname($exportZipFile);
    $exportExpectedDirectory = $exportDirectory . '_expected';
    $exportZipFileName = basename($exportZipFile);

    $exportExpectedZipFileName = $exportExpectedDirectory
      . DIRECTORY_SEPARATOR . $exportZipFileName;
    
    mkdir($exportDirectory);
    copy($exportExpectedZipFileName, $exportZipFile);
  }

}