<?php


namespace Cms\Business;

/**
 * @package      Cms\Business
 *
 * @method \Cms\Service\WebsiteSettings getService
 */

class WebsiteSettings extends Base\Service
{
  /**
   * @param string $websiteId
   *
   * @return \Cms\Data\WebsiteSettings[]
   */
  public function getAll($websiteId)
  {
    return $this->getService()->getAll($websiteId);
  }

  /**
   * @param string $websiteId
   * @param string $id
   * @param array  $attributes
   *
   * @return \Cms\Data\WebsiteSettings
   * @throws \Exception
   */
  public function update($websiteId, $id, array $attributes)
  {
    return $this->getService()->update($websiteId, $id, $attributes);
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
    // superuser hsa all rights
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightname) {
      case 'getAll':
        if ($this->isUserInAnyWebsiteGroup($identity, $check['websiteId'])) {
          return true;
        }
            break;
      case 'edit':
        if ($this->checkUserGroupRights($identity, $check['websiteId'], 'templates', 'all')) {
          return true;
        }
            break;
    }

    return false;
  }
}
