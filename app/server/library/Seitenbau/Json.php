<?php
namespace Seitenbau;

use Seitenbau\Json\JsonException as JsonException;

/**
 * @package      Seitenbau
 * @subpackage   Json
 */
class Json
{
  const TYPE_OBJECT = 0;
  const TYPE_ARRAY  = 1;
  const JSON_INDENT = '    ';
  
  public static function encode($value)
  {
    try {
      return \Zend_Json::encode($value);
    } catch (\Exception $e) {
      throw new JsonException('error while encoding json', 1, $e);
    }
  }
  
  public static function decode($json, $type = self::TYPE_OBJECT)
  {
    try {
      if ($type == self::TYPE_ARRAY) {
        return \Zend_Json::decode($json, \Zend_Json::TYPE_ARRAY);
      } else {
        return \Zend_Json::decode($json, \Zend_Json::TYPE_OBJECT);
      }
    } catch (\Exception $e) {
      throw new JsonException('error while decoding json', 2, $e);
    }
  }

  public static function prettyPrint($json, $indentStr = self::JSON_INDENT)
  {
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {
      // Grab the next character in the string.
      $char = substr($json, $i, 1);

      // Are we inside a quoted string?
      if ($char == '"' && $prevChar != '\\') {
          $outOfQuotes = !$outOfQuotes;

      // If this character is the end of an element,
      // output a new line and indent the next line.
      } elseif (($char == '}' || $char == ']') && $outOfQuotes) {
        $result .= $newLine;
        $pos --;
        for ($j=0; $j<$pos; $j++) {
          $result .= $indentStr;
        }
      }

      // Add the character to the result string.
      $result .= $char;

      // If the last character was the beginning of an element,
      // output a new line and indent the next line.
      if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
        $result .= $newLine;
        if ($char == '{' || $char == '[') {
          $pos ++;
        }

        for ($j = 0; $j < $pos; $j++) {
          $result .= $indentStr;
        }
      }

      $prevChar = $char;
    }

    if (!self::isValid($result)) {
      throw new JsonException('pretty printing json failed', 3);
    }

    return $result;
  }

  public static function isValid($json)
  {
    try {
      self::decode($json);
    } catch (\Exception $e) {
      return false;
    }
    return true;
  }
}
