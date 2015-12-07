<?php
namespace Cms\Response\TemplateSnippet;

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
  public $templatesnippets = array();

  /**
   * @param array $templateSnippets
   */
  public function __construct(array $templateSnippets = array())
  {
    $this->setTemplateSnippets($templateSnippets);
  }

  /**
   * @return array
   */
  public function getTemplateSnippets()
  {
    return $this->templatesnippets;
  }
  
  /**
   * @param array $templateSnippets
   */
  protected function setTemplateSnippets(array $templateSnippets)
  {
    foreach ($templateSnippets as $templateSnippet) {
      $this->templatesnippets[] = new \Cms\Response\TemplateSnippet($templateSnippet);
    }
  }
}
