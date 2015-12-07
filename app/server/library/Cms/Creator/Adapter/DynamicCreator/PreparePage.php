<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Cms\Creator\CreatorContext;
use Cms\Exception as CmsException;
use Orm\Data\Media as DataMedia;
use Orm\Data\Album as DataAlbum;
use Render\Cache\FileBasedJsonCache;
use Render\Exceptions\NoContentException;
use Render\MediaContext;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\NoneValidationHelper;
use Render\Nodes\INode;
use Render\RenderContext;
use Render\Visitors\CssOnlyVisitor;
use Render\Visitors\HtmlVisitor;
use Seitenbau\Http as SbHttp;
use Seitenbau\Logger;
use Render\NodeFactory;
use Render\NodeTree;
use Dual\Render\RenderContext as LegacyRenderContext;

/**
 * Class PreparePage
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class PreparePage
{
  /**
   * @var \Cms\Creator\CreatorContext
   */
  private $creatorContext;

  /**
   * @var SiteStructure
   */
  private $structure;

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var string
   */
  private $pageId;

  /**
   * @var PreparePageResult
   */
  private $result;

  /**
   * @var array
   */
  private $mediaUrlCalls = array();

  /**
   * @var MediaHelper
   */
  private $mediaHelper;

  /**
   * @param CreatorContext $creatorContext
   * @param string         $websiteId
   * @param array          $info
   */
  public function __construct(
      CreatorContext $creatorContext,
      $websiteId,
      array $info
  ) {
    $this->creatorContext = $creatorContext;
    $this->websiteId = $websiteId;
    $this->validateAndInitInfo($info);
  }

  /**
   * The actual prepare, i. e. do stuff
   */
  public function prepare()
  {
    $this->initPreparePageResult();
    try {
      $rootNode = $this->createRootNode();
    } catch (NoContentException $ignore) {
      return $this->result;
    }

    // prepare content
    $this->prepareContent($rootNode);
    // call css cache AFTER prepareContent, as we then know about legacy support and such
    $this->prepareCssCache($rootNode);

    // render the html to getall media calls (as well as cache the output result)
    // do this only if we are not in legacy support
    if (!$this->result->getLegacySupport()) {
      // $this->prepareHtmlCache($rootNode); // TODO: enable this if you want to have pre-generated images for all image calls
    }

    // remember media url calls
    $this->result->setMediaUrlCalls($this->mediaUrlCalls);

    return $this->result;
  }

  /**
   * initialize the result object
   */
  protected function initPreparePageResult()
  {
    $this->result = new PreparePageResult();
    $this->result->setWebsiteId($this->getWebsiteId());
    $this->result->setPageId($this->getPageId());
  }

  /**
   * Collects various information about the used modules, legacy support,
   * and the actual page content (json-tree with module formValues and stuff)
   *
   * @param INode $rootNode
   */
  protected function prepareContent(INode $rootNode)
  {
    $websiteId = $this->getWebsiteId();
    $pageId = $this->getPageId();
    $creatorVisitor = $this->createCreatorVisitor();
    $rootNode->accept($creatorVisitor);
    $this->setValues($creatorVisitor, $websiteId, $pageId);
  }

  /**
   * Renders the CSS and caches the output of it.
   *
   * @param INode $rootNode
   *
   * @throws \Cms\Exception
   */
  protected function prepareCssCache(INode $rootNode)
  {
    $websiteId = $this->getWebsiteId();
    $pageId = $this->getPageId();
    $pathToWebRoot = '../../';
    $renderContext = $this->createRenderContext($websiteId, $pageId, $this->getStructure(), $pathToWebRoot);
    $cssVisitor = $this->createCSSVisitor($renderContext);

    ob_start();
    try {
      $rootNode->accept($cssVisitor);
    } catch (\Exception $e) {
      ob_end_clean();
      throw new CmsException(2210, __METHOD__, __LINE__, array(
        'website.id' => $websiteId,
        'page.id' => $pageId,
        'exception.message' => $e->getMessage(),
        'exception.code' => $e->getCode(),
        'exception.file' => $e->getFile(),
        'exception.line' => $e->getLine(),
      ), $e);
    }
    $cssCacheString = ob_get_clean();

    $this->result->setCssCacheValue($cssCacheString);
  }

  /**
   * Renders the HTML and buffers the output
   *
   * @param INode $rootNode
   *
   * @throws \Cms\Exception
   */
  protected function prepareHtmlCache(INode $rootNode)
  {
    $websiteId = $this->getWebsiteId();
    $pageId = $this->getPageId();

    // path to
    $pageDepth = $this->getStructure()->getPageDepth($pageId);
    $pathToWebRoot = str_repeat('../', $pageDepth);

    $renderContext = $this->createRenderContext($websiteId, $pageId, $this->getStructure(), $pathToWebRoot);
    $htmlVisitor = $this->createHtmlVisitor($renderContext);

    ob_start();
    try {
      $rootNode->accept($htmlVisitor);
    } catch (\Exception $e) {
      ob_end_clean();
      throw new CmsException(2211, __METHOD__, __LINE__, array(
        'website.id' => $websiteId,
        'page.id' => $pageId,
        'exception.message' => $e->getMessage(),
        'exception.code' => $e->getCode(),
        'exception.file' => $e->getFile(),
        'exception.line' => $e->getLine(),
        ), $e);
    }
    /** @noinspection PhpUnusedLocalVariableInspection */
    $htmlCacheString = ob_get_clean();
    // use $htmlCacheString for static pages
    $this->result->setHtmlCacheValue($htmlCacheString);
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param CreatorVisitor $visitor
   * @param string         $websiteId
   * @param string         $pageId
   */
  protected function setValues(CreatorVisitor $visitor, $websiteId, $pageId)
  {
    $context = $this->getCreatorContext();
    $this->result->setUsedModuleIds($visitor->getUsedModuleIds());
    $this->result->setLegacySupport((bool)$visitor->legacySupportActivated());
    $this->result->setPageContent($visitor->getContent());
    $this->result->setPageGlobal($context->getPageGlobal($websiteId, $pageId));
    $this->result->setPageMeta($context->getPageMeta($websiteId, $pageId));
    $this->result->setPageAttributes($context->getPageAttributes($websiteId, $pageId));
    $this->addUsedMediaAndAlbumIdsToResult();
  }

  /**
   * @return INode
   */
  protected function createRootNode()
  {
    $nodeTree = $this->createNodeTree(
        $this->getWebsiteId(),
        $this->getPageId()
    );
    return $nodeTree->getRootNode();
  }

  /**
   * @param string $websiteId
   * @param string $pageId
   *
   * @return NodeTree
   */
  protected function createNodeTree($websiteId, $pageId)
  {
    $infoStorage = $this->getCreatorContext()->getModuleInfoStorage($websiteId);
    $content = $this->getCreatorContext()->getPageContent($websiteId, $pageId);
    $nodeFactory = new NodeFactory($infoStorage);
    return new NodeTree($content, $nodeFactory);
  }

  /**
   *
   * @return CreatorVisitor
   */
  protected function createCreatorVisitor()
  {
    return new CreatorVisitor();
  }

  /**
   * @param \Render\RenderContext $renderContext
   *
   * @return CssOnlyVisitor
   */
  protected function createCSSVisitor($renderContext)
  {
    return new CssOnlyVisitor($renderContext);
  }

  /**
   * @param \Render\RenderContext $renderContext
   *
   * @return HtmlVisitor
   */
  protected function createHtmlVisitor($renderContext)
  {
    return new HtmlVisitor($renderContext);
  }

  /**
   * Render Context suitable for usage in the creator
   * (e.g. use Service as data backend, but generate URLs for usage in live page)
   *
   * @param string        $websiteId
   * @param string        $currentPageId
   * @param SiteStructure $structure
   *
   * @param               $relativePathToWebRoot
   *
   * @return \Render\RenderContext
   */
  protected function createRenderContext($websiteId, $currentPageId, $structure, $relativePathToWebRoot)
  {
    $websiteInfoStorage = $this->getCreatorContext()->getWebsiteInfoStorage($websiteId);

    $moduleInfoStorage = $this->getCreatorContext()->createCreatorModuleInfoStorage($websiteId, $relativePathToWebRoot);

    $navUrlHelper = new SiteStructurePageUrlHelper($structure, $currentPageId, $relativePathToWebRoot);
    $navigationInfoStorage = $this->getCreatorContext()->getNavigationInfoStorage(
        $websiteId,
        $currentPageId,
        $navUrlHelper
    );

    $colorInfoStorage = $this->getCreatorContext()->getColorInfoStorage($websiteId);

    // media context
    $mediaContext = new MediaContext($this->getCreatorContext()->createMediaInfoStorage(
        $websiteId,
        $this->createInterceptorMediaUrlHelper($relativePathToWebRoot)
    ), $this->getCreatorContext()->getImageToolFactory());

    $interfaceLocaleCode = 'en_EN';
    $renderMode = RenderContext::RENDER_MODE_CREATOR;
    $renderType = RenderContext::RENDER_TYPE_PAGE;
    $resolutions = $this->getCreatorContext()->getResolutions($websiteId);

    $renderContext = new RenderContext(
        $websiteInfoStorage,
        $moduleInfoStorage,
        $navigationInfoStorage,
        $colorInfoStorage,
        $mediaContext,
        $interfaceLocaleCode,
        $renderMode,
        $renderType,
        $resolutions
    );
    // TODO: add cache here?

    // init legacy if required
    if ($this->result->getLegacySupport()) {
      LegacyRenderContext::init($renderContext, $websiteId, $currentPageId);
    }

    return $renderContext;

  }

  /**
   * @param $relativePathToWebRoot
   *
   * @return InterceptorCDNMediaUrlHelper|CDNMediaUrlHelper
   */
  protected function createInterceptorMediaUrlHelper($relativePathToWebRoot)
  {
    $liveCdnUrl = $relativePathToWebRoot . 'files/media/cdn.php';
    return new InterceptorCDNMediaUrlHelper(
        new NoneValidationHelper(),
        $liveCdnUrl,
        InterceptorCDNMediaUrlHelper::REQUEST_PARAMETER,
        array($this, 'addMediaCall')
    );
  }

  /**
   * Logs the mediaUrl calls
   *
   * @param string $key
   * @param array  $call
   *
   * NOTE: This is only public because PHP does not allow to call protected functions
   *       from within closures (or call_use_func). It *should* be only used in #createInterceptorMediaUrlHelper!
   */
  public function addMediaCall($key, $call)
  {
    $this->mediaUrlCalls[$key] = $call;
  }

  /**
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContext()
  {
    return $this->creatorContext;
  }

  /**
   * @return SiteStructure
   */
  protected function getStructure()
  {
    return $this->structure;
  }

  /**
   * @param array $info
   *
   * @throws \Exception
   */
  protected function validateAndInitInfo($info)
  {
    if (!isset($info['id']) || empty($info['id'])) {
      throw new \Exception('no page id given');
    }
    $this->pageId = $info['id'];

    if (!isset($info['structure']) || !is_array($info['structure'])) {
      throw new \Exception('no structure data given');
    }
    $this->structure = new SiteStructure($this->getCreatorContext());
    $this->structure->initFromArray($info['structure']);
  }

  /**
   * find used module and album ids
   */
  private function addUsedMediaAndAlbumIdsToResult()
  {
    $this->addUsedMediaAndAlbumIdsFromData($this->result->getPageContent());
    $this->addUsedMediaAndAlbumIdsFromData($this->result->getPageMeta());
    $this->addUsedMediaAndAlbumIdsFromData($this->result->getPageAttributes());
  }

  /**
   * find used module and album ids in data and set the ids to the result
   *
   * @param mixed $value
   */
  private function addUsedMediaAndAlbumIdsFromData($value)
  {
    $mediaHelperResult = $this->getMediaHelper()->findMediaAndAlbumIds($value);
    $this->result->addUsedMediaIds($mediaHelperResult->getMediaIds());
    $this->result->addUsedAlbumIds($mediaHelperResult->getAlbumsIds());
  }

  protected function getPageId()
  {
    return $this->pageId;
  }

  /**
   * @return MediaHelper
   */
  protected function getMediaHelper()
  {
    if (!isset($this->mediaHelper)) {
      $this->mediaHelper = new MediaHelper();
    }
    return $this->mediaHelper;
  }
}
