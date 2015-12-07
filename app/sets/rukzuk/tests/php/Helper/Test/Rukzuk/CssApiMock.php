<?php
namespace Test\Rukzuk;

require_once(TEST_PATH.'/Helper/Test/Rukzuk/MediaItemMock.php');

use \Render\APIs\APIv1\Navigation;
use \Render\InfoStorage\NavigationInfoStorage\ArrayBasedNavigationInfoStorage;
use \Test\Rukzuk\MediaItemMock;
use \Render\APIs\APIv1\MediaItemNotFoundException;
use \Render\APIs\APIv1\WebsiteSettingsNotFound;

class CssApiMock
{
  protected $module;
  protected $moduleInfo;
  protected $unit;
  protected $mode;
  protected $colors;
  protected $navItems;
  protected $currentPageId;
  protected $isTemplate;
  protected $mediaItems;
  protected $unitNodes;
  protected $modules;
  protected $testCase;
  protected $websiteSettings;
  protected $interfaceLocale;
  protected $locale;

  public function __construct ($conf = null, \PHPUnit_Framework_TestCase $testCase = null)
  {
    $this->module = $this->getValue('module', $conf);
    $this->moduleInfo = $this->getValue('moduleInfo', $conf);
    $this->unit = $this->getValue('unit', $conf);
    $this->mode = $this->getValue('mode', $conf, 'live');
    $this->colors = $this->getValue('colors', $conf, null);
    $this->navItems = $this->getValue('navigation', $conf, array());
    $this->currentPageId = $this->getValue('currentPageId', $conf, null);
    $this->isTemplate = $this->getValue('isTemplate', $conf, false);
    $this->mediaItems = $this->getValue('mediaItems', $conf, array());
    $this->websiteSettings = $this->getValue('websiteSettings', $conf, array());
    $this->interfaceLocale = $this->getValue('interfaceLocale', $conf, 'en_US');
    $this->locale = $this->getValue('locale', $conf, 'en_US');

    // initialize the set of units representing a unit tree
    $this->initUnitNodes($this->getValue('units', $conf, array()));

    // initialize the set of module data
    $this->initModules($this->getValue('modules', $conf, array()));

    $this->testCase = $testCase;
  }

  public function isEditMode()
  {
    return $this->mode === 'edit';
  }

  public function isLiveMode()
  {
    return false; // TODO: should be $this->mode === 'live';
  }

  public function isPreviewMode()
  {
    return $this->mode === 'preview';
  }

  public function getParentUnit($unit = null)
  {
    $node = $this->getUnitNode($unit);
    if (is_object($node)) {
      $parentNode = $this->getUnitNode($node->parentId);
      if (is_object($parentNode)) {
        return $parentNode->unit;
      }
    }
    return null;
  }

  public function getChildren($unit = null)
  {
    $children = array();
    $node = $this->getUnitNode($unit);
    if (is_object($node)) {
      foreach ($node->childrenIds as $childUnitId) {
        $childNode = $this->getUnitNode($childUnitId);
        if (is_object($childNode)) {
          $children[] = $childNode->unit;
        }
      }
    }
    return $children;
  }

  public function getUnitById($id)
  {
    $node = $this->getUnitNode($id);
    if (is_object($node)) {
      return $node->unit;
    }
    return $this->unit;
  }

  public function getFormValue($unit, $key, $fallbackValue = null)
  {
    $formValues = $unit->getFormValues();
    if (!(is_array($formValues) && array_key_exists($key, $formValues))) {
      return $fallbackValue;
    }

    return $formValues[$key];
  }

  public function getResolutions()
  {
    return array(
      'enabled' => TRUE,
      'data' => array(
        array('id' => 'res1', 'name' => 'Res1', 'width' => 768),
        array('id' => 'res2', 'name' => 'Res2', 'width' => 480),
        array('id' => 'res3', 'name' => 'Res3', 'width' => 320)
      )
    );
  }

  public function getInterfaceLanguage()
  {
    $locale = explode('_', $this->getInterfaceLocale());
    return $locale[0];
  }

  public function getInterfaceLocale()
  {
    return $this->interfaceLocale;
  }

  public function getLocale()
  {
    return $this->locale;
  }

  public function getColorById($colorId)
  {
    return $this->getValue($colorId, $this->colors, $colorId);
  }

  public function getModuleInfo($unit = null)
  {
    $node = $this->getUnitNode($unit);
    if (is_object($node)) {
      return $this->createModuleInfo($node->unit->getModuleId());
    }
    return $this->moduleInfo;
  }

  public function getNavigation()
  {
    list($navInfoStorage, $pageUrls) = $this->createInfoStorageDataFromDummyInput($this->navItems);

    return new \Render\APIs\APIv1\Navigation(
      new \Render\InfoStorage\NavigationInfoStorage\ArrayBasedNavigationInfoStorage(
        $navInfoStorage,
        $this->currentPageId,
        new \Render\PageUrlHelper\SimplePageUrlHelper($pageUrls, $this->currentPageId, '#cssUrl')
      )
    );
  }

  public function isPage()
  {
    return !$this->isTemplate;
  }

