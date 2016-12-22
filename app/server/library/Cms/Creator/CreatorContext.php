<?php


namespace Cms\Creator;

use Cms\Render\InfoStorage\ContentInfoStorage\ServiceBasedContentInfoStorage;
use Cms\Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageConverter;
use Cms\Render\InfoStorage\NavigationInfoStorage\ServiceBasedNavigationInfoStorage;
use Render\ImageToolFactory\SimpleImageToolFactory;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaUrlHelper\IMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\SecureFileValidationHelper;
use Render\PageUrlHelper\NonePageUrlHelper;
use Seitenbau\RandomGenerator;
use Seitenbau\Registry;
use Cms\Request\Base as CmsRequestBase;
use Cms\Business\Website as WebsiteBusiness;
use Cms\Business\WebsiteSettings as WebsiteSettingsBusiness;
use Cms\Business\Modul as ModuleBusiness;
use Cms\Business\Page as PageBusiness;
use Cms\Business\PageType as PageTypeBusiness;
use Cms\Business\Media as MediaBusiness;
use Cms\Business\Ticket as TicketBusiness;
use Cms\Business\Template as TemplateBusiness;
use Render\IconHelper\SimpleIconHelper;
use Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage;
use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage;
use Render\InfoStorage\ContentInfoStorage\IContentInfoStorage;
use Cms\Render\InfoStorage\WebsiteInfoStorage\ServiceBasedWebsiteInfoStorage;
use Cms\Render\InfoStorage\ModuleInfoStorage\ServiceBasedModuleInfoStorage;
use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Cms\Render\InfoStorage\ServiceBasedColorInfoStorageHelper;
use Cms\Render\MediaUrlHelper\CmsCDNMediaUrlHelper;

class CreatorContext
{
  /**
   * @var \Cms\Business\Website
   */
  private $websiteBusiness;
  /**
   * @var \Cms\Business\WebsiteSettings
   */
  private $websiteSettingsBusiness;
  /**
   * @var \Cms\Business\Modul
   */
  private $moduleBusiness;
  /**
   * @var \Cms\Business\Page
   */
  private $pageBusiness;
  /**
   * @var \Cms\Business\PageType
   */
  private $pageTypeBusiness;
  /**
   * @var \Cms\Business\Media
   */
  private $mediaBusiness;
  /**
   * @var \Cms\Business\Ticket
   */
  private $ticketBusiness;
  /**
   * @var \Cms\Business\Template
   */
  private $templateBusiness;

  /**
   * @var IWebsiteInfoStorage[]
   */
  private $websiteInfoStorages = array();

  /**
   * @var IModuleInfoStorage[]
   */
  private $moduleInfoStorages = array();

  /**
   * @var IContentInfoStorage[]
   */
  private $contentInfoStorages = array();

  /**
   * @var IMediaInfoStorage[]
   */
  private $mediaInfoStorages = array();

  /**
   * @param \Cms\Business\Website         $websiteBusiness
   * @param \Cms\Business\WebsiteSettings $websiteSettingsBusiness
   * @param \Cms\Business\Modul           $moduleBusiness
   * @param \Cms\Business\Page            $pageBusiness
   * @param \Cms\Business\PageType        $pageTypeBusiness
   * @param \Cms\Business\Media           $mediaBusiness
   * @param \Cms\Business\Ticket          $ticketBusiness
   * @param \Cms\Business\Template        $templateBusiness
   */
  public function __construct(
      WebsiteBusiness $websiteBusiness,
      WebsiteSettingsBusiness $websiteSettingsBusiness,
      ModuleBusiness $moduleBusiness,
      PageBusiness $pageBusiness,
      PageTypeBusiness $pageTypeBusiness,
      MediaBusiness $mediaBusiness,
      TicketBusiness $ticketBusiness,
      TemplateBusiness $templateBusiness
  ) {
    $this->websiteBusiness = $websiteBusiness;
    $this->websiteSettingsBusiness = $websiteSettingsBusiness;
    $this->moduleBusiness = $moduleBusiness;
    $this->pageBusiness = $pageBusiness;
    $this->pageTypeBusiness = $pageTypeBusiness;
    $this->mediaBusiness = $mediaBusiness;
    $this->ticketBusiness = $ticketBusiness;
    $this->templateBusiness = $templateBusiness;
  }

  /**
   * @param $websiteId
   *
   * @return IWebsiteInfoStorage
   */
  public function getWebsiteInfoStorage($websiteId)
  {
    if (!isset($this->websiteInfoStorages[$websiteId])) {
      $this->websiteInfoStorages[$websiteId] = $this->createWebsiteInfoStorage($websiteId);
    }
    return $this->websiteInfoStorages[$websiteId];
  }

