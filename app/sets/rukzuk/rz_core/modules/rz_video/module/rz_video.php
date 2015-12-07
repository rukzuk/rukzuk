<?php
namespace Rukzuk\Modules;

class rz_video extends SimpleModule
{

  static protected $fallbackRatio = 56.25; // 16:9

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $embedCode = $renderApi->getFormValue($unit, 'htmlCode');
    if (!empty($embedCode)) {
      try {
        $attributes = $this->extractAttributes($embedCode);
        echo $this->renderVideo($attributes['src'], $attributes['ratio']);
      } catch (\Exception $e) {
        // in case of errors just echo the entered code
        echo $embedCode;
      }
    }

    $renderApi->renderChildren($unit);
  }

  /**
   * Extracts src and ratio from HTML embed code
   * TODO could be done via JavaScript on formValueChange but wouldn't be downward compatible
   * @param $embedCode
   * @return array
   * @throws \Exception
   */
  protected function extractAttributes($embedCode)
  {
    $pregResult = array();

    preg_match('/src=["\']([^"\']*)/i', $embedCode, $pregResult);
    if (count($pregResult)) {
      $src = $pregResult[1];
    }

    preg_match('/width\=["\']([0-9]*)["\']/i', $embedCode, $pregResult);
    if (count($pregResult)) {
      $width = (int)$pregResult[1];
    }

    preg_match('/height\=["\']([0-9]*)["\']/i', $embedCode, $pregResult);
    if (count($pregResult)) {
      $height = (int)$pregResult[1];
    }

    if (isset($src)) {
      $ratio = $this::$fallbackRatio;
      if (isset($width) && isset($height)) {
        $ratio = ($height / $width) * 100;
      }

      return array(
        'src' => $src,
        'ratio' => $ratio
      );
    } else {
      throw new \Exception();
    }
  }

  /**
   * @param $src
   * @param $ratio
   */
  protected function renderVideo($src, $ratio)
  {
    // fill height element which helps to keep aspect ratio of video (using %-padding technique)
    $fillHeight = new HtmlTagBuilder('div', array(
      'class' => 'fillHeight',
      'style' => 'padding-bottom: ' . sprintf('%F', $ratio) . '%;'
    ));

    $videoWrapper = new HtmlTagBuilder('div', array(
      'class' => 'videoWrapper'
    ), array(new HtmlTagBuilder('iframe', array(
      'data-src' => $src,
      'class' => 'lazyload',
      'allowfullscreen' => null
    )), $fillHeight));

    echo $videoWrapper->toString();
  }

}
