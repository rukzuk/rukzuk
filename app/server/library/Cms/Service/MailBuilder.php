<?php
namespace Cms\Service;

use Cms\Service\Base\Plain as PlainServiceBase;
use Cms\Exception as CmsException;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Locale as SbLocale;

/**
 * Mail Builder service
 *
 * @package      Cms
 * @subpackage   Service
 */

class MailBuilder extends PlainServiceBase
{
  /**
   * @var string
   */
  private $templateDirectory;

  /**
   * @var \Zend_View
   */
  private $view;

  public function __construct()
  {
    $this->templateDirectory = Registry::getConfig()->mail->template->directory;
    $this->view = new \Zend_View(
        array('scriptPath' => $this->templateDirectory)
    );
  }

  /**
   * @param  string       $methodName
   * @param  SbLocale     $locale
   * @return string
   */
  private function getTemplateFilename($methodName, SbLocale $locale)
  {
    $templateName = strtolower(str_replace(array('get', 'Mail'), '', $methodName)).'.tpl';
    $templateFile = $this->getTemplateFilenameForLocal($templateName, $locale);
    if (!empty($templateFile)) {
      return $templateFile;
    }
    $templateFile = $this->getTemplateFilenameForLocal($templateName, self::getDefaultLocale());
    if (!empty($templateFile)) {
      return $templateFile;
    }
    throw new CmsException('1', __METHOD__, __LINE__, array('local' => $locale->toString()));
  }

  /**
   * @param  string       $templateName
   * @param  SbLocale     $locale
   * @return string
   */
  private function getTemplateFilenameForLocal($templateName, SbLocale $locale)
  {
    $templateFile = FS::joinPath($locale->getLanguageCode(), $templateName);
    if (is_readable(FS::joinPath($this->templateDirectory, $templateFile))) {
      return $templateFile;
    }
    $templateFile = FS::joinPath($locale->getLanguage(), $templateName);
    if (is_readable(FS::joinPath($this->templateDirectory, $templateFile))) {
      return $templateFile;
    }
    return null;
  }

  /**
   * @param  \Orm\Entity\OptIn $optin
   * @param  string     $charset
   * @return \Cms\Mail
   */
  public function getOptinMail(\Orm\Entity\OptIn $optin, $charset = 'utf-8')
  {
    $config = Registry::getConfig();
    $baseUrl = Registry::getBaseUrl();
    $optinUser = $optin->getUser();
    $locale = self::getLocale($optinUser->getLanguage());

    $fromUser = $this->getMailFromUserData(array(
      'email' => $config->user->mail->optin->from->address,
      'name' => $config->user->mail->optin->from->name,
    ));

    $this->view->clearVars();
    $this->view->subject = null;
    $this->view->optin = $optin;
    $this->view->optinUser = $optinUser;
    $this->view->optinUrl = $baseUrl.$config->user->mail->optin->uri;
    $this->view->spaceUrl = $baseUrl;
    $this->view->fromName = $fromUser['name'];
    $this->view->fromEmail = $fromUser['email'];
    
    $optinMail = new \Cms\Mail($charset);
    $optinMail->setBodyText(
        $this->view->render($this->getTemplateFilename(__FUNCTION__, $locale))
    );
    // the subject is set in mail template
    $subject = $this->view->subject;
    
    $optinMail->setFrom($fromUser['email'], $fromUser['name']);
    $optinMail->setSubject($subject);
    $optinMail->addTo(
        $optinUser->getEmail(),
        $optinUser->getFirstname() . ' ' . $optinUser->getLastname()
    );

    return $optinMail;
  }

  /**
   * @param  \Orm\Entity\OptIn $optin
   * @param  string     $charset
   * @return \Cms\Mail
   */
  public function getRenewPasswordMail(\Orm\Entity\OptIn $optin, $charset = 'utf-8')
  {
    $config = Registry::getConfig();
    $baseUrl = Registry::getBaseUrl();
    $optinUser = $optin->getUser();
    $locale = self::getLocale($optinUser->getLanguage());

    $fromUser = $this->getMailFromUserData(array(
      'email' => $config->user->mail->renew->password->from->address,
      'name' => $config->user->mail->renew->password->from->name,
    ));

    $this->view->clearVars();
    $this->view->optin = $optin;
    $this->view->optinUser = $optinUser;
    $this->view->optinUrl = $baseUrl.$config->user->mail->renew->password->uri;
    $this->view->spaceUrl = $baseUrl;
    $this->view->fromName = $fromUser['name'];
    $this->view->fromEmail = $fromUser['email'];

    $renewMail = new \Cms\Mail($charset);
    $renewMail->setBodyText(
        $this->view->render($this->getTemplateFilename(__FUNCTION__, $locale))
    );
    // the subject is set in mail template
    $subject = $this->view->subject;


    $renewMail->setFrom($fromUser['email'], $fromUser['name']);
    $renewMail->setSubject($subject);
    $renewMail->addTo(
        $optinUser->getEmail(),
        $optinUser->getFirstname() . ' ' . $optinUser->getLastname()
    );

    return $renewMail;
  }

  /**
   * Erstellt ein Feedback-Mail Objekt mit entsprechenden Daten
   *
   * @param   \Cms\Feedback $feedback
   * @return  \Cms\Mail
   */
  public function getFeedbackMail(\Cms\Feedback $feedback, $charset = 'utf-8')
  {
    $locale = new SbLocale('de');
    
    $this->view->clearVars();
    $this->view->feedback = $feedback;

    $renewMail = new \Cms\Mail($charset);
        
    $renewMail->setBodyText(
        utf8_encode($this->view->render($this->getTemplateFilename(__FUNCTION__, $locale))),
        $charset
    );
    
    $renewMail->setFrom($feedback->getEmail());
    $renewMail->setSubject($feedback->getSubject());
        
    $renewMail->addTo(Registry::getConfig()->feedback->mail->adress);

    return $renewMail;
  }
  
  /**
   * Gibt die \Seitenbau\Locale zurueck
   * @return \Seitenbau\Locale
   */
  protected static function getLocale($locale = null)
  {
    if ($locale instanceof SbLocale) {
      return $locale;
    }

    if (SbLocale::isLocale($locale)) {
      return new SbLocale($locale);
    }
    
    $locale = Registry::getLocale();
    if (!is_null($locale)) {
      return $locale;
    }
    return self::getDefaultLocale();
  }
  
  /**
   * returns default locale
   * @return \Seitenbau\Locale
   */
  protected static function getDefaultLocale()
  {
    return new SbLocale(Registry::getConfig()->translation->default);
  }

  /**
   * @param array $fallbackUserData
   *
   * @return array
   */
  protected function getMailFromUserData(array $fallbackUserData = array())
  {
    try {
      $owner = $this->getUserService()->getOwner();
    } catch (\Exception $doNothing) {
      return $fallbackUserData;
    }

    if (!is_object($owner) || !($ownerEmail = $owner->getEmail()) || empty($ownerEmail)) {
      return $fallbackUserData;
    }

    return array(
      'email' => $ownerEmail,
      'name' => $owner->getFirstname() . ' ' . $owner->getLastname(),
    );
  }

  /**
   * @return \Cms\Service\User
   */
  protected function getUserService()
  {
    return $this->getService('User');
  }
}
