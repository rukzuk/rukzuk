<?php
namespace Cms\Service;

use Cms\Exception as CmsException;
use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Service\Optin\InvalidModeException;
use Cms\Service\Optin\ExpiredCodeException;
use Seitenbau\Registry as Registry;

/**
 * @package      Cms
 * @subpackage   Service
 *
 * @method \Cms\Dao\Optin getDao
 */
class Optin extends DaoServiceBase
{
  /**
   * returns the user id of the optin given by code
   *
   * @param string $code
   *
   * @return string
   */
  public function getUserIdByCode($code)
  {
    $optin = $this->getDao()->getByCode($code);
    return $optin->getUserid();
  }

  /**
   * deletes the optin given by code
   *
   * @param $code
   */
  public function deleteByCode($code)
  {
    $this->getDao()->deleteByCode($code);
  }

  /**
   * @param  string $code
   *
   * @return boolean
   * @throws \Cms\Exception
   */
  public function validateCode($code)
  {
    $optin = $this->getDao()->getByCode($code);
    $this->isValidMode($optin);
    $this->isValidTimebox($optin);
    return true;
  }

  /**
   * @param  \Orm\Entity\OptIn $optin
   *
   * @throws \Cms\Service\Optin\InvalidModeException
   */
  private function isValidMode(\Orm\Entity\OptIn $optin)
  {
    $mode = $optin->getMode();
    if (!in_array($mode, array(\Orm\Entity\OptIn::MODE_REGISTER, \Orm\Entity\OptIn::MODE_PASSWORD))) {
      throw new InvalidModeException(1038, __METHOD__, __LINE__, array('mode' => $mode));
    }
  }

  /**
   * @param  \Orm\Entity\OptIn $optin
   *
   * @throws Optin\ExpiredCodeException
   * @throws Optin\InvalidModeException
   * @return boolean
   */
  private function isValidTimebox(\Orm\Entity\OptIn $optin)
  {
    $this->isValidMode($optin);

    $configuredLifetimeInDays = 0;
    $mode = $optin->getMode();
    if ($mode === \Orm\Entity\OptIn::MODE_REGISTER) {
      $configuredLifetimeInDays = (int)Registry::getConfig()->optin->lifetime->register;
    }
    if ($mode === \Orm\Entity\OptIn::MODE_PASSWORD) {
      $configuredLifetimeInDays = (int)Registry::getConfig()->optin->lifetime->password;
    }
    if ($configuredLifetimeInDays === 0) {
      return true;
    }

    $today = new \DateTime();
    $intervalSpec = sprintf('P%dD', $configuredLifetimeInDays);
    $lifetimeBoundary = $today->sub(new \DateInterval($intervalSpec));
    if ($lifetimeBoundary->getTimestamp() > $optin->getTimestamp()->getTimestamp()) {
      throw new ExpiredCodeException(1037, __METHOD__, __LINE__, array('code' => $optin->getCode()));
    }
    return true;
  }

  /**
   * @param \Cms\Data\User[] $users
   *
   * @return array
   */
  public function createAndStoreOptinCodes(array $users)
  {
    $userIds = array();
    foreach ($users as $user) {
      $userIds[] = $user->getId();
    }
    $this->execute(
        'deleteByUserIdsAndMode',
        array($userIds, \Orm\Entity\OptIn::MODE_REGISTER)
    );
    return $this->getDao()->create($users, \Orm\Entity\OptIn::MODE_REGISTER);
  }

  /**
   * @param \Cms\Data\User $user
   *
   * @return array
   */
  public function createAndStorePasswordCode(\Cms\Data\User $user)
  {
    $userIds = array($user->getId());
    $this->execute(
        'deleteByUserIdsAndMode',
        array($userIds, \Orm\Entity\OptIn::MODE_PASSWORD)
    );
    return $this->getDao()->create(array($user), \Orm\Entity\OptIn::MODE_PASSWORD);
  }

  /**
   * @param array $optins
   */
  public function sendOptinMails(array $optins)
  {
    $mailBuilderService = $this->getService('MailBuilder');

    foreach ($optins as $optin) {
      $optinMail = $mailBuilderService->getOptinMail($optin);
      $optinMail->send();
    }
  }

  /**
   * @param array $optins
   */
  public function sendRenewPasswordMails(array $optins)
  {
    $mailBuilderService = $this->getService('MailBuilder');

    foreach ($optins as $optin) {
      $renewPasswordMail = $mailBuilderService->getRenewPasswordMail($optin);
      $renewPasswordMail->send();
    }
  }

  /**
   * @param  string $id
   *
   * @return boolean
   */
  public function deleteByUserId($id)
  {
    return $this->execute('deleteByUserId', array($id));
  }

  /**
   * @return boolean
   */
  public function deleteAll()
  {
    return $this->execute('deleteAll', array());
  }
}
