<?php
namespace Cms\Response;

use Cms\Exception;

/**
 * Abbildung des Response Error
 *
 * @package      Cms
 * @subpackage   Response
 *
 * @SWG\Model(id="Response/Error")
 */
class Error
{
  /**
   * @var integer
   * @SWG\Property(required=true)
   */
  public $code;

  /**
   * @var integer
   * @SWG\Property(required=true)
   */
  public $logid;

  /**
   * @var array
   * @SWG\Property(required=true)
   */
  public $param;

  /**
   * @var string
   * @SWG\Property(required=true)
   */
  public $text;

  public function __construct($exception = null, $logId = null)
  {
    if ($exception != null) {
      $this->setCode($exception->getCode());

      $logId = (method_exists($exception, 'getLogId'))
             ? $exception->getLogId()
             : $logId;
      $this->setLogId($logId);

      $param = (method_exists($exception, 'getData'))
             ? $exception->getData()
             : null;
      $this->setParam($param);
      
      $this->setText($exception->getMessage());
    }
  }

  public function setLogId($logid)
  {
    $this->logid = $logid;
  }

  public function getLogId()
  {
    return $this->logid;
  }

  public function setCode($code)
  {
      $this->code = (int)$code;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getParam()
  {
    return $this->param;
  }

  public function setParam($param)
  {
    if ($this->getCode() > 1) {
      $this->param = $param;
    }
  }

  public function getText()
  {
    return $this->text;
  }

  public function setText($text)
  {
    $this->text = $text;
  }
}
