<?php
namespace Seitenbau;

/**
 * Opt-In-Code Generator
 *
 * @package    Seitenbau
 */

class OptinCodeGenerator
{
  const DEFAULT_CODE_LENGTH = 15;
  
  /**
   * @return string
   */
  public static function generate()
  {
    $length = OptinCodeGenerator::DEFAULT_CODE_LENGTH;
    
    $config = Registry::getConfig();
    $optinConfig = $config->get('optin');
    if ($optinConfig) {
      $length = Registry::getConfig()->optin->code->length;
    }
    
    $randomCharacters = str_split(md5(mt_rand(0, 200000) . microtime(true)));
    $mergedCharacters = array_merge($randomCharacters, range('a', 'z'));
    shuffle($mergedCharacters);

    return substr(implode('', $mergedCharacters), 0, $length);
  }
}
