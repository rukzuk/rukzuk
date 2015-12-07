<?php
namespace Cms;

use \Zend_Mail;
use \Seitenbau\Registry;

/**
 * Feedback
 *
 * @package      Cms
 */

class Feedback
{
  protected $adapter;

  protected $email;

  protected $subject;

  protected $userFeedback;

  protected $userAgent;

  protected $platform;

  protected $clientErrors;

  protected $webhost;

  protected $defaultAdapter = 'mail';

  public function __construct(\Zend_Config $config)
  {
    $adapter = $this->initConfigAdapter($config);
    $this->setAdapter($adapter);

    $this->setWebhost(Registry::getBaseUrl());
  }

  /**
   * @return \Cms\Feedback\Adapter\Base
   */
  public function getAdapter()
  {
    return $this->adapter;
  }

  public function setAdapter(\Cms\Feedback\Adapter\Base $adapter)
  {
    $this->adapter = $adapter;
  }

  /**
   * Versendet das Feedback
   */
  public function send()
  {
    $this->getAdapter()->send($this);
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }

  public function getSubject()
  {
    return $this->subject;
  }

  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  public function getUserFeedback()
  {
    return $this->userFeedback;
  }

  public function setUserFeedback($userFeedback)
  {
    $this->userFeedback = $userFeedback;
  }

  public function getUserAgent()
  {
    return $this->userAgent;
  }

  public function setUserAgent($userAgent)
  {
    $this->userAgent = $userAgent;
  }

  public function getPlatform()
  {
    return $this->platform;
  }

  public function setPlatform($platform)
  {
    $this->platform = $platform;
  }

  public function getClientErrors()
  {
    return $this->clientErrors;
  }

  public function setClientErrors($clientErrors)
  {
    $this->clientErrors = $clientErrors;
  }

  public function getWebhost()
  {
    return $this->webhost;
  }

  public function setWebhost($webhost)
  {
    $this->webhost = $webhost;
  }

  /**
   * Initialisiert einen Feedback Adapter anhand der uebergebenen Config
   *
   * Ist in der Config kein Adapter hinterlegt, so wird der Default-Adapter
   * initialisiert
   *
   * @param     \Zend_Config  $config
   * @return    \Cms\Feedback\Adapter\Base
   * @throws    \Cms\Feedback\Exception
   */
  private function initConfigAdapter(\Zend_Config $config)
  {
    // Klasse des Adapters festlegen
    $adapterClass = (isset($config->adapter))
                  ? $config->adapter : $this->defaultAdapter;
    $adapterName = '\Cms\Feedback\Adapter\\' . ucfirst($adapterClass);
    if (!class_exists($adapterName)) {
    // Wegen Bug in der PHP Version < 5.3.3 (https://bugs.php.net/bug.php?id=50731)
      if (version_compare(PHP_VERSION, '5.3.3') < 0) {
        require_once 'Zend/Loader.php';
        \Zend_Loader::loadClass($adapterName);
      }
      if (!class_exists($adapterName)) {
        throw new \Cms\Feedback\Exception(
            -17,
            __METHOD__,
            __LINE__,
            array('adapter' => $adapterName)
        );
      }
    }

    // Adapter initialisieren
    try {
      $adapter = new $adapterName($config);
    } catch (\Exception $e) {
      throw new \Cms\Feedback\Exception(
          -14,
          __METHOD__,
          __LINE__,
          array('mode' => $config->adapter)
      );
    }

    return $adapter;
  }
}
