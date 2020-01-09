<?php
namespace Rukzuk\Modules;

class rz_style_font extends SimpleModule
{
  // remember all already loaded google fonts
  // TODO: remove the static, if backend only creates each module once (per render run)
  static private $googleFonts = array();

  /**
   * Load Google Fonts
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {
    $ws = $api->getWebsiteSettings('privacy');

    if ($ws['allowLoadOfGoogleFonts'] == true) {
      return ""; // end here
    }

    $fonts = array();
    $googleFontFormValue = $api->getFormValue($unit, 'cssFontFamilyGoogle');

    foreach($googleFontFormValue as $res => $font) {
      if($res === 'type') {
        continue;
      }
      if ($font != '' && !in_array($font, self::$googleFonts)) {
        array_push(self::$googleFonts, $font);
        $fonts[] = '<link href="https://fonts.googleapis.com/css?family=' . htmlspecialchars(urlencode($font)) . ':100,200,300,400,500,600,700,800,900,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic" data-font-name="' . htmlspecialchars($font) . '" rel="stylesheet">';
      }
    }

    return implode("\n", $fonts);
  }
}
