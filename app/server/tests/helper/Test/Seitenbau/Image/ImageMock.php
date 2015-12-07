<?php


namespace Test\Seitenbau\Image;


use Seitenbau\Image\Image;

class ImageMock implements Image
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
   * @param array $config
   */
  public function __construct(array $config = array())
  {
    $this->methodCalls[] = array(__METHOD__, $config);
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
   * @param string $imageFile
   */
  public function setFile($imageFile)
  {
    $this->methodCalls[] = array(__METHOD__, $imageFile);
  }

  /**
   * @return bool
   */
  public function load()
  {
    $this->methodCalls[] = array(__METHOD__);
    return true;
  }

  /**
   * @param string $destinationFile
   * @param null|string   $imageType
   *
   * @return bool
   */
  public function save($destinationFile, $imageType = null)
  {
    $this->methodCalls[] = array(__METHOD__, $destinationFile, $imageType);
    $result = file_put_contents($destinationFile,
      var_export($this->methodCalls, true));
    return ($result !== false);
  }

  /**
   * @param null|string $imageType
   *
   * @return bool
   */
  public function send($imageType = null)
  {
    $this->methodCalls[] = array(__METHOD__, $imageType);
    var_export($this->methodCalls);
    return true;
  }

  /**
   * @return bool
   */
  public function close()
  {
    $this->methodCalls[] = array(__METHOD__);
    return true;
  }

  /**
   * @return array
   */
  public function getImageSize()
  {
    $this->methodCalls[] = array(__METHOD__);
    return array('width' => 400, 'height' => 350);
  }

  /**
   * @return array
   */
  public function getCurImageSize()
  {
    $this->methodCalls[] = array(__METHOD__);
    return array('width' => 300, 'height' => 250);
  }

  /**
   * @return array
   */
  public function getImageInfo()
  {
    $this->methodCalls[] = array(__METHOD__);
    return array(
      'width'   => 300,
      'height'  => 250,
      'type'    => self::TYPE_JPG,
    );
  }

  /**
   * @param array $action
   *
   * @return bool
   */
  public function resize($action)
  {
    $this->methodCalls[] = array(__METHOD__, $action);
    return true;
  }

  /**
   * @param array $action
   *
   * @return bool
   */
  public function crop($action)
  {
    $this->methodCalls[] = array(__METHOD__, $action);
    return true;
  }

  /**
   * @param array $action
   *
   * @return bool
   */
  public function quality($action)
  {
    $this->methodCalls[] = array(__METHOD__, $action);
    return true;
  }

  /**
   * @param int $quality
   *
   * @return bool
   */
  public function setQuality($quality)
  {
    $this->methodCalls[] = array(__METHOD__, $quality);
    return true;
  }

  /**
   * @param $filePath
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
    $this->methodCalls[] = array(__METHOD__);
    return array('width' => 400, 'height' => 350);
  }
}