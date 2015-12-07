<?php
namespace Cms\Business\Website;

use Cms\Business\Website as WebsiteBusiness,
    Cms\Response,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Business\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class UpdateTest extends ServiceTestCase
{
  protected $business;

  protected $testEntry;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new WebsiteBusiness('Website');

    $attributes = array(
      'name' => 'PHPUnit Test Website - Update',
      'description' => 'website description',
      'navigation' => '[]',
      'colorscheme' => '[{"id":"860ac1af-c52f-4bbc-965e-cd35533d30fe","value":"rgba(59,78,255,1)","name":"Farbe 2"}]',
      'publish' => '{"host":"www.example.de","username":"user","password":"geheim","basedir":"","mode":"ftp"}'
    );
    $this->testEntry = $this->business->create($attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $updateId = $this->testEntry->getId();
    
    $attributes = array(
      'name' => 'PHPUnit Test Website - Update Neuer Name',
      'description' => 'other description',
      'navigation' => '[{}]',
      'colorscheme' => '[]',
      'publish' => '{"host":"www.example.com","username":"name","password":"abcdefg","basedir":"/","mode":"sftp"}'
    );
    $result = $this->business->update($updateId, $attributes);
    
    $this->assertResultSuccess($result, $attributes);
  }

  /**
   * @test
   * @group library
   */
  public function passwortNotSaveWhenMarked()
  {
    $updateId = $this->testEntry->getId();
    
    $publishData = $this->testEntry->getPublish();
    $requestPublishData = str_replace('"password":"geheim"', '"password":"*****"', $publishData);
        
    $attributes = array(
      'name' => 'PHPUnit Test Website - Update Neuer Name',
      'description' => 'other description',
      'navigation' => '[{}]',
      'colorscheme' => '[]',
      'publish' => $requestPublishData
    );
    $result = $this->business->update($updateId, $attributes);
    
    // Passwort darf nicht ueberschrieben werden, wenn es markiert ist
    $attributes['publish'] = $publishData;    
    $this->assertResultSuccess($result, $attributes);
  }
  
  /**
   * @test
   * @group library
   */
  public function passwortNewWhenNew()
  {
    $updateId = $this->testEntry->getId();
    
    $publishData = $this->testEntry->getPublish();
    $requestPublishData = str_replace('"password":"geheim"', '"password":"abcdefg"', $publishData);
        
    $attributes = array(
      'name' => 'PHPUnit Test Website - Update Neuer Name',
      'description' => 'other description',
      'navigation' => '[{}]',
      'colorscheme' => '[]',
      'publish' => $requestPublishData
    );
    $result = $this->business->update($updateId, $attributes);
    
    $this->assertResultSuccess($result, $attributes);
  }
  
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function idNotFound()
  {
    $attributes = array(
      'name' => 'PHPUnit Test Website - Update Neuer Name',
      'description' => 'other description',
      'navigation' => '[{}]'
    );

    $this->business->update('ID-EXISTIERT-NICHT', $attributes);
  }

  protected function assertResultFalse($result, $expectedData = '')
  {
    $this->assertNull($result);
  }

  protected function assertResultSuccess($result, $expectedData = '')
  {
    $this->assertInstanceOf('Cms\Data\Website', $result);
    $this->assertNotNull($result->getId());
    $this->assertSame($expectedData['name'], $result->getName());
    $this->assertSame($expectedData['description'], $result->getDescription());
    $this->assertSame($expectedData['navigation'], $result->getNavigation());
    $this->assertSame($expectedData['colorscheme'], $result->getColorscheme());
    $this->assertSame($expectedData['publish'], $result->getPublish());
  }
}