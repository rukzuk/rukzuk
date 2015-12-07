<?php
namespace Rukzuk\Modules;

/**
 * Class Translator
 * @package Rukzuk\Modules
 */
class Translator
{
  private $locale;
  private $lang;
  private $dictionary = array();

  public function __construct($api, $moduleInfo, $forceLocale = null)
  {
    if (is_string($forceLocale)) {
      $this->locale = $forceLocale;
    } else {
      $this->locale = $api->getInterfaceLocale();
    }
    $splitLocale = explode('_', $this->locale);
    $this->lang = $splitLocale[0];

    $customData = $moduleInfo->getCustomData();
    if (is_array($customData) && array_key_exists('i18n', $customData)) {
      $this->dictionary = $customData['i18n'];
    }
  }

  /**
   * Returns the translations for a given key
   *
   * @param string $key The translation key
   *
   * @return array|null
   *    An array containing all translations for the given key or
   *    null if there are no translations for that key in the
   *    dictionary
   */
  public function i18n($key)
  {
    if (array_key_exists($key, $this->dictionary)) {
      return $this->dictionary[$key];
    }
    return null;
  }

  /**
   * Translates the given language key to the current system language
   *
   * @param String $key The key you whant to be tranlated
   * @param String [$fallback] The fallback string if there is no translation
   *    for the given key; The key will be used as fallback if this parameter
   *    was omitted
   *
   * @return String
   *    The translation in the current language
   *    The first translation in the dictionary
   */
  public function translate($key, $fallback = null)
  {
    $i18nData = $this->i18n($key);

    if (is_array($i18nData)) {
      if (array_key_exists($this->lang, $i18nData)) {
        return $i18nData[$this->lang];
      } else {
        return reset($i18nData);
      }
    }

    if (is_string($fallback)) {
      return $fallback;
    } else {
      return $key;
    }
  }

  /**
   * Translate json string to the current system language.
   * Falls back to text if not a valid json string.
   *
   * @param $string - json encoded text with language as keys, e.g. {"en": "text eng", "de": "text ger"}
   * @return string
   */
  public function translateInput($string)
  {
    $inlineTranslate = json_decode($string, true);
    if (is_array($inlineTranslate)) {
      if (array_key_exists($this->lang, $inlineTranslate)) {
        return $inlineTranslate[$this->lang];
      } else if (array_key_exists('en', $inlineTranslate)) {
        // fall back to en
        return $inlineTranslate['en'];
      }
      // fallback to first entry if current lang and en are not found
      return reset($inlineTranslate);
    }
    //
    return $string;
  }

  /**
   * Format Currency
   *
   * @param float   $amount - value money
   * @param string  $currency
   *
   * @return string
   */
  public function formatCurrency($amount, $currency)
  {
    $return = '';

    if (class_exists('NumberFormatter')) {
      // format (requires php intl)
      $fmt = new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY);
      if ($fmt) {
        $return = $fmt->formatCurrency($amount, $currency);
      }
    }

    // fallback
    if ($return === '' || $return === 'NaN') {
      $return = sprintf("%0.2f %s", $amount, $currency);
    }

    return $return;
  }
}