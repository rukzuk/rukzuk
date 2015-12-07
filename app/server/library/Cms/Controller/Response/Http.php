<?php
namespace Cms\Controller\Response;

/**
 * Http-Response fuer CMS mit File-Streaming
 *
 * @package    Cms
 * @subpackage Controller
 */
class Http extends \Zend_Controller_Response_Http
{
  const CHUNK_SIZE = 1048576;

  /**
   * @var null|callable
   */
  private $outputBodyCallback = null;

  /**
   * @param callable $outputBodyCallback
   */
  public function setOutputBodyCallback($outputBodyCallback)
  {
    $this->outputBodyCallback = $outputBodyCallback;
  }

  /**
   * reset body output values
   *
   * @param null|string $name
   *
   * @return void
   */
  public function clearBody($name = null)
  {
    $this->outputBodyCallback = null;
    return parent::clearBody($name);
  }

  /**
   * output body
   *
   * @return void
   */
  public function outputBody()
  {
    $callback = $this->outputBodyCallback;
    if (is_callable($callback)) {
      $callback($this, self::CHUNK_SIZE);
    } else {
      parent::outputBody();
    }
  }

  /**
   * Flush the output buffer
   */
  public function flushOutput()
  {
    ob_flush();
    flush();
  }
}
