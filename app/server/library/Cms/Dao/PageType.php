<?php


namespace Cms\Dao;

use Cms\Dao\PageType\Source as PageTypeSource;
use Cms\Data\PageType as DataPageType;

interface PageType
{
  /**
   * returns all page types of the given source
   *
   * @param PageTypeSource $source
   *
   * @return DataPageType[]
   */
  public function getAll(PageTypeSource $source);

  /**
   * returns the page type of the given source and id
   *
   * @param PageTypeSource $source
   *
   * @return DataPageType
   */
  public function getById(PageTypeSource $source, $id);
}
