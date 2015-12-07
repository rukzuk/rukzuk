<?php


namespace Render\APIs\APIv1;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists;
use Render\InfoStorage\ModuleInfoStorage\Exceptions\ModuleDoesNotExists;
use Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists;
use Render\RenderContext;
use Render\Unit;

class HeadAPI
{

  /**
   * @var RenderContext
   */
  private $renderContext;

  /**
   * @var Navigation
   */
  private $navigation;

  /**
   * @param RenderContext $renderContext
   */
  public function __construct(RenderContext $renderContext)
  {
    $this->renderContext = $renderContext;
    $this->navigation = new Navigation(
        $renderContext->getNavigationInfoStorage()
    );
  }

  /**
   * return Render\RenderContext
   */
  protected function getRenderContext()
  {
    return $this->renderContext;
  }

  /**
   * Returns true if the current renderings happens inside of
   * the rukzuk cms edit mode. Else false.
   *
   * @return bool
   */
  public function isEditMode()
  {
    return $this->getRenderContext()->getRenderMode() == RenderContext::RENDER_MODE_EDIT;
  }

  /**
   * Returns true if the current renderings happens inside of
   * the rukzuk cms preview mode. Else false.
   *
   * @return bool
   */
  public function isPreviewMode()
  {
    return $this->getRenderContext()->getRenderMode() == RenderContext::RENDER_MODE_PREVIEW;
  }

  /**
   * Returns true if the current rendering happens on a
   * live server (website is deployed). Else false.
   *
   * @return bool
   */
  public function isLiveMode()
  {
    return $this->getRenderContext()->getRenderMode() == RenderContext::RENDER_MODE_LIVE;
  }

  /**
   * Returns the resolutions array
   *
   * @return array
   */
  public function getResolutions()
  {
    return $this->getRenderContext()->getResolutions();
  }

  /**
   * Returns true when the current rendering task renders a template
   *
   * @return bool
   */
  public function isTemplate()
  {
    return $this->getRenderContext()->getRenderType() == RenderContext::RENDER_TYPE_TEMPLATE;
  }

  /**
   * Returns true when the current rendering task renders a page
   *
   * @return bool
   */
  public function isPage()
  {
    return $this->getRenderContext()->getRenderType() == RenderContext::RENDER_TYPE_PAGE;
  }

  /**
   * Returns the navigation object
   *
   * @return Navigation
   */
  public function getNavigation()
  {
    return $this->navigation;
  }

  /**
   * Convert a color id to a rgba() value
   *
   * @param string $colorId
   *
   * @return string rgba() value of the given color id
   */
  public function getColorById($colorId)
  {
    return $this->getRenderContext()->getColorInfoStorage()->getColor($colorId);
  }

  /**
   * Returns the Color Scheme as array map
   *
   * @return array (color-id => color-value)
   */
  public function getColorScheme()
  {
    $colorScheme = array();
    $colorIds = $this->getRenderContext()->getColorInfoStorage()->getColorIds();
    foreach ($colorIds as $id) {
      $colorScheme[$id] = $this->getRenderContext()->getColorInfoStorage()->getColor($id);
    }
    return $colorScheme;
  }

  /**
   * Returns the media item with the given media id
   * or null if the image does not exists.
   *
   * @param string $mediaId
   *
   * @throws MediaItemNotFoundException
   * @return MediaItem
   */
  public function getMediaItem($mediaId)
  {
    try {
      $mediaContext = $this->getRenderContext()->getMediaContext();
      return new MediaItem($mediaContext, $mediaId);
    } catch (MediaInfoStorageItemDoesNotExists $_e) {
      throw new MediaItemNotFoundException();
    }
  }

  /**
   * Returns the language code of the current cms user interface.
   *
   * @return string The language code (examples: en; de; fr)
   */
  public function getInterfaceLanguage()
  {
    $locale = explode('_', $this->getInterfaceLocale());
    return $locale[0];
  }

  /**
   * Returns the locale of the current cms user interface.
   *
   * @return string The language code (examples: en_US; de_DE; de_CH)
   */
  public function getInterfaceLocale()
  {
    return $this->getRenderContext()->getInterfaceLocaleCode();
  }

  /**
   * Returns the current locale code
   *
   * @return string   The locale code (examples: en_US; de_DE; fr)
   */
  public function getLocale()
  {
    return $this->getRenderContext()->getLocale();
  }

  /**
   * Returns the website settings with the given website settings id
   *
   * @param string $websiteSettingsId
   *
   * @return array
   * @throws WebsiteSettingsNotFound
   */
  public function getWebsiteSettings($websiteSettingsId)
  {
    try {
      return $this->getRenderContext()->getWebsiteInfoStorage()->getWebsiteSettings($websiteSettingsId);
    } catch (WebsiteSettingsDoesNotExists $e) {
      throw new WebsiteSettingsNotFound();
    }
  }
}
