<?php
namespace Cms\Dao;

/*
 * @package      Cms
 * @subpackage   Dao
 */
interface UserStatus
{
  /**
   * Owner of the space
   * @return \DateTime
   */
  public function getLastLogin();

  /**
   * @param string    $userId
   * @param string    $authBackend
   * @param \DateTime $lastLogin
   *
   * @return \Cms\Data\UserStatus
   */
  public function setLastLogin($userId, $authBackend, \DateTime $lastLogin);
}
