<?php
namespace Cms\Business;

/**
 * Class UserStatus
 *
 * @package Cms\Business
 *
 * @method \Cms\Service\UserStatus getService
 */
class UserStatus extends Base\Service
{
  /**
   * @return \DateTime
   */
  public function getLastLogin()
  {
    return $this->getService()->getLastLogin();
  }

  /**
   * @param string    $userId
   * @param string    $authBackend
   * @param \DateTime $lastLogin
   */
  public function setLastLogin($userId, $authBackend, \DateTime $lastLogin)
  {
    return $this->getService()->setLastLogin($userId, $authBackend, $lastLogin);
  }
}