  /**
   * @param string $websiteId
   *
   * @return IWebsiteInfoStorage
   */
  protected function createWebsiteInfoStorage($websiteId)
  {
    return new ServiceBasedWebsiteInfoStorage(
        $websiteId,
        $this->websiteBusiness->getService(),
        $this->websiteSettingsBusiness->getService()
    );
  }

  /**
   * @param string $websiteId
   *
   * @return IModuleInfoStorage
   */
  public function getModuleInfoStorage($websiteId)
  {
    if (!isset($this->moduleInfoStorages[$websiteId])) {
      $this->moduleInfoStorages[$websiteId] = new ServiceBasedModuleInfoStorage(
          $websiteId,
          $this->moduleBusiness->getService()
      );
    }
    return $this->moduleInfoStorages[$websiteId];
  }

  /**
   * @param string $websiteId
   *
   * @return IContentInfoStorage
   */
  protected function createContentInfoStorage($websiteId)
  {
    return new ServiceBasedContentInfoStorage(
      $websiteId,
      $this->templateBusiness->getService()
    );
  }

  /**
   * @param string $websiteId
   *
   * @return IContentInfoStorage
   */
  public function getContentInfoStorage($websiteId)
  {
    if (!isset($this->contentInfoStorages[$websiteId])) {
      $this->contentInfoStorages[$websiteId] = $this->createContentInfoStorage($websiteId);
    }
    return $this->contentInfoStorages[$websiteId];
  }

  /**
   * @param string $websiteId
   *
   * @param string $relativePathToWebRoot
   *
   * @return IModuleInfoStorage
   */
  public function createCreatorModuleInfoStorage($websiteId, $relativePathToWebRoot)
  {
    return new CreatorServiceBasedModuleInfoStorage(
        $websiteId,
        $this->moduleBusiness->getService(),
        $relativePathToWebRoot
    );
  }

  /**
   * This MediaInfoStorage is only created once per website,
   * so be careful if you want to have different helpers!
   *
   * @param string          $websiteId
   *
   * @param IMediaUrlHelper $mediaUrlHelper
   *
   * @return IMediaInfoStorage
   */
  public function getMediaInfoStorage($websiteId, $mediaUrlHelper)
  {
    if (!isset($this->mediaInfoStorages[$websiteId])) {
      $this->mediaInfoStorages[$websiteId] = $this->createMediaInfoStorage($websiteId, $mediaUrlHelper);
    }
    return $this->mediaInfoStorages[$websiteId];
  }

  /**
   * Create MediaInfoStorage
   *
   * @param $websiteId
   * @param $mediaUrlHelper
   *
   * @return ServiceBasedMediaInfoStorage
   */
  public function createMediaInfoStorage($websiteId, $mediaUrlHelper)
  {
    $iconHelper = new SimpleIconHelper($this->getIconFilePath(), 'icon_fallback.png');
    $mediaDirectory = Registry::getConfig()->media->files->directory . DIRECTORY_SEPARATOR . $websiteId;
    $mediaService = $this->mediaBusiness->getService();
    return new ServiceBasedMediaInfoStorage($websiteId, $mediaDirectory, $mediaService, $mediaUrlHelper, $iconHelper);
  }

  /**
   * @return CmsCDNMediaUrlHelper
   */
  public function createCmsMediaUrlHelper()
  {
    $config = Registry::getConfig();
    $mediaCacheBaseDirectory = $config->media->cache->directory;
    $mediaCache = new MediaCache($mediaCacheBaseDirectory);
    $cdnUrl = $config->server->url . '/cdn/get';
    $mediaValidationHelper = new SecureFileValidationHelper(
        $mediaCache,
        false
    );
    return new CmsCDNMediaUrlHelper(
        $mediaValidationHelper,
        $cdnUrl,
        CmsRequestBase::REQUEST_PARAMETER
    );
  }

  public function getColorInfoStorage($websiteId)
  {
    return ServiceBasedColorInfoStorageHelper::getColorInfoStorage($this->websiteBusiness, $websiteId);
  }

