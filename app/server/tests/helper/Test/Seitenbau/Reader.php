<?php
namespace Test\Seitenbau;
/**
 * Abstract Reader Klass
 *
 * @package      Seitenbau\Test
 */
abstract class Reader
{
  /**
   * @param  $name
   * @return string
   */
  abstract function byName($name);
  /**
   * @param  string $file
   * @return string
   */
  protected function getFileContent($file)
  {
     $content = file_get_contents($file);
     if (function_exists('mb_convert_encoding')) {
       return \mb_convert_encoding(
         $content,
         'UTF-8',
         \mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)
       );
     }
     return $content;
  }
}