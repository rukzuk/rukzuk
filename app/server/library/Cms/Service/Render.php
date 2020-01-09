<?php


namespace Cms\Service;

use Cms\Render\MediaUrlHelper\CmsCDNMediaUrlHelper;
use Cms\Render\InfoStorage\ServiceBasedColorInfoStorageHelper;
use Cms\Service\Base\Plain as PlainServiceBase;
use Dual\Render\RenderContext as LegacyRenderContext;
use Dual\Render\RenderObject as RenderObject;
use Render\Cache\FileBasedJsonCache;
use Render\Cache\ICache;
use Render\IconHelper\SimpleIconHelper;
use Render\ImageToolFactory\SimpleImageToolFactory;
use Render\InfoStorage\ColorInfoStorage\ArrayBasedColorInfoStorage;
use Render\InfoStorage\ColorInfoStorage\IColorInfoStorage;
use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage;
use Cms\Render\InfoStorage\WebsiteInfoStorage\ServiceBasedWebsiteInfoStorage;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaContext;
use Cms\Render\CmsRenderer;
use Cms\Render\PageUrlHelper\CmsPageUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\SecureFileValidationHelper;
use Render\NodeContext;
use Render\PageUrlHelper\IPageUrlHelper;
use Seitenbau\Registry;

use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Cms\Render\InfoStorage\ModuleInfoStorage\ServiceBasedModuleInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;
use Cms\Render\InfoStorage\NavigationInfoStorage\ServiceBasedNavigationInfoStorage;
use Render\RenderContext as RenderContext;
use Render\NodeTree;
use Render\NodeFactory;
use Render\InfoStorage\ContentInfoStorage\IContentInfoStorage;
use Cms\Render\InfoStorage\ContentInfoStorage\ServiceBasedContentInfoStorage;
use \Cms\Request\Base as CmsRequestBase;
use Seitenbau\FileSystem as FS;
use Orm\Data\Media as DataMedia;

class Render extends PlainServiceBase
{
  /**
   * @param mixed              $content
   * @param string             $websiteId
   * @param string             $templateId
   * @param string             $mode
   * @param array              $globalContent
   * @param \Cms\Data\Template $template
   * @param null               $codeType
   */
  public function renderTemplateContent(
      $content,
      $websiteId,
      $templateId,
      $mode,
      $globalContent,
      $template,
      $codeType = null
  ) {
    // Check page content and stop/break nicely
    $content = $this->convertContentToArray($content);
    if (empty($content)) {
      return;
    }

    // create item meta data simulation
    $currentItemInfo = array(
      'id' => $templateId,
      'websiteId' => $websiteId,
      'name' => $template->getName(),
      'pageType' => $template->getPageType(),
      'globalContent' => $globalContent
    );

    $this->renderWithNewRenderer(
        $content,
        $websiteId,
        $templateId,
        $mode,
        $currentItemInfo,
        $codeType,
        true
    );
  }

  /**
   * @param mixed          $content
   * @param string         $websiteId
   * @param string         $pageId
   * @param string         $mode
   * @param array          $globalContent
   * @param \Cms\Data\Page $page
   * @param null           $codeType
   */
  public function renderPageContent(
      $content,
      $websiteId,
      $pageId,
      $mode,
      $globalContent,
      $page,
      $codeType = null
  ) {
    // Check page content and stop/break nicely
    $content = $this->convertContentToArray($content);
    if (empty($content)) {
      return;
    }

    // create item meta data
    $currentItemInfo = array(
      'id' => $pageId,
      'websiteId' => $websiteId,
      'name' => $page->getName(),
      'pageType' => $page->getPageType(),
      'description' => $page->getDescription(),
      'date' => $page->getDate(),
      'inNavigation' => $page->getInnavigation(),
      'navigationTitle' => $page->getNavigationtitle(),
      'globalContent' => $globalContent,
    );

    $this->renderWithNewRenderer(
        $content,
        $websiteId,
        $pageId,
        $mode,
        $currentItemInfo,
        $codeType,
        false
    );
  }

