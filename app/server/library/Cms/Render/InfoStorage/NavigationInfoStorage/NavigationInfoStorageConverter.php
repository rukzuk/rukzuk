<?php
namespace Cms\Render\InfoStorage\NavigationInfoStorage;

use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;

/**
 * Class NavigationInfoStorageConverter
 * @package Cms\Render\InfoStorage\NavigationInfoStorage
 */
class NavigationInfoStorageConverter
{

  /**
   * @var \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage
   */
  private $navStorage;

  /**
   * @param INavigationInfoStorage $navStorage
   */
  public function __construct(INavigationInfoStorage $navStorage)
  {
    $this->navStorage = $navStorage;
  }

  /**
   * @param string $pageId
   *
   * @return array
   */
  public function extractPageAttributes($pageId)
  {
    try {
      return $this->navStorage->getPageAttributes($pageId);
    } catch (\Exception $_e) {
      return array();
    }
  }

  /**
   *
   * @return array
   */
  public function extractNavigationArray()
  {

    $roots = $this->navStorage->getRootChildrenIds();
    return $this->buildNavArray($roots);
  }

  /**
   * @param $ids
   *
   * @return array
   */
  protected function buildNavArray($ids)
  {
    $nodes = array();

    foreach ($ids as $id) {
      $item = $this->navStorage->getItem($id);

      $data = array(
        'id' => $id,
        'pageType' => $item->getPageType(),
        'name' => $item->getTitle(),
        'description' => $item->getDescription(),
        'date' => $item->getDate(),
        'inNavigation' => $item->showInNavigation(),
        'navigationTitle' => $item->getNavigationTitle(),
        'mediaId' => $item->getMediaId(),
      );

      $children = $item->getChildrenIds();
      if (count($children) > 0) {
        $data['children'] = $this->buildNavArray($children);
      }

      $nodes[$id] = $data;
    }

    return $nodes;
  }
}
