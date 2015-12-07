<?php
namespace Rukzuk\Modules;

/**
 * Module for Open Graph (used by Facebook to show links in posts)
 * @package Rukzuk\Modules
 */
class rz_opengraph extends SimpleModule
{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    // no content
  }

  /**
   * Content for the <head> area of the website
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {

    // TODO: allow this module only once!

    $ogTitle = new HtmlTagBuilder('meta', array('property' => 'og:title', 'content' => $api->getFormValue($unit, 'ogTitle')));

    $ogType = new HtmlTagBuilder('meta', array('property' => 'og:type', 'content' => $api->getFormValue($unit, 'ogType')));

    $url = $api->getFormValue($unit, 'ogUrl');
    $ogUrl = new HtmlTagBuilder('meta', array('property' => 'og:url', 'content' => $api->getFormValue($unit, 'ogUrl')));

    $ogImageStr = '';
    try {
      $mediaItem = $api->getMediaItem($api->getFormValue($unit, 'ogImage'));
      $imgUrl = $mediaItem->getUrl();
      $absoluteImgUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $imgUrl;
      $ogImage = new HtmlTagBuilder('meta', array('property' => 'og:image', 'content' => $absoluteImgUrl));
      $ogImageStr =  $ogImage->toString();
    } catch (\Exception $ignore) {
    }

    $ogDesc = new HtmlTagBuilder('meta', array('property' => 'og:description', 'content' => $api->getFormValue($unit, 'ogDesc')));

    return $ogTitle->toString() . $ogType->toString() . $ogUrl->toString() . $ogImageStr . $ogDesc->toString();
  }

}