<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * Request object for Media Upload
 *
 * @package      Cms
 * @subpackage   Request
 */
class Upload extends Base
{
  const DEFAULT_UPLOAD_INPUT_NAME = 'file';
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $id;
  /**
   * @var string
   */
  private $albumId;
  /**
   * @var string
   */
  private $name;
  /**
   * @var string
   */
  private $fileInputname;
  /**
   * @var string
   */
  private $uploadFilename;
  /**
   * @var integer
   */
  private $lastModification;
  /**
   * @var integer
   */
  private $uploadFilesize;
  /**
   * @var string
   */
  private $uploadFileExtension;

  protected function initParamsFromRequest(\Zend_Controller_Request_Abstract $request = null)
  {
    parent::initParamsFromRequest($request);
    
    if ($request != null && $request->getParam('params') === null) {
      $this->setRequestParams(array(
        'runId'         => $request->getParam('runId'),
        'websiteId'     => $request->getParam('websiteId'),
        'id'            => $request->getParam('id'),
        'albumId'       => $request->getParam('albumId'),
        'name'          => $request->getParam('name'),
        'fileInputname' => $this->getFileInoutNameFromRequest($request),
      ));
    }
  }
  
  protected function getFileInoutNameFromRequest(\Zend_Controller_Request_Abstract $request)
  {
    return$request->getParam(
        'fileInputname',
        \Cms\Request\Media\Upload::DEFAULT_UPLOAD_INPUT_NAME
    );
  }
 
  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    if ($this->getRequestParam('id') !== '') {
      $this->setId($this->getRequestParam('id'));
    }
    if ($this->getRequestParam('albumid') !== '') {
      $this->setAlbumId($this->getRequestParam('albumid'));
    }
    $this->setName($this->getRequestParam('name'));
    
    if ($this->getRequestParam('fileinputname') !== null) {
      $this->setFileInputname($this->getRequestParam('fileinputname'));
    } else {
      $this->setFileInputname(self::DEFAULT_UPLOAD_INPUT_NAME);
    }

    if (count($_FILES) > 0) {
      $uploadFileExtension = $this->getExtensionFromFilename(
          $_FILES[$this->getFileInputname()]['name']
      );
      $this->setUploadFilename($_FILES[$this->getFileInputname()]['name']);
      $this->setLastModification(null);
      $this->setUploadFileExtension($uploadFileExtension);
      $this->setUploadFilesize((int)$_FILES[$this->getFileInputname()]['size']);
    }
  }
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
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param string $id
   */
  public function setAlbumId($id)
  {
    $this->albumId = $id;
  }
  /**
   * @return string
   */
  public function getAlbumId()
  {
    return $this->albumId;
  }
  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
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
  public function getUploadFilename()
  {
    return $this->uploadFilename;
  }
  /**
   * @param integer $time
   */
  public function setLastModification($time)
  {
    $this->lastModification = $time;
  }
  /**
   * @return integer
   */
  public function getLastModification()
  {
    return $this->lastModification;
  }
  /**
   * @param integer $size
   */
  public function setUploadFilesize($size)
  {
    $this->uploadFilesize = $size;
  }
  /**
   * @return integer
   */
  public function getUploadFilesize()
  {
    return $this->uploadFilesize;
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
   * @param string $extension
   */
  public function setUploadFileExtension($extension)
  {
    $this->uploadFileExtension = $extension;
  }
  /**
   * @return string
   */
  public function getUploadFileExtension()
  {
    return $this->uploadFileExtension;
  }
  /**
   * @param  string $filename
   * @return string
   */
  private function getExtensionFromFilename($filename)
  {
    return substr(strrchr($filename, '.'), 1);
  }
}
