<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;

/**
 * User
 *
 * @package      Cms
 * @subpackage   Service
 *
 * @method       \Cms\Dao\UserStatus getDao
 */
class UserStatus extends DaoServiceBase
{
  public function getLastLogin()
  {
    return $this->getDao()->getLastLogin();
  }

  /**
   * @param string    $userId
   * @param string    $authBackend
   * @param \DateTime $lastLogin
   */
  public function setLastLogin($userId, $authBackend, \DateTime $lastLogin)
  {
    return $this->getDao()->setLastLogin($userId, $authBackend, $lastLogin);
  }
}
