<?php
namespace Application\Controller\Media;

use Cms\Validator\UniqueId as UniqueIdValidator,
    Orm\Data\Media as DataMedia,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Seitenbau\Registry as Registry,
    Seitenbau\FileSystem as FS;

/**
 * UploadTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class UploadTest extends ControllerTestCase
{

  protected function tearDown()
  {
    $this->clearFakeUpload();

    $uploadDirNames = array(
      'SITE-ra10e8ul-11af-46cd-a651-fc42dc78feue-SITE',
      'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE'
    );

    foreach ($uploadDirNames as $dirname)
    {
      $uploadDir = \Seitenbau\Registry::getConfig()->media->files->directory .
                    DIRECTORY_SEPARATOR .
                    $dirname . DIRECTORY_SEPARATOR;
      $this->removeDir($uploadDir);
    }

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnMissingWebsiteId()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testUploadFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'backbone.js';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9t1';

    $name = 'some_name';
    $fileInputname = 'upload';
    $requestUri = sprintf(
      '/media/upload/params/{"fileinputname":"%s","name":"%s"}',
      $fileInputname,
      $name
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }

    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('websiteid', $errorFields);
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnTooLongName()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testUploadFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'backbone.js';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9t2';

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $tooLongName = str_repeat('integration_test_upload', 12);
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","name":"%s", "fileinputname":"%s"}',
      $websiteId,
      $tooLongName,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }

    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('name', $errorFields);
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnInvalidAlbumId()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testUploadFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'backbone.js';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9t2';

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $name = 'integration_test_upload';
    $fileInputname = 'upload';
    $invalidAlbumId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","albumId":"%s","name":"%s", "fileinputname":"%s"}',
      $websiteId,
      $invalidAlbumId,
      $name,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }

    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('albumid', $errorFields);
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnInvalidMediaId()
  {
    $testUploadFile = FS::joinPath(Registry::getConfig()->test->files->directory, 'backbone.js');

    $requestParrams = array(
      'websiteid'     => 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE',
      'id'            => 'MDB-.-.-.-.-.-MDB',
      'albumId'       => 'ALBUM-ce2ecf03-acc4-4adb-ed34-72ebb08780uo-ALBUM',
      'name'          => 'integration_test_upload',
      'fileinputname' => 'upload'
    );

    $this->assertFakeUpload($requestParrams['fileinputname'], $testUploadFile);
    $this->dispatchWithParams('/media/upload', $requestParrams);
    
    $response = $this->getValidatedErrorResponse();
    $errorData = $response->getError();

    $this->assertInternalType('array', $errorData);
    $this->assertNotEmpty($errorData);
    $errorFields = array();
    foreach ($errorData as $error) {
      $errorFields[] = $error->param->field;
    }
    $this->assertContains('id', $errorFields);
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnNoName()
  {
    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $noName = '';
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $noName,
      $fileInputname
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnNoFileinputname()
  {
    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $mediaName = 'some_name';
    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $mediaName
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnTooLongFileinputname()
  {
    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $mediaName = 'some_name';
    $fileInputname = str_repeat('upload', 9);
    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $mediaName,
      $fileInputname
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnTooShortName()
  {
    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $tooShortName = 'i';
    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $tooShortName
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-423
   */
  public function uploadShouldFailOnNonExistingWebsite()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;

    $testUploadFile = $testMediaFilesDirectory . DIRECTORY_SEPARATOR . 'bridge.JPG';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9bu';

    $this->assertFileExists($testUploadFile);

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fnoa-SITE';
    $mediaName = 'uppercased_extension_test_upload';
    $albumId = 'ALBUM-ce2ecf03-acc4-4adb-ed34-72ebb08780uo-ALBUM';
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","albumid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $albumId,
      $mediaName,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }
  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-423
   */
  public function uploadShouldFailOnNonExistingAlbum()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;

    $testUploadFile = $testMediaFilesDirectory . DIRECTORY_SEPARATOR . 'bridge.JPG';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9bu';

    $this->assertFileExists($testUploadFile);

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fnoa-SITE';
    $mediaName = 'uppercased_extension_test_upload';
    $albumId = 'ALBUM-ce2ecf03-acc4-4adb-ed34-72ebb08780uo-ALBUM';
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","albumid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $albumId,
      $mediaName,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldFailOnNonExistingMedia()
  {
    $testUploadFile = FS::joinPath(Registry::getConfig()->test->files->directory, 'backbone.js');

    $requestParrams = array(
      'websiteid'     => 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE',
      'id'            => 'MDB-not0exis-ting-0000-0000-media0item00-MDB',
      'name'          => 'integration_test_upload',
      'fileinputname' => 'upload'
    );

    $this->assertFakeUpload($requestParrams['fileinputname'], $testUploadFile);
    $this->dispatchWithParams('/media/upload', $requestParrams);
    
    $response = $this->getValidatedErrorResponse();
    $errorData = $response->getError();

    $this->assertInternalType('array', $errorData);
    $this->assertNotEmpty($errorData);
    $this->assertEquals('261', $errorData[0]->code);
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-402
   */
  public function uploadOfMediaWithUpcassedFileExtensionShouldGetResized()
  {
    $expectedUploadCount = $expectedCachedFileCount = 1;
    $expectedUploadExtension = 'JPG';
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testMediaCacheDirectory = $config->media->cache->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testUploadFile = $testMediaFilesDirectory . DIRECTORY_SEPARATOR . 'bridge.JPG';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9bu';

    $this->assertFileExists($testUploadFile);

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78feue-SITE';
    $mediaName = 'uppercased_extension_test_upload';
    $albumId = 'ALBUM-ce2ecf03-acc4-4adb-ed34-72ebb08780ue-ALBUM';
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","albumid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $albumId,
      $mediaName,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertHeaderContains('Content-Type', 'text/plain');
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $expectedMediaWebsiteDirectory = $testMediaFilesDirectory . DIRECTORY_SEPARATOR
      . $websiteId;
    $this->assertTrue(is_dir($expectedMediaWebsiteDirectory));

    $directory = new \DirectoryIterator($expectedMediaWebsiteDirectory);
    $uploadedFiles = array();
    foreach ($directory as $fileinfo)
    {
      if (!$fileinfo->isDot())
      {
        $splitFileName = explode(".", $fileinfo->getFilename());
        $this->assertSame($expectedUploadExtension, end($splitFileName));
        $uploadedFiles[] = $fileinfo->getFilename();
      }
    }
    $this->assertSame($expectedUploadCount, count($uploadedFiles));

    $getAllRequest = '/media/getall/params/{"websiteid":"%s","albumid":"%s",'
        .  '"maxIconWidth":"100","maxIconHeight":"100"}';
    $getAllRequest = sprintf($getAllRequest, $websiteId, $albumId);

    $this->dispatch($getAllRequest);

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertTrue(count($responseData->media) > 0);
    $this->assertInstanceOf('stdClass', $responseData->media[0]);
    $this->assertObjectHasAttribute('icon', $responseData->media[0]);
    $webhostFromConfig = $config->server->url;
    $iconUrlOfUploadedImage = str_replace(
      $webhostFromConfig, '', urldecode($responseData->media[0]->icon)
    );

    $this->dispatch($iconUrlOfUploadedImage);

    $testMediaWebsiteCacheDirectory = $testMediaCacheDirectory
      . DIRECTORY_SEPARATOR . $websiteId;
    $expectedMediaWebsiteCacheDirectory = $testMediaWebsiteCacheDirectory;

    $this->assertTrue(is_dir($expectedMediaWebsiteCacheDirectory));

    $directory = new \DirectoryIterator($expectedMediaWebsiteCacheDirectory);
    $cachedFiles = array();
    foreach ($directory as $fileinfo)
    {
      if (!$fileinfo->isDot())
      {
        $splitFileName = explode(".", $fileinfo->getFilename());
        $actualUploadExtension = end($splitFileName);
        if ($actualUploadExtension != 'secure')
        {
          $this->assertSame(strtolower($expectedUploadExtension), $actualUploadExtension);
          $cachedFiles[] = $fileinfo->getFilename();
        }
      }
    }
    $this->assertSame($expectedCachedFileCount, count($uploadedFiles));

    $testMediaWebsiteUploadDirectory = $testMediaFilesDirectory
      . DIRECTORY_SEPARATOR . $websiteId;

    DirectoryHelper::removeRecursiv($testMediaWebsiteUploadDirectory, $testMediaFilesDirectory);
    DirectoryHelper::removeRecursiv($testMediaWebsiteCacheDirectory, $testMediaCacheDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function uploadShouldStoreFileAsExpectedAndHaveTextPlainContentType()
  {
    $expectedUploadCount = 1;
    $expectedUploadExtension = 'js';
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testUploadFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'backbone.js';
    $testTmpFile = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'phpIsd9te';

    $websiteId = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $mediaName = 'integration_test_upload';
    $albumId = 'ALBUM-ce2ecf03-acc4-4adb-ed34-72ebb08780up-ALBUM';
    $fileInputname = 'upload';

    $requestUri = sprintf(
      '/media/upload/params/{"websiteid":"%s","albumid":"%s","name":"%s","fileinputname":"%s"}',
      $websiteId,
      $albumId,
      $mediaName,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testUploadFile, $testTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertHeaderContains('Content-Type', 'text/plain');
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $uniqueidValidator = new UniqueIdValidator(
      DataMedia::ID_PREFIX,
      DataMedia::ID_SUFFIX
    );
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $mediaId = $responseData->id;
    $this->assertTrue($uniqueidValidator->isValid($mediaId));

    $expectedMediaWebsiteDirectory = $testMediaFilesDirectory . DIRECTORY_SEPARATOR
      . $websiteId;
    $this->assertTrue(is_dir($expectedMediaWebsiteDirectory));

    $directory = new \DirectoryIterator($expectedMediaWebsiteDirectory);
    $uploadedFiles = array();
    foreach ($directory as $fileinfo)
    {
      if (!$fileinfo->isDot()) {
        $splitFileName = explode(".", $fileinfo->getFilename());
        $this->assertSame($expectedUploadExtension, end($splitFileName));
        $uploadedFiles[] = $fileinfo->getFilename();
      }
    }
    $this->assertSame($expectedUploadCount, count($uploadedFiles));
    $testMediaWebsiteUploadDirectory = $testMediaFilesDirectory . DIRECTORY_SEPARATOR .
      $websiteId;

    $this->dispatch('/media/getbyid/params/{"websiteid":"' . $websiteId . '","id":"' . $mediaId . '"}');
    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('dateUploaded', $responseData);
    $this->assertNotNull(
      $responseData->dateUploaded, 'Upload-Datum muss automatisch gesetzt werden'
    );
    $this->assertInternalType(
      'int', $responseData->dateUploaded,
      'Upload-Datum muss Timestamp sein'
    );

    DirectoryHelper::removeRecursiv($testMediaWebsiteUploadDirectory, $testMediaFilesDirectory);
  }
  
  /**
   * @test
   * @group integration
   */
  public function uploadShouldOverwriteExistingFileAsExpected()
  {
    $config = Registry::getConfig();
    $testFilesDirectory = $config->test->files->directory;
    $testMediaFilesDirectory = $config->media->files->directory;
    
    $websiteId      = 'SITE-ra10e8ul-11af-46cd-a651-fc42dc78fe50-SITE';
    $albumId        = 'ALBUM-existing-0000-0000-0000-album0000001-ALBUM';
    $uploadFileName = 'test_1.jpg';
    $uploadFile     = FS::joinPath($testMediaFilesDirectory, $uploadFileName);
    
    $newMediaId = $this->assertCreateNewMediaItem($websiteId, $albumId, FS::joinPath($testFilesDirectory, 'backbone.js'));
    
    $requestParrams = array(
      'websiteid'     => $websiteId,
      'id'            => $newMediaId,
      'name'          => $uploadFileName,
      'fileinputname' => 'upload_overwrite'
    );
    $this->assertFakeUpload($requestParrams['fileinputname'], $uploadFile);
    $this->dispatchWithParams('/media/upload', $requestParrams);
    $this->getValidatedSuccessResponse();
    
    $this->dispatchWithParams('/media/getbyid', array('websiteid' => $websiteId, 'id' => $newMediaId));
    $media = $this->getValidatedSuccessResponse()->getData();
    $this->assertEquals($newMediaId, $media->id);
    $this->assertEquals($requestParrams['name'], $media->name);
    $this->assertEquals(filesize($uploadFile), $media->filesize);
        
    DirectoryHelper::removeRecursiv(
      FS::joinPath($testMediaFilesDirectory, $websiteId),
      $testMediaFilesDirectory
    );
  }

  /**
   * Loescht ein Verzeichnis samt Inhalt (Dateien und Unterordner)
   *
   * @param string $websiteDir
   */
  private function removeDir($dir)
  {
    if (is_dir($dir))
    {
      $dirHandle = opendir($dir);
      while(($file = \readdir($dirHandle)) !== false)
      {
        if ($file == '.' || $file == '..') continue;

        $handle = $dir . DIRECTORY_SEPARATOR . $file;

        $filetype = filetype($handle);

        if ($filetype == 'dir')
        {
          $this->removeDir($handle);
        }
        else
        {
          unlink($handle);
        }
      }
      closedir($dirHandle);
      rmdir($dir);
    }
  }
  
  private function assertCreateNewMediaItem($websiteId, $albumId, $uploadFile)
  {
    $requestParrams = array(
      'websiteid'     => $websiteId,
      'albumId'       => $albumId,
      'name'          => 'UploadTest_'.time(),
      'fileinputname' => 'upload_'.time(),
    );
    $this->assertFakeUpload($requestParrams['fileinputname'], $uploadFile);
    $this->dispatchWithParams('/media/upload', $requestParrams);
    $newMediaId = $this->getValidatedSuccessResponse()->getData()->id;

    $this->dispatchWithParams('/media/getbyid', array('websiteid' => $websiteId, 'id' => $newMediaId));
    $media = $this->getValidatedSuccessResponse()->getData();
    $this->assertEquals($newMediaId, $media->id);
    $this->assertEquals($requestParrams['name'], $media->name);
    $this->assertEquals(filesize($uploadFile), $media->filesize);
    
    return $media->id;
  }
}