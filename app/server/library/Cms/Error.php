<?php
namespace Cms;

use Seitenbau\Registry as Registry;

/**
 * Cms Error
 *
 * Mit dieser Klasse koennen die Daten zu den in der Config hinterlegten Error
 * verwendet werden
 *
 * @package      Cms
 */

class Error
{
  static protected $errors = null;
  
  static protected $defaultError = array(
   'priority' => 'ERR',
   'message'  => 'unknown error',
  );

  /**
   * @param int $code
   * @return array
   */
  public static function getPriorityByCode($code)
  {
    $error = self::getError($code);
    return $error['priority'];
  }

  public static function getMessageByCode($code, $data = array(), $locale = null)
  {
    $message = self::_('error.message.'.$code, $locale);
    return self::replaceMessagePlaceholder($message, $data);
  }
  
  /**
   * @param string $message
   * @param array $data
   */
  protected static function replaceMessagePlaceholder($message, $data)
  {
    if (is_array($data) && count($data) > 0) {
      foreach ($data as $key => $value) {
        if (is_scalar($value)) {
          $message = str_replace('{' . $key . '}', $value, $message);
        }
      }
    }
    return $message;
  }

  protected static function loadErrors()
  {
    if (!is_array(self::$errors)) {
      $config = new \Zend_Config_Ini(
          APPLICATION_PATH . '/configs/errors.ini'
      );
      self::$errors = $config->get('errors')->toArray();
    }
  }

  /**
   * @param int $code
   * @return array
   */
  public static function getError($code)
  {
    self::loadErrors();
    if (is_array(self::$errors) && (array_key_exists($code, self::$errors))) {
      return self::$errors[$code];
    }
    return self::getDefaultError();
  }

  /**
   * @return array
   */
  protected static function getDefaultError()
  {
    return self::$defaultError;
  }

  /**
   * @param string $key
   * @param Seitenbau\Locale $locale
   * @return string
   */
  protected static function _($key, $locale = null)
  {
    if (is_null($locale)) {
      $locale = Registry::getLocale('Zend_Translate');
    }
    return Registry::get('Zend_Translate')->_($key, $locale);
  }
}
