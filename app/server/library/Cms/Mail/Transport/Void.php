<?php
namespace Cms\Mail\Transport;

/**
 * @package      Cms
 * @subpackage   Mail
 */
class Void extends \Zend_Mail_Transport_Abstract
{
  protected function _sendMail()
  {
  }
}
