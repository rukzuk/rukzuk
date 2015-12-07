<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Feedback as CmsFeedback;
use Cms\Mail;
use Cms\Feedback\Adapter\Mail as MailAdapter;

/**
 * Stellt die Business-Logik fuer Feedback zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Feedback extends Base\Plain
{
  /**
   * Verschickt ein Feedback-Formular
   * @param array $attributes
   */
  public function send(array $attributes)
  {
    $feedback = new CmsFeedback(Registry::getConfig()->feedback);

    if (isset($attributes['content'])) {
      $feedback->setUserFeedback($attributes['content']);
    }
    if (isset($attributes['email'])) {
      $feedback->setEmail($attributes['email']);
    }
    if (isset($attributes['subject'])) {
      $feedback->setSubject($attributes['subject']);
    }
    if (isset($attributes['userAgent'])) {
      $feedback->setUserAgent($attributes['userAgent']);
    }
    if (isset($attributes['clientErrors'])) {
      $feedback->setClientErrors($attributes['clientErrors']);
    }
    if (isset($attributes['platform'])) {
      $feedback->setPlatform($attributes['platform']);
    }

    try {
      $feedback->send();
    } catch (\Exception $e) {
      throw new \Cms\Exception(1101, __METHOD__, __LINE__, null, $e);
    }
  }
}