  public function isTemplate()
  {
    return $this->isTemplate;
  }

  public function getWebsiteSettings($websiteSettingsId)
  {
    if (isset($this->websiteSettings[$websiteSettingsId])
      || array_key_exists($websiteSettingsId, $this->websiteSettings))
    {
      return $this->websiteSettings[$websiteSettingsId];
    }
    throw new WebsiteSettingsNotFound();
  }

  //
  //
  // helper
  //
  //

  protected function getValue($key, $param, $defaultValue = null)
  {
    if (is_array($param) && array_key_exists($key, $param)) {
      return $param[$key];
    } else {
      return $defaultValue;
    }
  }

  private function createInfoStorageDataFromDummyInput($raw)
  {
    $result = array();
    $pages = array();
    foreach ($raw as $id => $rawItem) {
      $itemData = array_merge(array(
        'id' => $id,
        'name' => $id
      ), $rawItem);

      $pages[$id] = isset($rawItem['url']) ? $rawItem['url'] : '';

      if (isset($rawItem['children']) && is_array($rawItem['children'])) {
        list($itemData['children'], $childPages) = $this->createInfoStorageDataFromDummyInput($rawItem['children']);
        $pages = array_merge($childPages, $pages);
      }
      $result[] = $itemData;
    }
    return array($result, $pages);
  }

  private function initUnitNodes(array $units, $parentNode = null)
  {
    if (!is_array($this->unitNodes)) {
      $this->unitNodes = array();
    }

    $unitIds = array();

    foreach ($units as $key => $conf) {
      $id = is_string($conf) ? $conf : $this->getValue('id', $conf, $key);
      $moduleId = $this->getValue('moduleId', $conf, $id);
      $name = $this->getValue('name', $conf, $id);
      $formValues = $this->getValue('formValues', $conf, array());
      $ghostContainer = $this->getValue('ghostContainer', $conf, false);
      $templateUnitId = $this->getValue('templateUnitId', $conf, null);
      $children = $this->getValue('children', $conf, array());

      // create node
      $node = new \StdClass();
      $node->unit = new \Render\Unit($id, $moduleId, $name, $formValues, $ghostContainer, $templateUnitId);
      $node->parentId = is_object($parentNode) ? $parentNode->unit->getId() : null;
      $node->childrenIds = $this->initUnitNodes($children, $node);

      $unitIds[] = $id;
      $this->unitNodes[$id] = $node;
    }
    return $unitIds;
  }

  private function initModules(array $modules)
  {
    if (!is_array($this->modules)) {
      $this->modules = array();
    }

    foreach ($modules as $key => $module)
    {
      $id = $this->getValue('id', $module, $key);
      $this->modules[$key] = $module;
    }
  }

  protected function getUnitNode($unit)
  {
    $id = is_object($unit) ? $unit->getId() : $unit;
    if (is_string($id) && is_array($this->unitNodes) && array_key_exists($id, $this->unitNodes)) {
      return $this->unitNodes[$id];
    }
    return null;
  }

  protected function createModule($id)
  {
    $conf = $this->getModuleConfig($id);
    $ns = $this->getValue('ns', $conf, '\Rukzuk\Modules');
    $class = $this->getValue('class', $conf, $id);
    $className = $ns . '\\' . $class;

    if (!class_exists($className)) {
      throw new \Exception('Cannot create module "'.$id.'": Class "'.$className.'" is not defined');
    }

    return new $className();
  }

  protected function createModuleInfo($id)
  {
    $conf = $this->getModuleConfig($id);
    $assetPath = $this->getValue('assetPath', $conf, MODULE_PATH.'/'.$id.'/assets');
    $assetUrl = $this->getValue('assetUrl', $conf, $id.'/assets');
    $manifest = $this->getValue('manifest', $conf);
    $customData = $this->getValue('custom', $conf);
    $test = $this->testCase;

    $moduleInfoStrorage = $test->getMockBuilder('Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage')
      ->disableOriginalConstructor()->getMock();
    $moduleInfoStrorage->expects($test->any())->method('getModuleAssetPath')->will($test->returnValue($assetPath));
    $moduleInfoStrorage->expects($test->any())->method('getModuleAssetUrl')->will($test->returnValue($assetUrl));
    $moduleInfoStrorage->expects($test->any())->method('getModuleManifest')->will($test->returnValue($manifest));
    $moduleInfoStrorage->expects($test->any())->method('getModuleCustomData')->will($test->returnValue($customData));

    return new \Render\ModuleInfo($moduleInfoStrorage, $id);

  }

  protected function getModuleConfig($id)
  {
   if (is_array($this->modules) && array_key_exists($id, $this->modules)) {
     return $this->modules[$id];
   }
   return null;
  }

  public function getUnitCache($unit, $key)
  {
    return null;
  }

  public function setUnitCache($unit, $key, $value)
  {

  }

  public function getColorScheme()
  {
   return $this->colors;
  }

  public function getMediaItem($mediaId)
  {
    if (array_key_exists($mediaId, $this->mediaItems)) {
      return new MediaItemMock($this->mediaItems[$mediaId]);
    } else {
      throw new MediaItemNotFoundException();
    }
  }

}

