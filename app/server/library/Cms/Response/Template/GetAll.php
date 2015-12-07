<?php
namespace Cms\Response\Template;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer GetAll
 *
 * @package      Cms
 * @subpackage   Response
 */

class GetAll implements IsResponseData
{
  /**
   * @var array
   */
  public $templates = array();

  /**
   * @param array $templates
   */
  public function __construct(array $templates = array())
  {
    $this->setTemplates($templates);
  }

  /**
   * @return array
   */
  public function getTemplates()
  {
    return $this->templates;
  }
  
  /**
   * @param array $templates
   */
  protected function setTemplates(array $templates)
  {
    foreach ($templates as $template) {
      $this->templates[] = new \Cms\Response\Template($template);
    }
  }
}