  public function getResolutions($websiteId)
  {
    $resolutions_json = $this->websiteBusiness->getById($websiteId)->getResolutions();
    $resolutions = json_decode($resolutions_json, true);
    if (!is_array($resolutions)) {
      return array();
    }
    return $resolutions;
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return array
   */
  public function getPageContent($websiteId, $pageId)
  {
    $page = $this->getPage($websiteId, $pageId);
    $content = $this->convertContentToArray($page->getContent());
    if (isset($content[0])) {
      return $content[0];
    } else {
      return array();
    }
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return array
   */
  public function getPageMeta($websiteId, $pageId)
  {
    $page = $this->getPage($websiteId, $pageId);
    return array(
      'id' => $page->getId(),
      'websiteId' => $page->getWebsiteid(),
      'templateId' => $page->getTemplateid(),
      'name' => $page->getName(),
      'description' => $page->getDescription(),
      'date' => $page->getDate(),
      'inNavigation' => (bool)$page->getInnavigation(),
      'navigationTitle' => $page->getNavigationtitle(),
      'mediaId' => $page->getMediaId(),
      'type' => $page->getPageType(),
    );
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return array
   */
  public function getPageGlobal($websiteId, $pageId)
  {
    $page = $this->getPage($websiteId, $pageId);
    return $this->convertContentToArray($page->getGlobalContent());
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return array
   */
  public function getPageAttributes($websiteId, $pageId)
  {
    $helper = new NavigationInfoStorageConverter($this->getNavigationInfoStorage($websiteId));
    return $helper->extractPageAttributes($pageId);
  }

  /**
   * @param string $websiteId
   *
   * @return string[]
   */
  public function getPageIds($websiteId)
  {
    $pageInfoList = $this->pageBusiness->getInfosByWebsiteId($websiteId, false);
    return array_keys($pageInfoList);
  }

  /**
   * @param $websiteId
   *
   * @return array
   */
  public function getNavigation($websiteId)
  {
    $helper = new NavigationInfoStorageConverter($this->getNavigationInfoStorage($websiteId));
    return $helper->extractNavigationArray();
  }

  /**
   * ServiceBased Navigation Info Storage with custom PageUrlHelper
   *
   * @param string                               $websiteId
   * @param string                               $pageId
   * @param \Render\PageUrlHelper\IPageUrlHelper $navUrlHelper
   *
   * @return ServiceBasedNavigationInfoStorage
   */
  public function getNavigationInfoStorage($websiteId, $pageId = null, $navUrlHelper = null)
  {
    if (is_null($navUrlHelper)) {
      $navUrlHelper = new NonePageUrlHelper();
    }

    return new ServiceBasedNavigationInfoStorage(
        $this->websiteBusiness,
        $this->pageBusiness,
        $this->pageTypeBusiness,
        $websiteId,
        $navUrlHelper,
        $pageId
    );
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return \Cms\Data\Page
   */
  protected function getPage($websiteId, $pageId)
  {
    return $this->pageBusiness->getById($pageId, $websiteId);
  }

  /**
   * @param string $websiteId
   * @param string $albumId
   *
   * @return array
   */
  public function getMediaIdsByAlbumId($websiteId, $albumId)
  {
    $mediaIds = array();
    $mediaItems = $this->mediaBusiness->getByWebsiteIdAndFilter(
        $websiteId,
        array('albumid' => $albumId),
        true
    );
    foreach ($mediaItems as $media) {
      $mediaIds[] = $media->getId();
    }
    return $mediaIds;
  }

  /**
   * @return string
   */
  public function getIconFilePath()
  {
    return Registry::getConfig()->file->types->icon->directory;
  }

  /**
   * @return SimpleImageToolFactory
   */
  public function getImageToolFactory()
  {
    return new SimpleImageToolFactory(APPLICATION_PATH . '/../library');
  }

  /**
   * @return MediaCache
   */
  public function getMediaCache()
  {
    return new MediaCache(Registry::getConfig()->media->cache->directory);
  }

  /**
   * @param $websiteId
   *
   * @return \Cms\Data\Website
   */
  protected function getWebsite($websiteId)
  {
    return $this->websiteBusiness->getById($websiteId);
  }

  /**
   * @param $content
   *
   * @return array
   */
  protected function convertContentToArray($content)
  {
    if (empty($content)) {
      return array();
    }
    if (is_string($content)) {
      $content = json_decode($content, true);
    } elseif (is_array($content)) {
      $content = json_decode(json_encode($content), true);
    }
    return $content;
  }

  /**
   * @param  string $websiteId
   * @param  string $controller
   * @param  string $action
   * @param  array  $params
   *
   * @return string
   */
  public function createTicketUrl($websiteId, $controller, $action, $params)
  {
    // init
    $config = Registry::getConfig();

    // Params als JSON-String
    $paramsAsJson = json_encode($params);
    $params = array(
      $config->request->parameter => $paramsAsJson,
    );

    // Ticket aus Render Url erstellen
    $credential = null;
    if ($config->creator->accessticket->authentication) {
      $credential = array(
        'username' => RandomGenerator::generateString(10),
        'password' => RandomGenerator::generateString(10),
      );
    }
    $ticketUrl = $this->ticketBusiness->createTicketUrl(
        $websiteId,
        false, // Forwarding
        false, // Daten zu gross fuer GET-Request
        array(
        'controller' => $controller,
        'action' => $action,
        'params' => $params,
        ),
        $config->creator->accessticket->ticketLifetime,
        $config->creator->accessticket->remainingCalls,
        $config->creator->accessticket->sessionLifetime,
        $credential,
        $credential,
        true // use internal url
    );

    return $ticketUrl;
  }
}
