<?php
namespace Cms;

use \Zend_Mail;
use \Seitenbau\Registry;

/**
 * Mail
 *
 * @package      Cms
 */

class Mail extends Zend_Mail
{
  const TRANSPORT_TYPE_FILE = 'file';
  const TRANSPORT_TYPE_SMTP = 'smtp';
  const TRANSPORT_TYPE_SENDMAIL = 'sendmail';
  const TRANSPORT_TYPE_VOID = 'void';

  private $defaultTransport = self::TRANSPORT_TYPE_SENDMAIL;

  /**
   * @var array
   */
  private $transportClassMappings = array(
    self::TRANSPORT_TYPE_FILE => 'Seitenbau\Mail\Transport\File',
    self::TRANSPORT_TYPE_SMTP => 'Zend_Mail_Transport_Smtp',
    self::TRANSPORT_TYPE_SENDMAIL => 'Zend_Mail_Transport_Sendmail',
    self::TRANSPORT_TYPE_VOID => 'Cms\Mail\Transport\Void',
  );

  /**
   * @param string $charset
   */
  public function __construct($charset = null)
  {
    parent::__construct($charset);
    $this->initTransport();
  }

  /**
   * Legt den Transport der Mail anhand der Konfiguration fest
   */
  protected function initTransport()
  {
    $configMailTransport = (Registry::getConfig()->mail->transport)
                         ? Registry::getConfig()->mail->transport
                         : $this->defaultTransport;

    $transportClass = (isset($this->transportClassMappings[$configMailTransport]))
                    ? $this->transportClassMappings[$configMailTransport]
                    : null;

    if ($transportClass == null) {
      throw new \Cms\Mail\Exception(
          -41,
          __METHOD__,
          __LINE__,
          array('transport' => $configMailTransport)
      );
    }

    if (!class_exists($transportClass)) {
      throw new \Cms\Mail\Exception(
          -40,
          __METHOD__,
          __LINE__,
          array('transportclass' => $transportClass)
      );
    }

    try {
      $transportInstance = new $transportClass();
    } catch (\Exception $e) {
      throw new \Cms\Mail\Exception(
          -42,
          __METHOD__,
          __LINE__,
          array('transportclass' => $configMailTransport)
      );
    }

    self::setDefaultTransport($transportInstance);
  }

  /**
   * @return \Zend_Mail_Transport_Abstract
   */
  public function getTransport()
  {
    return Zend_Mail::$_defaultTransport;
  }
}