  /**
   * @param array   $content
   * @param string  $websiteId
   * @param string  $pageOrTemplateId
   * @param string  $mode
   * @param array   $currentItemInfo
   * @param string  $codeType
   * @param boolean $isTemplate
   */
  protected function renderWithNewRenderer(
      array &$content,
      $websiteId,
      $pageOrTemplateId,
      $mode,
      $currentItemInfo,
      $codeType,
      $isTemplate
  ) {
    $websiteService = $this->getService('Website');
    $moduleService = $this->getService('Modul');
    $templateService = $this->getService('Template');
    $websiteSettingsService = $this->getService('WebsiteSettings');

    $websiteInfoStorage = new ServiceBasedWebsiteInfoStorage(
        $websiteId,
        $websiteService,
        $websiteSettingsService
    );
    $moduleInfoStorage = new ServiceBasedModuleInfoStorage(
        $websiteId,
        $moduleService
    );
    $contentInfoStorage = new ServiceBasedContentInfoStorage(
      $websiteId,
      $templateService
    );
    $colorInfoStorage = $this->getColorInfoStorage($websiteService, $websiteId);
    $resolutions = $this->getResolutions($websiteService, $websiteId);
    $navigationInfoStorage = $this->createNavigationInfoStorage(
        $websiteId,
        $pageOrTemplateId,
        $isTemplate,
        $currentItemInfo,
        $this->createPageUrlHelper($websiteId, $pageOrTemplateId, $isTemplate, $mode)
    );
    $usedMediaIds = $this->getMediaIdsFromContent($content);
    $mediaContext = $this->createMediaContext($websiteId, $usedMediaIds);
    $cacheImpl = $this->createCache($websiteId, $pageOrTemplateId);
    $renderContext = $this->createRenderContext(
        $mode,
        $isTemplate,
        $resolutions,
        $websiteInfoStorage,
        $moduleInfoStorage,
        $mediaContext,
        $navigationInfoStorage,
        $colorInfoStorage,
        $cacheImpl
    );
    $nodeContext = $this->createNodeContext($moduleInfoStorage, $contentInfoStorage, $pageOrTemplateId, $isTemplate);

    // Legacy Support (NOTE: this init NEEDS to be AFTER the init of all info storage and the new render context)
    LegacyRenderContext::init($renderContext, $websiteId, $pageOrTemplateId);

    $this->startNewRenderer($content, $codeType, $nodeContext, $renderContext);
  }

  /**
   * Renders the given content array with the new visitor based renderer.
   *
   * @param array $content
   * @param $codeType
   * @param \Render\NodeContext $nodeContext
   * @param \Render\RenderContext $renderContext
   */
  protected function startNewRenderer(
      array &$content,
      $codeType,
      NodeContext $nodeContext,
      RenderContext $renderContext
  ) {
    $nodeTree = $this->createNodeTree($content, $nodeContext);
    $renderer = new CmsRenderer($renderContext, $nodeTree);
    switch ($codeType) {
      case RenderObject::CODE_TYPE_CSS:
        $renderer->renderCss();
            break;
      case RenderObject::CODE_TYPE_HTML;
      default:
        $renderer->renderHtml();
            break;
    }
  }

  /**
   * @param string                 $editMode
   * @param boolean                $isTemplate
   * @param array                  $resolutions
   * @param IWebsiteInfoStorage    $websiteInfoStorage ,
   * @param IModuleInfoStorage     $moduleInfoStorage
   * @param \Render\MediaContext   $mediaContext
   * @param INavigationInfoStorage $navigationInfoStorage
   * @param IColorInfoStorage      $colorInfoStorage
   * @param \Render\Cache\ICache   $cacheImpl
   *
   * @return RenderContext
   */
  protected function createRenderContext(
      $editMode,
      $isTemplate,
      array &$resolutions,
      IWebsiteInfoStorage $websiteInfoStorage,
      IModuleInfoStorage $moduleInfoStorage,
      MediaContext $mediaContext,
      INavigationInfoStorage $navigationInfoStorage,
      IColorInfoStorage $colorInfoStorage,
      ICache $cacheImpl
  ) {
    return new RenderContext(
        $websiteInfoStorage,
        $moduleInfoStorage,
        $navigationInfoStorage,
        $colorInfoStorage,
        $mediaContext,
        $this->getInterfaceLocaleCode(),
        $editMode,
        $this->getRenderType($isTemplate),
        $resolutions,
        $this->getJsApiUrl(),
        $cacheImpl
    );
  }


