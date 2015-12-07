<?php
namespace Cms\Feedback\Adapter;

use Cms\Service\MailBuilder;

/**
 * Feedback Mail Adapter
 *
 * @package      Cms
 */

class Mail extends Base
{
  private $config;

  public function __construct(\Zend_Config $config)
  {
    $this->setConfig($config);
    //$this->setMailer(new \Zend_Mail());
  }

  public function send(\Cms\Feedback $feedback)
  {
    if (!$this->isActiv()) {
      return false;
    }

    $mailBuilder = new MailBuilder();
    $feedbackMail = $mailBuilder->getFeedbackMail($feedback);

    if ($this->config->date) {
      $feedbackMail->setDate($this->config->date);
    }
    
    $this->setSpecificTransporterSettings($feedbackMail->getTransport());
    
    $feedbackMail->send();
  }

  public function isActiv()
  {
    if ($this->getConfig()->activ == true) {
      return true;
    }

    return false;
  }

  /**
   *
   * @return \Zend_Config
   */
  public function getConfig()
  {
    return $this->config;
  }

  public function setConfig(\Zend_Config $config)
  {
    $this->config = $config;
  }

  /**
   * Ggf. Spezielle Optionen/ Konfigurationen fuer einzelne Transporter setzen
   */
  protected function setSpecificTransporterSettings(
      \Zend_Mail_Transport_Abstract $transporter
  ) {
    // File-Transporter
    if ($transporter instanceof \Zend_Mail_Transport_File) {
      $this->setTransporterFileSettings($transporter);
    }
  }

  /**
   * Legt die speziellen Settings fuer den (Zend) File-Transporter fest
   *
   * @param \Zend_Mail_Transport_Abstract $transporter
   */
  protected function setTransporterFileSettings(
      \Zend_Mail_Transport_File $transporter
  ) {
    $options = array();
    if (isset($this->getConfig()->file) && isset($this->getConfig()->file->path)) {
      $options['path'] = $this->getConfig()->file->path;
    }
    $transporter->setOptions($options);
  }
}
