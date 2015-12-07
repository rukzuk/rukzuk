<?php


namespace Render;

use Render\Cache\ICache;
use Render\Cache\NoneCache;
use Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage;
use Render\InfoStorage\ColorInfoStorage\IColorInfoStorage;
use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;

class RenderContext
{
  // MODES

  /** Edit Mode */
  const RENDER_MODE_EDIT = 'EDIT';
  
  /** Preview Mode (inside CMS / ticket link) */
  const RENDER_MODE_PREVIEW = 'PREVIEW';

  /** Live Mode (previously known as SHOW) */
  const RENDER_MODE_LIVE = 'LIVE';
  
  /** Creator Mode (analyze structure, and probably cache values, e.g. css) */
  const RENDER_MODE_CREATOR = 'CREATOR';

  // TYPES

  /** rendering a page (which is generated from a template) */
  const RENDER_TYPE_PAGE = 'PAGE';

  /** rendering a template */
  const RENDER_TYPE_TEMPLATE = 'TEMPLATE';

  /**
   * @var string
   */
  private $renderMode;

  /**
   * @var array
   */
  private $resolutions;

  /**
   * @var null|string
   */
  private $jsApiUrl;

  /**
   * @var IWebsiteInfoStorage
   */
  private $websiteInfoStorage;

  /**
   * @var IModuleInfoStorage
   */
  private $moduleInfoStorage;

  /**
   * @var INavigationInfoStorage
   */
  private $navigationInfoStorage;

  /**
   * @var string
   */
  private $renderType;

  /**
   * @var MediaContext
   */
  private $mediaContext;

  /**
   * @var InfoStorage\ColorInfoStorage\IColorInfoStorage
   */
  private $colorInfoStorage;
  /**
   * @var string
   */
  private $interfaceLocaleCode;
  /**
   * @var ICache
   */
  private $cache;
  /**
   * @var string
   */
  private $locale = 'en_US';

  /**
   * @param IWebsiteInfoStorage    $websiteInfoStorage
   * @param IModuleInfoStorage     $moduleInfoStorage
   * @param INavigationInfoStorage $navigationInfoStorage
   * @param IColorInfoStorage      $colorInfoStorage
   * @param MediaContext           $mediaContext
   * @param string                 $interfaceLocaleCode
   * @param string                 $renderMode
   * @param string                 $renderType
   * @param array                  $resolutions
   * @param string|null            $jsApiUrl
   * @param ICache                 $cache
   */
  public function __construct(
      IWebsiteInfoStorage $websiteInfoStorage,
      IModuleInfoStorage $moduleInfoStorage,
      INavigationInfoStorage $navigationInfoStorage,
      IColorInfoStorage $colorInfoStorage,
      MediaContext $mediaContext,
      $interfaceLocaleCode,
      $renderMode = self::RENDER_MODE_PREVIEW,
      $renderType = self::RENDER_TYPE_TEMPLATE,
      array $resolutions = array(),
      $jsApiUrl = null,
      ICache $cache = null
  ) {
    if (is_null($cache)) {
      $cache = new NoneCache();
    }
    $this->cache = $cache;
    $this->renderMode = $renderMode;
    $this->renderType = $renderType;
    $this->resolutions = $resolutions;
    $this->jsApiUrl = $jsApiUrl;
    $this->websiteInfoStorage = $websiteInfoStorage;
    $this->moduleInfoStorage = $moduleInfoStorage;
    $this->navigationInfoStorage = $navigationInfoStorage;
    $this->mediaContext = $mediaContext;
    $this->colorInfoStorage = $colorInfoStorage;
    $this->interfaceLocaleCode = $interfaceLocaleCode;
  }

  /**
   * @return string
   */
  public function getRenderMode()
  {
    return $this->renderMode;
  }

  /**
   * @return string
   */
  public function getRenderType()
  {
    return $this->renderType;
  }

  /**
   * @return array
   */
  public function getResolutions()
  {
    return $this->resolutions;
  }

  /**
   * @return null|string
   */
  public function getJsApiUrl()
  {
    return $this->jsApiUrl;
  }

  /**
   * @return IWebsiteInfoStorage
   */
  public function getWebsiteInfoStorage()
  {
    return $this->websiteInfoStorage;
  }

  /**
   * @return IModuleInfoStorage
   */
  public function getModuleInfoStorage()
  {
    return $this->moduleInfoStorage;
  }

  /**
   * @return INavigationInfoStorage
   */
  public function getNavigationInfoStorage()
  {
    return $this->navigationInfoStorage;
  }

  /**
   * @return MediaContext
   */
  public function getMediaContext()
  {
    return $this->mediaContext;
  }

  /**
   * @return IColorInfoStorage
   */
  public function getColorInfoStorage()
  {
    return $this->colorInfoStorage;
  }

  /**
   * @return ICache
   */
  public function getCache()
  {
    return $this->cache;
  }

  /**
   * Returns the locale of the current cms user interface.
   *
   * @return string The language code (examples: en_US; de_DE; de_CH)
   */
  public function getInterfaceLocaleCode()
  {
    return $this->interfaceLocaleCode;
  }

  /**
   * Returns the locale information
   *
   * @return string The language code (examples: en_US; de_DE; de_CH)
   */
  public function getLocale()
  {
    return $this->locale;
  }

  /**
   * Sets the locale information
   *
   * @param string $locale  The language code (examples: en_US; de_DE; de_CH)
   */
  public function setLocale($locale)
  {
    $this->locale = strtr($locale, '-', '_');
  }
}
