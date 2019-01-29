<?php
namespace Cms\Request\Import;

use Cms\Request\Base;

/**
 * Request object for Import File
 *
 * @package      Cms
 * @subpackage   Request
 */
class File extends Base
{
  const DEFAULT_FILE_INPUT_NAME = 'file';
  const DEFAULT_EMPTY_WEBSITE_ID = '-';
  
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $uploadFilename;
  /**
   * @var string
   */
  private $fileInputname;
  /**
   * @var string
   */
  private $allowedType;

  private $chunks;

  /**
   * @param string $id
   */
  public function setWebsiteId($id)
  {
    $this->websiteId = $id;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  
  /**
   * @param string $name
   */
  public function setUploadFilename($name)
  {
    $this->uploadFilename = $name;
  }

  /**
   * @return string
   */
  public function getParam($param)
  {
    return $_REQUEST[$param];
  }


  /**
   * @return string
   */
  public function getUploadFilename()
  {
    return $this->uploadFilename;
  }


  /**
   * @param string $name
   */
  public function setFileInputname($name)
  {
    $this->fileInputname = $name;
  }
  /**
   * @return string
   */
  public function getFileInputname()
  {
    return $this->fileInputname;
  }
  
  /**
   * @param string $allowedType
   */
  public function setAllowedType($allowedType)
  {
    $this->allowedType = $allowedType;
  }
  /**
   * @return string
   */
  public function getAllowedType()
  {
    return $this->allowedType;
  }

  protected function setValues()
  {
    if ($this->getRequestParam('websiteid') === null ||
        $this->getRequestParam('websiteid') === '') {
      $this->setWebsiteId(self::DEFAULT_EMPTY_WEBSITE_ID);
    } else {
      $this->setWebsiteId($this->getRequestParam('websiteid'));
    }

    if ($this->getRequestParam('name') !== null) {
      $this->setFileInputname($this->getRequestParam('name'));
    } else {
      $this->setFileInputname(self::DEFAULT_FILE_INPUT_NAME);
    }
    if (count($_FILES) > 0) {
      $this->setUploadFilename($_FILES[$this->getFileInputname()]['name']);
    }

    if ($this->getRequestParam('allowedtype') !== null) {
      $this->setAllowedType($this->getRequestParam('allowedtype'));
    }


  }
}
