<?php
namespace Cms\Controller\Plugin;

use Seitenbau\Registry as Registry;
use \Cms\Access\Manager as AccessManager;
use Seitenbau\Locale as SbLocale;

/**
 * set the current locale definition
 *
 * @package    Cms
 * @subpackage Controller\Plugin
 */

class LocaleSetup extends \Zend_Controller_Plugin_Abstract
{
  public function __construct()
  {
  }

  /**
   * Wird nach dem Routing aufgerufen
   *
   * @param  Zend_Controller_Request_Abstract $request
   * @return void
   */
  public function routeShutdown(\Zend_Controller_Request_Abstract $request)
  {
    $lang = $request->getParam('lang');
    if (isset($lang) && $this->setCurrentLang($request->getParam('lang'))) {
      return;
    }

    $identity = AccessManager::singleton()->getIdentityAsArray();
    if (isset($identity['language']) && $this->setCurrentLang($identity['language'])) {
      return;
    }
  }
  
  /**
   * @param  string $lang
   * @return void
   */
  protected function setCurrentLang($lang)
  {
    if (empty($lang) || !SbLocale::isLocale($lang, true)) {
      return false;
    }

    $locale = new SbLocale($lang);
    if (!($locale instanceof SbLocale)) {
      return false;
    }

    Registry::getLocale()->setLocale($locale);
    $translate = Registry::get('Zend_Translate');
    if ($translate instanceof \Zend_Translate) {
      $translate->setLocale($locale);
    }
    return true;
  }
}
