<?php
namespace Test\Rukzuk;

use \Rukzuk\Modules\Lib;

require_once(MODULE_PATH . '/rz_root/module/lib/dyncss/DynCSS.php');

class CssTestCase extends ModuleTestCase
{

  private static $cssEngine = null;

  protected function createCss($unitConf = null, $moduleInfoConf = null, $moduleConf = null)
  {
    // create a mock of render API using the given configurations (e.g. unit
    // form values, module class name, asset paths, ...)
    $rootApi = new RenderApiMock(array(
      'module' => $this->createModule($moduleConf),
      'moduleInfo' => $this->createModuleInfo($moduleInfoConf),
      'unit' => $this->createUnit($unitConf)
    ));

    return $this->createCssWithApi($rootApi);
  }

  protected function createCssWithApi($api) {
    if (is_array($api)) {
      $api = $this->createRenderApi($api);
    }

    $unit = $this->createUnit();


    // trigger CSS generation
    $dynCss = new Lib\DynCSS(self::getCssEngine());

    ob_start();
    $dynCss->generateCSS($api, $unit, false);
    $cssCode = ob_get_contents();
    ob_end_clean();

    if (count($dynCss->getErrors()) > 0) {
      $this->fail(implode(PHP_EOL, $dynCss->getErrors()));
    }

    return $cssCode;
  }

  /**
   * @return Lib\DynCSSEngine
   */
  protected static function getCssEngine()
  {
    // singleton
    if (is_null(self::$cssEngine)) {
      self::$cssEngine = new Lib\DynCSSEngine(MODULE_PATH . '/rz_root/assets');
    }
    return self::$cssEngine;
  }

  //
  //
  // asserts
  //
  //

  /**
   * Asserts that the CSS code contains a given selector and if a set of styles
   * is provided, it will check if all styles are applied to this selector
   * @param string $rawCssCode       The raw CSS code
   * @param string $assertedSelector The selector which must be contained in the css code
   * @param array  $assertedStyles   Optional; A set of style which should be applied to the
   *                                 selector; If ommitted it will only be checked if the
   *                                 CSS code contains the given selector
   */
  protected function assertContainsCssRule($rawCssCode, $assertedSelector, $assertedStyles)
  {
    $cssCode = $this->formatCssCode($rawCssCode);
    $selectorFound = false;

    foreach ($cssCode as $selector => $styles) {
      if ($this->containsSelector($assertedSelector, $selector)) {
        $selectorFound = true;

        foreach ($assertedStyles as $key => $style) {
          if (in_array($style, $styles)) {
            unset($assertedStyles[$key]);
          }
        }
      }
    }

    if (!$selectorFound) {
      $this->fail("The selector \"$assertedSelector\" was not found in the following css code:\n$rawCssCode");
    }

    if (count($assertedStyles) > 0) {
      $missing = implode("\n - ", $assertedStyles);
      $this->fail("The following styles are not applied to \"$assertedSelector\":\n - $missing\nby the css code\n$rawCssCode");
    }
  }

  /**
   * Succeeds if
   *   #selector {
   *   }
   * or just an empty string
   * @param $rawCssCode
   * @param $msg
   */
  protected function assertEmptyCssBody($rawCssCode, $msg = 'CSS Body should be empty')
  {
    if (strpos($rawCssCode, '{')) {
      $innerCss = trim(preg_replace('/\}$/', '', preg_replace('/^.*\{/', '', $rawCssCode)));
    } else {
      $innerCss = $rawCssCode;
    }

    $this->assertEmpty($innerCss, $msg . "\n\t" . 'Found CSS Rules: "' . $innerCss . '"');
  }

  //
  //
  // helper
  //
  //

  private function containsSelector($needle, $haystack)
  {
    foreach (explode(',', $haystack) as $singleSelector) {
      if ($needle === trim($singleSelector)) {
        return true;
      }
    }
    return false;
  }


  private function formatCssCode($code)
  {
    $result = array();
    $split = preg_split('/{|}/', $code);
    $selector = '';

    for ($i = 0; $i < count($split); $i++) {
      $str = trim($split[$i]);
      if ($i % 2 === 0) {
        $selector = $str;
      } else {
        // the style values
        $result[$selector] = array_map(function ($style) {
          return trim($style);
        }, preg_split('/\n/', $str));
      }
    }

    return $result;
  }
}
