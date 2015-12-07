<?php


namespace Test\Render\ImageToolFactory;


use Render\ImageToolFactory\ImageTool;

class ImageToolMock extends ImageTool
{
  /**
   * @var array
   */
  private $methodCalls = array();

  /**
   * @var bool
   */
  private $isImageFileFlag = true;

  /**
   *
   */
  public function __construct()
  {
    $this->methodCalls[] = array(__METHOD__);
  }

  /**
   * @return array
   */
  public function getMethodCalls()
  {
    return $this->methodCalls;
  }

  /**
   * @param $isImageFileFlag
   */
  public function setIsImageFile($isImageFileFlag)
  {
    $this->isImageFileFlag = $isImageFileFlag;
  }

  /**
   * Returns TRUE if file is an image; FALSE otherwise
   *
   * @param string $filePath
   *
   * @return bool
   */
  public function isImageFile($filePath)
  {
    $this->methodCalls[] = array(__METHOD__, $filePath);
    return $this->isImageFileFlag;
  }

  /**
   * @param string $filePath
   *
   * @return array
   */
  public function getDimensionFromFile($filePath)
  {
    $this->methodCalls[] = array(__METHOD__, $filePath);
    return array('width' => 400, 'height' => 350);
  }

  /**
   * Opens the image given by $filePath
   *
   * @param string $filePath
   */
  public function open($filePath)
  {
    $this->methodCalls[] = array(__METHOD__, $filePath);
  }

  /**
   * Close the loaded image
   */
  public function close()
  {
    $this->methodCalls[] = array(__METHOD__);
  }

  /**
   * Write images to file
   *
   * @param string $filePath
   *
   * @return bool
   */
  public function save($filePath)
  {
    $this->methodCalls[] = array(__METHOD__, $filePath);
  }

  /**
   * Output image to browser
   */
  public function send()
  {
    $this->methodCalls[] = array(__METHOD__);
  }

  /**
   * @return array
   */
  public function getDimension()
  {
    $this->methodCalls[] = array(__METHOD__);
    return array('width' => 200, 'height' => 150);
  }

  /**
   * Modify the image by given operations
   *
   * @param array $operations
   */
  public function modify(array $operations = array())
  {
    $this->methodCalls[] = array(__METHOD__, $operations);
  }
}