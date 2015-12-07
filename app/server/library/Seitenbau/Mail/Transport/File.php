<?php
namespace Seitenbau\Mail\Transport;

/**
 * @package      Seitenbau
 * @subpackage   Mail
 */

class File extends \Zend_Mail_Transport_File
{

  /**
   * Sets options
   *
   * @param  array $options
   * @return void
   */
  public function setOptions(array $options)
  {
    // Verzeichnis erstellen, wenn nicht vorhanden ist
    if (isset($options['path']) && !is_dir($options['path'])) {
      $mkDirSuccess = mkdir($options['path']);
      if (!$mkDirSuccess) {
        throw new \Zend_Mail_Transport_Exception(sprintf(
            'Can not create directory "%s"',
            $options['path']
        ));
      }
    }
    
    parent::setOptions($options);
  }
}