  /**
   * @param $websiteId
   * @param $pageOrTemplateId
   *
   * @return FileBasedJsonCache
   */
  protected function createCache($websiteId, $pageOrTemplateId)
  {
    $config = Registry::getConfig();
    $dataPath = $config->item->data->directory;
    $cachePath = FS::joinPath($dataPath, $websiteId, 'cache', $pageOrTemplateId);
    FS::createDirIfNotExists($cachePath, true);
    return new FileBasedJsonCache($cachePath);
  }

  /**
   * @param       $websiteId
   * @param       $pageOrTemplateId
   * @param       $isTemplate
   * @param array $currentItemInfo
   * @param       $pageUrlHelper
   *
   * @return ServiceBasedNavigationInfoStorage
   */
  protected function createNavigationInfoStorage(
      $websiteId,
      $pageOrTemplateId,
      $isTemplate,
      array $currentItemInfo,
      $pageUrlHelper
  ) {
    $websiteBusiness = new \Cms\Business\Website('Website');
    $pageBusiness = new \Cms\Business\Page('Page');
    $pageTypeBusiness = new \Cms\Business\PageType('PageType');

    return new ServiceBasedNavigationInfoStorage(
        $websiteBusiness,
        $pageBusiness,
        $pageTypeBusiness,
        $websiteId,
        $pageUrlHelper,
        $pageOrTemplateId,
        $isTemplate,
        $currentItemInfo
    );
  }

  /**
   * Creates a CMS based Page URL Helper (used to get URL for rendering Pages, Templates and their
   * CSS)
   *
   * @param $websiteId
   * @param $pageOrTemplateId
   * @param $isTemplate
   * @param $mode
   *
   * @return IPageUrlHelper
   */
  protected function createPageUrlHelper($websiteId, $pageOrTemplateId, $isTemplate, $mode)
  {
    return new CmsPageUrlHelper(
        $websiteId,
        $pageOrTemplateId,
        $isTemplate,
        $this->getRenderPageServiceUrl(),
        $this->getRenderPageCssServiceUrl(),
        $this->getRenderTemplateServiceUrl(),
        $this->getRenderTemplateCssServiceUrl(),
        $mode,
        Registry::getBaseUrl()
    );
  }

  /**
   * @return string
   */
  protected function getRenderTemplateServiceUrl()
  {
    $config = Registry::getConfig();
    return $config->server->url . '/render/template/' . $config->request->parameter . '/';
  }

  /**
   * @return string
   */
  protected function getRenderTemplateCssServiceUrl()
  {
    $config = Registry::getConfig();
    return $config->server->url . '/render/templatecss/' . $config->request->parameter . '/';
  }

  /**
   * @return string
   */
  protected function getRenderPageServiceUrl()
  {
    $config = Registry::getConfig();
    return $config->server->url . '/render/page/' . $config->request->parameter . '/';
  }

  /**
   * @return string
   */
  protected function getRenderPageCssServiceUrl()
  {
    $config = Registry::getConfig();
    return $config->server->url . '/render/pagecss/' . $config->request->parameter . '/';
  }

