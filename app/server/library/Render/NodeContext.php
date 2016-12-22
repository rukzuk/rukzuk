<?php


namespace Render;

use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Render\InfoStorage\ContentInfoStorage\IContentInfoStorage;

class NodeContext
{
  /**
   * @var IModuleInfoStorage
   */
  private $moduleInfoStorage;

  /**
   * @var IContentInfoStorage
   */
  private $contentInfoStorage;

  /**
   * @var string
   */
  private $pageId;

  /**
   * @var string
   */
  private $templateId;

  /**
   * @param IModuleInfoStorage $moduleInfoStorage InfoStorage used to load the modules
   * @param IContentInfoStorage $contentInfoStorage InfoStorage used to load template or page contents
   * @param string $pageId
   * @param string $templateId
   */
  public function __construct(IModuleInfoStorage $moduleInfoStorage,
                              IContentInfoStorage $contentInfoStorage,
                              $pageId,
                              $templateId)
  {
    $this->moduleInfoStorage = $moduleInfoStorage;
    $this->contentInfoStorage = $contentInfoStorage;
    $this->pageId = $pageId;
    $this->templateId = $templateId;
  }

  /**
   * @return IModuleInfoStorage
   */
  public function getModuleInfoStorage()
  {
    return $this->moduleInfoStorage;
  }

  /**
   * @return IContentInfoStorage
   */
  public function getContentInfoStorage()
  {
    return $this->contentInfoStorage;
  }

  /**
   * @return string
   */
  public function getPageId()
  {
    return $this->pageId;
  }

  /**
   * @return string
   */
  public function getTemplateId()
  {
    return $this->templateId;
  }
}
