<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Cms\Creator\CreatorContext;

class SiteStructure
{
  /**
   * @var \Cms\Creator\CreatorContext
   */
  private $creatorContext;

  /**
   * @var array
   */
  private $reservedDirectories;

  /**
   * @var null|string
   */
  private $homePageId = null;

  /**
   * @var array
   */
  private $pageUrls = array();

  /**
   * @var array
   */
  private $pageStructure = array();

  /**
   * @var array
   */
  private $cleaningNameRegExpSearch = array();
  /**
   * @var array
   */
  private $cleaningNameRegExpReplace = array();


  /**
   * @param CreatorContext $creatorContext
   */
  public function __construct(CreatorContext $creatorContext)
  {
    $this->creatorContext = $creatorContext;
    $this->initCleaningNameRegExp();
  }

  private function reset()
  {
    $this->reservedDirectories = array();
    $this->homePageId = null;
    $this->pageUrls = array();
    $this->pageStructure = array();
  }

  /**
   * @param string $websiteId
   * @param array  $reservedDirectories
   */
  public function initByWebsiteId(
      $websiteId,
      array $reservedDirectories = array()
  ) {
    $this->reset();
    $this->reservedDirectories = $reservedDirectories;
    $navigation = $this->getCreatorContext()->getNavigation($websiteId);
    $this->initHomePageId($navigation);
    $this->initSiteStructureRecursive($navigation);
  }

  /**
   * @param array $data
   */
  public function initFromArray(array $data)
  {
    $this->reset();
    if (isset($data['reserveddirectories']) && is_array($data['reserveddirectories'])) {
      $this->reservedDirectories = $data['reserveddirectories'];
    }
    if (isset($data['homepageid']) && is_string($data['homepageid'])) {
      $this->homePageId = $data['homepageid'];
    }
    if (isset($data['pageurls']) && is_array($data['pageurls'])) {
      $this->pageUrls = $data['pageurls'];
    }
    if (isset($data['pagestructure']) && is_array($data['pagestructure'])) {
      $this->pageStructure = $data['pagestructure'];
    }
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'reserveddirectories' => $this->reservedDirectories,
      'homepageid' => $this->homePageId,
      'pageurls' => $this->pageUrls,
      'pagestructure' => $this->pageStructure,
    );
  }

  /**
   * @param string $pageId
   * @param bool $forceFileName - also outputs the index.php/index.html if true
   * @return null|string
   */
  public function getPageUrl($pageId, $forceFileName = true)
  {
    if (!isset($this->pageUrls[$pageId])) {
      return null;
    }

    $path = $this->pageUrls[$pageId]['path'];
    $fileName = $this->pageUrls[$pageId]['fileName'];

    // path
    $url =  $path ? $path . '/' : '';

    // file name
    if ($forceFileName || !($fileName === 'index.php' || $fileName === 'index.html')) {
      $url .= $fileName;
    }

    return $url;
  }

  /**
   * @param string $pageId
   *
   * @return int
   */
  public function getPageDepth($pageId)
  {
    if (!isset($this->pageUrls[$pageId])) {
      return 0;
    }
    return $this->pageUrls[$pageId]['depth'];
  }

  /**
   * @param $navigation
   */
  protected function initHomePageId($navigation)
  {
    $node = reset($navigation);
    if (!is_array($node)) {
      return;
    }
    if (!isset($node['id'])) {
      return;
    }
    $this->homePageId = $node['id'];
  }

  /**
   * @param array  $navigation
   * @param string $basePath
   * @param int    $depth
   */
  protected function initSiteStructureRecursive(array $navigation, $basePath = '', $depth = 1)
  {
    foreach ($navigation as $node) {
      if (!isset($node['id'])) {
        continue;
      }
      $pageId = $node['id'];
      if ($pageId == $this->homePageId) {
        $nodePath = '';
        $pageDepth = 0;
      } else {
        $nodePath = $this->getCleanStructurePathName($basePath, $node);
        $pageDepth = $depth;
      }
      $this->pageStructure[$nodePath] = $pageId;
      $this->pageUrls[$pageId] = array(
        'path'      => $nodePath,
        'fileName'  => 'index.php',
        'depth'     => $pageDepth,
      );
      if (isset($node['children']) && is_array($node['children'])) {
        $this->initSiteStructureRecursive($node['children'], $nodePath, $pageDepth+1);
      }
    }
  }

  /**
   * @param string  $basePath
   * @param array   $node
   *
   * @throws \Exception
   * @return string
   */
  protected function getCleanStructurePathName($basePath, array $node)
  {
    if (!empty($basePath)) {
      $basePath .= '/';
    }
    $pageName = $this->getNameFromNavigationNode($node);
    $cleanName = preg_replace(
        $this->cleaningNameRegExpSearch,
        $this->cleaningNameRegExpReplace,
        $pageName
    );

    $newPath = $basePath.$cleanName;
    $i=1;
    while ($this->structurePathNameExists($newPath, $node['id'])) {
      $newPath = $basePath.$cleanName.'-'.($i++);
      // security check
      if ($i >= 99999) {
        throw new \Exception(sprintf(
            "Security error (max iteration) at generate structure name '%s'",
            $newPath
        ));
      }
    }
    return $newPath;
  }

  /**
   * @param $newPath
   * @param $pageId
   *
   * @return bool
   */
  protected function structurePathNameExists($newPath, $pageId)
  {
    if (isset($this->pageStructure[$newPath]) &&
      $pageId != $this->pageStructure[$newPath]) {
      return true;
    }
    if (in_array($newPath, $this->reservedDirectories)) {
      return true;
    }
    return false;
  }

  /**
   * @param $node
   *
   * @return string
   */
  protected function getNameFromNavigationNode($node)
  {
    if (isset($node['name']) && !empty($node['name'])) {
      return $node['name'];
    } else {
      return 'noname';
    }
  }

  /**
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContext()
  {
    return $this->creatorContext;
  }

  protected function initCleaningNameRegExp()
  {
    $reqExp = array(
      '/[\x{00d6}]/u'   => 'Oe',  // 'Oe'
      '/[\x{00f6}]/u'   => 'oe',  // 'oe'
      '/[\x{00c4}]/u'   => 'Ae',  // 'Ae'
      '/[\x{00e4}]/u'   => 'ae',  // 'ae'
      '/[\x{00dc}]/u'   => 'Ue',  // 'Ue'
      '/[\x{00fc}]/u'   => 'ue',  // 'ue'
      '/[\x{00df}]/u'   => 'ss',  // 'ss'
      '/[^0-9a-z_\-]/i' => '-',   // '-'
      '/[-]+/i'         => '-',   // '-'
    );
    $this->cleaningNameRegExpSearch = array_keys($reqExp);
    $this->cleaningNameRegExpReplace = array_values($reqExp);
  }
}