  /**
   * @param array $content
   * @param NodeContext $nodeContext
   *
   * @return NodeTree
   */
  protected function createNodeTree(array &$content, NodeContext $nodeContext)
  {
    $nodeFactory = new NodeFactory($nodeContext);
    return new NodeTree($content, $nodeFactory);
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
      $content = \Seitenbau\Json::decode($content, \Zend_Json::TYPE_ARRAY);
    } elseif (is_array($content)) {
      $content = json_decode(json_encode($content), true);
    }
    if (isset($content[0]) && is_array($content[0])) {
      return $content[0];
    } else {
      return array();
    }
  }

  /**
   * @return string
   */
  protected function getInterfaceLocaleCode()
  {
    return (string)Registry::getLocale();
  }

  /**
   * @param $websiteService
   * @param $websiteId
   *
   * @return array
   */
  protected function getResolutions($websiteService, $websiteId)
  {
    $resolutions_json = $websiteService->getById($websiteId)->getResolutions();
    $resolutions = \Seitenbau\Json::decode($resolutions_json);
    if (!is_array($resolutions)) {
      return array();
    }
    return $resolutions;
  }

  protected function getJsApiUrl()
  {
    $config = Registry::getConfig();
    $apiFilePath = $config->client->api->file;
    $cacheBuster = (file_exists($apiFilePath) ? filemtime($apiFilePath) : time());
    return $config->client->api->url . '?' . $cacheBuster;
  }

  /**
   * @param boolean $isTemplate
   *
   * @return string
   */
  protected function getRenderType($isTemplate)
  {
    return ($isTemplate ? RenderContext::RENDER_TYPE_TEMPLATE : RenderContext::RENDER_TYPE_PAGE);
  }

  /**
   * @param string $websiteId
   * @param array  $usedMediaIds
   *
   * @return MediaContext
   */
  protected function createMediaContext($websiteId, array $usedMediaIds)
  {
    $urlHelper = $this->createMediaUrlHelper();
    $mediaService = $this->getService('Media');
    $mediaDirectory = Registry::getConfig()->media->files->directory;
    $mediaDirectory .= DIRECTORY_SEPARATOR . $websiteId;
    $iconHelper = new SimpleIconHelper(
        Registry::getConfig()->file->types->icon->directory,
        'icon_fallback.png'
    );
    $mediaInfoStorage = new ServiceBasedMediaInfoStorage(
        $websiteId,
        $mediaDirectory,
        $mediaService,
        $urlHelper,
        $iconHelper
    );
    $mediaInfoStorage->preloadMediaItems($usedMediaIds);
    $imageToolFactory = new SimpleImageToolFactory(APPLICATION_PATH . '/../library');
    $mediaContext = new MediaContext($mediaInfoStorage, $imageToolFactory);
    return $mediaContext;
  }

  /**
   * @return CmsCDNMediaUrlHelper
   */
  protected function createMediaUrlHelper()
  {
    $config = Registry::getConfig();
    $mediaCacheBaseDirectory = $config->media->cache->directory;
    $mediaCache = new MediaCache($mediaCacheBaseDirectory);
    $cdnUrl = $config->server->url . '/cdn/get';
    $mediaValidationHelper = new SecureFileValidationHelper($mediaCache, true);
    return new CmsCDNMediaUrlHelper(
        $mediaValidationHelper,
        $cdnUrl,
        CmsRequestBase::REQUEST_PARAMETER
    );
  }

  /**
   * @param        $websiteService
   * @param string $websiteId
   *
   * @return ArrayBasedColorInfoStorage
   */
  protected function getColorInfoStorage($websiteService, $websiteId)
  {
    return ServiceBasedColorInfoStorageHelper::getColorInfoStorage($websiteService, $websiteId);
  }

  /**
   * @param array $content
   *
   * @return array
   */
  protected function getMediaIdsFromContent($content)
  {
    $regexpMedia = sprintf(
        '/(%s[a-zA-Z0-9\-]+?%s)/',
        preg_quote(DataMedia::ID_PREFIX, '/'),
        preg_quote(DataMedia::ID_SUFFIX, '/')
    );
    if (preg_match_all($regexpMedia, json_encode($content), $matches, PREG_PATTERN_ORDER)) {
      return array_unique($matches[0]);
    } else {
      return array();
    }
  }

  /**
   * @param $moduleInfoStorage
   * @param $contentInfoStorage
   * @param $pageOrTemplateId
   * @param $isTemplate
   * @return NodeContext
   */
  protected function createNodeContext($moduleInfoStorage, $contentInfoStorage, $pageOrTemplateId, $isTemplate)
  {
    if ($isTemplate)
    {
      return new NodeContext($moduleInfoStorage, $contentInfoStorage, null, $pageOrTemplateId);
    }
    return new NodeContext($moduleInfoStorage, $contentInfoStorage, $pageOrTemplateId, null);
  }
}
