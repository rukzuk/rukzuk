<?php


namespace Test\Seitenbau\Cms\Dao\Page;

use Test\Seitenbau\Cms\Dao\Page\WriteableMock,
    Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;

class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyPageMock';
  
  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param string $newname
   */
  public function copy($id, $websiteId, $newname = null)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $websiteId
   * @param string $newWebsiteId
   */
  public function copyPagesToNewWebsite($websiteId, $newWebsiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $id
   * @param string $websiteId
   */
  public function delete($id, $websiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $websiteId
   * @param array  $ids
   */
  public function deleteByIds($websiteId, array $ids)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $attributes
   */
  public function update($id, $websiteId, $attributes)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string  $websiteId
   * @param array   $attributes
   * @param boolean $useColumnsValuesId Defaults to false
   */
  public function create($websiteId, $attributes, $useColumnsValuesId = false)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }
}