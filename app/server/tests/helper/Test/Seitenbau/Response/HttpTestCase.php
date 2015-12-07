<?php
namespace Test\Seitenbau\Response;


/**
 * Http-TestCase-Response fuer CMS mit File-Streaming (fuer unit Tests)
 *
 * @package    Cms
 * @subpackage Controller
 */
class HttpTestCase extends \Zend_Controller_Response_HttpTestCase
{
  const CHUNK_SIZE = 1048576;

  /**
   * @var null|callable
   */
  private $outputBodyCallback = null;

  /**
   * @var int
   */
  private $testCallbackCallCount = 0;

  private $testCallbackOutput = array();

  /**
   * @return null|string
   */
  public function getTestCallbackCallCount()
  {
    return $this->testCallbackCallCount;
  }

  /**
   * @return array
   */
  public function getTestCallbackOutput()
  {
    return $this->testCallbackOutput;
  }

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
      $this->testCallbackCallCount++;
      ob_start();
      $callback($this, self::CHUNK_SIZE);
      $this->testCallbackOutput[] = ob_get_clean();
    } else {
      return parent::outputBody();
    }
  }

  /**
   * Flush the output buffer
   */
  public function flushOutput()
  {
    // do not flush at testing
  }
}
