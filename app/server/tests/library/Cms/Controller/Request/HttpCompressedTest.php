<?php
namespace Cms\Controller\Request;

use Seitenbau\Registry,
    Cms\Controller\Request\HttpCompressed as HttpRequestCompressed;

/**
 * HttpCompressedTest
 *
 * @package      Cms
 * @subpackage   Controller\Request
 */
class HttpCompressedTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function successfullyDecompressGzRawPostData()
  {
    $compressedRequestFileDirectory =
        Registry::getConfig()->test->request->storage->directory .
       DIRECTORY_SEPARATOR . 'compressed' . DIRECTORY_SEPARATOR;
    $compressedRawBodyFile = $compressedRequestFileDirectory . 'gzCompressed.body';
    $decompressedParamFile = $compressedRequestFileDirectory . 'gzDeompressedParams.php';
    
    $this->assertFileExists($compressedRawBodyFile, 'Komprimierte Test-Request-Datei nicht gefunden: ' . $compressedRawBodyFile);
    $this->assertFileExists($decompressedParamFile, 'Dekomprimierte Test-Param-Variablen-Datei nicht gefunden: ' . $decompressedParamFile);

    // Dekomprimierte Werte ermitteln
    $decompressedParams = include($decompressedParamFile);
    
    // Request-Objekt erzeugen
    $request = new HttpRequestCompressed();
    
    // Raw Post Daten setzen
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_CMS_ENCODING'] = 'gz';
    $reflectionClass = new \ReflectionClass('Cms\Controller\Request\HttpCompressed');
    $reflectionPropertyDecompressedFlag = $reflectionClass->getProperty('postDataDecompressed');
    $reflectionPropertyDecompressedFlag->setAccessible(true);
    $reflectionPropertyDecompressedFlag->setValue($request, false);
    $reflectionPropertyRawBody = $reflectionClass->getProperty('_rawBody');
    $reflectionPropertyRawBody->setAccessible(true);
    $reflectionPropertyRawBody->setValue($request, file_get_contents($compressedRawBodyFile));
    
    // Dekomprimieren
    $request->decompressRequest();

    // Dekomprimierung pruefen
    $jsonParams = $request->getParam(\Cms\Request\Base::REQUEST_PARAMETER);
    $this->assertInternalType('string', $jsonParams);
    $params = json_decode($jsonParams, true);
    $this->assertInternalType('array', $params);
    $this->assertSame($decompressedParams, $params);
  }
}