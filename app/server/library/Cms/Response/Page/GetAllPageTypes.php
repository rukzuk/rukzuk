<?php


namespace Cms\Response\Page;

use Cms\Response\IsResponseData;
use Cms\Response\PageType as PageTypeResponse;
use Cms\Data\PageType as DataPageType;

/**
 * @package      Cms\Response
 * @subpackage   Page
 */
class GetAllPageTypes implements IsResponseData
{
  public $pageTypes = array();

  /**
   * @param DataPageType[] $pageTypes
   */
  public function __construct($pageTypes = array())
  {
    $this->setPageTypes($pageTypes);
  }

  /**
   * @return PageTypeResponse[]
   */
  public function getPageTypes()
  {
    return $this->pageTypes;
  }

  /**
   * @param DataPageType[] $pageTypes
   */
  protected function setPageTypes(array $pageTypes)
  {
    foreach ($pageTypes as $pageType) {
      $this->pageTypes[] = new PageTypeResponse($pageType);
    }
  }
}
