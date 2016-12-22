<?php
namespace Rukzuk\Modules;

use Render\APIs\APIv1\MediaImage;

class ResponsiveImageBuilder {

  private $api;
  private $unit;
  private $moduleInfo;

  static private $responsiveImageResolutions = array(320, 480, 768, 1024, 1280, 1440, 1600, 1920);
  static private $previewResponsiveImageResolution = 200;
  static private $maxResponsiveImageResolution = 1920;
  static private $responsiveImageDefaultQuality = 95;

  public function __construct($api, $unit, $moduleInfo = null)
  {
    $this->api = $api;
    $this->unit = $unit;
    $this->moduleInfo = $moduleInfo;
  }

  /**
   * Returns the url to placeholder image
   * @private
   */
  private function getPlaceholderUrl()
  {
    $parentUnit = $this->api->getParentUnit($this->unit);
    $rootUnit = $this->unit;

    while ($parentUnit !== null) {
      $rootUnit = $parentUnit;
      $parentUnit = $this->api->getParentUnit($rootUnit);
    }

    $rootModuleInfo = $this->api->getModuleInfo($rootUnit);
    return $rootModuleInfo->getAssetUrl('images/imageBlank.png');
  }

  /**
   * Convenience helper method to get a value from an array
   * @private
   */
  private function getValue($key, $cfg, $default = null)
  {
    if (is_array($cfg) && isset($cfg[$key])) {
      return $cfg[$key];
    } else {
      return $default;
    }
  }

  /**
   * Helper which creates the image urls for each of the pre-defined device
   * resolutions (see {@link $responsiveImageResolutions})
   * @param object $mediaItem
   * @param image object
   * @return array width -> url
   * @private
   */
  private function getResponsiveImageUrls($mediaItem, $imgConfig) {
    $imgUrls = array();
    $resolutions = ResponsiveImageBuilder::$responsiveImageResolutions;
    $image = $mediaItem->getImage();

    // add original width as resolution
    $originalWidth = $image->getOriginalWidth();
    if ($originalWidth < ResponsiveImageBuilder::$maxResponsiveImageResolution) {
      $resolutions[] = $originalWidth;
    }

    foreach ($resolutions as $width) {
      // skip if width would be higher than original (up-scaled)
      if ($width > $originalWidth) {
        continue;
      }

      $image->resetOperations();
      $imgUrls[$width] = $this->getResponsiveImageUrl($image, $imgConfig, $width);
    }

    return $imgUrls;
  }

  /**
   * Helper which creates the image url for one width
   * @param object $image
   * @param object $imgConfig
   * @param int $width
   * @return string url
   * @private
   */
  private function getResponsiveImageUrl($image, $imgConfig, $width) {
    $originalWidth = $image->getOriginalWidth();

    // apply cropping
    $cropCfg = $this->getValue('crop', $imgConfig);
    if ($cropCfg) {
      $image->crop($cropCfg['x'], $cropCfg['y'], $cropCfg['width'], $cropCfg['height']);
    }

    // apply resizing
    $resizeCfg = $this->getValue('resize', $imgConfig);
    if ($resizeCfg) {
      $image->resizeCenter($resizeCfg['width'], $resizeCfg['height']);
    }

    // resize image to current resolution width
    if ($width != $originalWidth) {
      $image->resizeScale($width);
    }

    // apply quality
    $image->setQuality(
      $this->getValue('quality', $imgConfig, ResponsiveImageBuilder::$responsiveImageDefaultQuality)
    );

    return $image->getUrl();
  }

  /**
   * Creates an html tag object that output build the basis for the javascript
   * responsive image lib
   *
   * @param string|\Render\APIs\APIv1\MediaImage $image
   *    The media image object or its id
   * @param array $imageCfg
   *    The image manipulations; The following keys/values are supported
   *    # 'crop'    => array('x' => int, 'y' => int, 'width' => int, 'height' => int)
   *    # 'resize'  => array('width' => int, 'height' => int)
   *    # 'quality' => int
   * @param array attributes
   *    A set of additional tag attributes (key -> attribute name, value -> attribute value)
   * @return \Rukzuk\Modules\HtmlTagBuilder
   */
  public function getImageTag($image = null, $imageCfg = null, $attributes = null)
  {
    // wrapper
    $wrapper = new HtmlTagBuilder('div', array(
      'class' => 'responsiveImageWrapper',
    ));
    // image tag
    $imgTag = new HtmlTagBuilder('img', array(
      'alt' => ''
    ));
    $imgTag->set($attributes);
    $imgTag->addClass('imgSize');

    // fill height element which helps to keep aspect ratio of image (using %-padding technique)
    $fillHeight = new HtmlTagBuilder('div', array(
      'class' => 'fillHeight'
    ));

    try {
      if (is_string($image)) {
        // get the image object if a image id was given
        $image = $this->api->getMediaItem($image)->getImage();
      }
    } catch (Exception $e) {
    }

    // we have a image (object)
    if (is_object($image)) {

      // height in percent of the width (used for padding trick)
      $origWidth = $image->getOriginalWidth();
      $origHeight = $image->getOriginalHeight();

      $origHeightRatio = ($origHeight / $origWidth) * 100;

      // create the image urls for each of the pre-defined and most common device resolutions
      $imgUrls = $this->getResponsiveImageUrls($image->getMediaItem(), $imageCfg);

      // provide big, not cropped image,
      // used in image cropper (panzoom) and for rz_lightbox
      try {
        $image->resetOperations();
        $image = $image->resizeScale(ResponsiveImageBuilder::$maxResponsiveImageResolution);
        $imgTag->set('data-cms-origsrc', $image->getUrl());
      } catch (Exception $e) {
        // do nothing
      }

      // indicate that we have an actual image
      $wrapper->addClass('hasImage');

      // data for js image replacement code (lazy sizes)

      // build src set
      $srcSet = array();
      foreach ($imgUrls as $w => $u) {
        $srcSet[] = $u.' '.$w.'w';
      }

      $imgTag->set(array(
        'data-sizes' => 'auto',
        'data-srcset' => implode(', ', $srcSet)
      ));
      $imgTag->addClass('lazyload');
      $imgTag->addClass('responsiveImage');

      // set default image (smallest resolution LQIP for fast responses)
      $image->resetOperations();
      // set default image only for screenshoter
      $preloadSrc = $this->getResponsiveImageUrl($image, $imageCfg, ResponsiveImageBuilder::$previewResponsiveImageResolution);
      if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/phantomjs/i", $_SERVER['HTTP_USER_AGENT'])) {
        $imgTag->set('src', $preloadSrc);
      }
      // set fill height value
      $imgHeight = $this->api->getFormValue($this->unit, 'imgHeight', '0%');
      // use original aspect ratio as height if 0%
      $fillHeightPaddingBottom = $imgHeight == '0%' ? sprintf('%F', $origHeightRatio).'%' : $imgHeight;
    } else {
      // show a blank image
      $imgTag->addClass('blankImgPlaceholder')->set('src', $this->getPlaceholderUrl());
      // set fill height value - use 50% if it is 0% (original image) because blank image has no size (it is a background)
      $blankHeight = $this->api->getFormValue($this->unit, 'imgHeight', '0%');
      // use 50% as height, because we don't have a original aspect ratio
      $fillHeightPaddingBottom = $blankHeight == '0%' ? '50%' : $blankHeight;
    }

    $fillHeight->set('style', 'padding-bottom: ' . $fillHeightPaddingBottom . ';');
    $wrapper->append($imgTag);
    $wrapper->append($fillHeight);

    return $wrapper;
  }
}

