<?php


namespace Cms\Business;

/**
 * Business Class Package
 *
 * @package Cms\Business
 *
 * @method \Cms\Service\Package getService
 */
class Package extends Base\Service
{
  /**
   * returns all packages of the given website
   *
   * @param   string $websiteId
   *
   * @return  \Cms\Data\Package[]
   */
  public function getAll($websiteId)
  {
    return $this->getService()->getAll($websiteId);
  }

  /**
   * @param array  $identity
   * @param string $rightname
   * @param mixed  $check
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // default: no rights
    return false;
  }
}
