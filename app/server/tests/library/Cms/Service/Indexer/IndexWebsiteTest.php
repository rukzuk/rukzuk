<?php
namespace Cms\Service\Indexer;

use Cms\Service\Indexer as IndexerService,
    Cms\Response,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\BuilderServiceTestCase,
    Test\Seitenbau\System\Helper as SystemHelper;
/**
 * IndexWebsiteTest
 *
 * @package      Cms
 * @subpackage   Service\Indexer
 */
class IndexWebsiteTest extends BuilderServiceTestCase
{
  protected $service;
  
  protected function setUp()
  {
    parent::setUp();

    $this->service = new IndexerService;
    $this->removeWebsiteIndexes();
  }
  /**
   * @test
   * @group library
   * @expectedException Exception
   */
  public function indexWebsiteShouldThrowExceptionOnNonExistingWebsite()
  {
    $nonExistingWebsiteId = 'SITE-no000000-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $this->service->indexWebsite($nonExistingWebsiteId);
  } 
  /**
   * @test
   * @group library
   */
  public function getIndexFileShouldReturnExpectedIndexFile()
  {
    $websiteId = 'SITE-20b2394c-b41c-490f-acec-70bb15968c52-SITE';
    $getIndexFileForWebsiteMethod = new \ReflectionMethod(
      'Cms\Service\Indexer', 'getIndexFileForWebsite'
    );
    
    $getIndexFileForWebsiteMethod->setAccessible(true);
    $expectedIndexFile = Registry::getConfig()->get('indexing')->basedir 
      . DIRECTORY_SEPARATOR . $websiteId;
    
    $this->assertSame(
      $expectedIndexFile,
      $getIndexFileForWebsiteMethod->invoke(new IndexerService, $websiteId) 
    ); 
  }
  /**
   * @test
   * @group library
   */
  public function indexWebsiteShouldNotCreateAnIndexWhenWebsiteHasNoPages()
  {
    $pagelessWebsiteId = 'SITE-nopages9-7fc5-464a-bd47-06b3b8af05dg-SITE';
    $this->service->indexWebsite($pagelessWebsiteId); 
    
    $nonExistingIndexFile = Registry::getConfig()->get('indexing')->basedir 
      . DIRECTORY_SEPARATOR . $pagelessWebsiteId;
    
    $this->assertFileNotExists($nonExistingIndexFile);
  }
  /**
   * @test
   * @group library
   */
  public function indexWebsiteShouldCreateIndex()
  {
    $websiteId = 'SITE-20b2394c-b41c-490f-acec-70bb15968c52-SITE';
    $this->service->indexWebsite($websiteId); 
    
    $indexDirectory = Registry::getConfig()->get('indexing')->basedir;
    $createdIndexDirectory = $indexDirectory
      . DIRECTORY_SEPARATOR . $websiteId;
    
    $this->assertFileExists($createdIndexDirectory);
    $this->assertTrue(is_dir($createdIndexDirectory));
    
    $this->assertGreaterThan(1, $this->getFilesCountOfWebsiteIndex($websiteId));
    
    DirectoryHelper::removeRecursiv($createdIndexDirectory, $indexDirectory);
  }
  /**
   * @test
   * @group library
   * @dataProvider indexingEnabledConfigurationValuesProvider
   */
  public function isIndexingEnabledShouldReturnBooleanValue($value)
  {
    $formerIndexingEnabled = $this->changeConfiguredIndexingEnabled(true);
    
    $indexingEnabled = $this->service->isIndexingEnabled();
    $this->assertInternalType('boolean', $indexingEnabled);
    
    $this->changeConfiguredIndexingEnabled($formerIndexingEnabled);
  }
  /**
   * @test
   * @group library
   * @dataProvider trueIndexingEnabledConfigurationValuesProvider
   */
  public function isIndexingEnabledShouldReturnBooleanWithValueTrue($value)
  {
    $formerIndexingEnabled = $this->changeConfiguredIndexingEnabled($value);
    
    $indexingEnabled = $this->service->isIndexingEnabled();
    $this->assertInternalType('boolean', $indexingEnabled);
    $this->assertSame(true, $indexingEnabled);
    
    $this->changeConfiguredIndexingEnabled($formerIndexingEnabled);
  }
  /**
   * @test
   * @group library
   * @dataProvider falseIndexingEnabledConfigurationValuesProvider
   */
  public function isIndexingEnabledShouldReturnBooleanWithValueFalse($value)
  {
    $formerIndexingEnabled = $this->changeConfiguredIndexingEnabled($value);
    
    $indexingEnabled = $this->service->isIndexingEnabled();
    $this->assertInternalType('boolean', $indexingEnabled);
    $this->assertSame(false, $indexingEnabled);
    
    $this->changeConfiguredIndexingEnabled($formerIndexingEnabled);
  }
  /**
   * @test
   * @group library
   */
  public function isLuceneIndexerShouldReturnBooleanWithValueTrue()
  {
    $formerIndexer = $this->changeConfiguredIndexer('Lucene');
    $isLuceneIndexer = $this->service->isLuceneIndexer();
    $this->assertInternalType('boolean', $isLuceneIndexer);
    $this->assertSame(true, $isLuceneIndexer);
    $this->changeConfiguredIndexer($formerIndexer);
  }
  /**
   * @test
   * @group library
   */
  public function isLuceneIndexerShouldReturnBooleanWithValueFalse()
  {
    $formerIndexer = $this->changeConfiguredIndexer('Elasticsearch');
    $isLuceneIndexer = $this->service->isLuceneIndexer();
    $this->assertInternalType('boolean', $isLuceneIndexer);
    $this->assertSame(false, $isLuceneIndexer);
    $this->changeConfiguredIndexer($formerIndexer);
  }
  /**
   * @return array
   */
  public function indexingEnabledConfigurationValuesProvider()
  {
    return array(
      array(true),    
      array(false),
      array(0),  
      array(1),  
      array("something")  
    );
  }
  /**
   * @return array
   */
  public function trueIndexingEnabledConfigurationValuesProvider()
  {
    return array(
      array(true),    
      array("true"),
      array("1"),  
      array(1)
    );
  }
  /**
   * @return array
   */
  public function falseIndexingEnabledConfigurationValuesProvider()
  {
    return array(
      array(false),    
      array("false"),
      array("0"),  
      array(0),
      array("sometext")
    );
  }
  
  /**
   * @param  string $websiteId
   * @return integer
   */
  private function getFilesCountOfWebsiteIndex($websiteId)
  {
    $config = Registry::getConfig();
    $indexDirectory = $config->indexing->basedir;
    $websiteIndexDirectory = $indexDirectory 
      . DIRECTORY_SEPARATOR . $websiteId;
    $fileCountCommand = sprintf("ls -1 %s | wc -l", $websiteIndexDirectory);
    list($error, $output, $status) = SystemHelper::user_proc_exec($fileCountCommand);
    return (int) $output[0];
  }
  
  /**
   * @param  string $directory
   * @return string 
   */
  private function changeConfiguredCreatorDirectory($directory)
  {
    $config = Registry::getConfig();
    $formerCreatorDirectory = $config->creator->directory;
    
    $changedCreatorDirectory= array(
      'creator' => array(
        'directory' => $directory
      )    
    );
    $changedCreatorDirectoryConfig = new \Zend_Config(
      $changedCreatorDirectory
    );
    $config->merge($changedCreatorDirectoryConfig);
    $this->assertEquals(
      $directory, $config->creator->directory
    );
    
    return $formerCreatorDirectory;
  }  
}