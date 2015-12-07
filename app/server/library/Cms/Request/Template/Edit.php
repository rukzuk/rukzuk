<?php
namespace Cms\Request\Template;

/**
 * Request object for Template edit
 *
 * @package      Cms
 * @subpackage   Request\Template
 */

class Edit extends EditMeta
{
  /**
   * @var string
   */
  private $content;

  protected function setValues()
  {
    parent::setValues();
    $this->setContent($this->getRequestParam('content'));
  }

  /**
   * @param string $content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }
  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }
}
