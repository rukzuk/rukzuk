<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * Validate optin request
 *
 * @package      Cms
 * @subpackage   Request
 */
class ValidateOptin extends Base
{
  /**
   * @var string
   */
  private $code;
  
  /**
   * @param mixed $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }
  /**
   * @return mixed
   */
  public function getCode()
  {
    return $this->code;
  }

  protected function setValues()
  {
    $this->setCode($this->getRequestParam('code'));
  }
}
