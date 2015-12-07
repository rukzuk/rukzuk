<?php


namespace Cms\Business;

use Cms\Data\PageType as DataPageType;

/**
 * @package Cms\Business
 *
 * @method \Cms\Service\PageType getService
 */

class PageType extends Base\Service
{
  /**
   * returns the page type of the given website id and page type id
   *
   * @param   string $websiteId
   * @param   string $id
   *
   * @return DataPageType
   */
  public function getById($websiteId, $id)
  {
    return $this->getService()->getById($websiteId, $id);
  }

  /**
   * @param array  $identity
   * @param string $rightname
   * @param mixed  $check
   *
   * @return bool
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // default: no rights
    return false;
  }
}
