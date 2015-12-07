<?php


namespace Test\Seitenbau\Cms\Dao\Website;

use Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;
    
class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyWebsiteMock';
  
  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }

  /**
   * @param string $id
   * @param array  $attributes
   */
  public function update($id, $attributes)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param array   $attributes
   * @param boolean $useAttributesId Defaults to false
   */
  public function create($attributes, $useAttributesId = false)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $id
   */
  public function deleteById($id)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param string $id
   * @param array  $attributes
   */
  public function copy($id, array $attributes)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param  string $id
   *
   * @return integer The increased version number
   */
  public function increaseVersion($id)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  /**
   * @param  string $id
   *
   * @return integer The decreased version number
   */
  public function decreaseVersion($id)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }
}