<?php
namespace Seitenbau\Screenshot;

use Seitenbau\Screenshot as SBScreenshot;

/**
 * Screenshot Basis-Klasse
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

abstract class Base implements Screenshot
{
  /**
   * @var boolean
   */
  protected $isActiv = true;

  /**
   * @var string
   */
  protected $directory = '';

  /**
   * @var string
   */
  protected $filetype = 'jpg';

  /**
   * @var string
   */
  protected $lastError = null;

  /**
   * @var array
   */
  protected $config = array();

  public function __construct(array $config = array())
  {
    $this->checkRequiredOptions($config);

    if (\array_key_exists(SBScreenshot::ACTIVATE, $config)) {
      $this->setActiv($config[SBScreenshot::ACTIVATE]);
    }

    if (\array_key_exists(SBScreenshot::DIRECTORY, $config)) {
      $this->setDirectory($config[SBScreenshot::DIRECTORY]);
    }

    if (\array_key_exists(SBScreenshot::FILETYPE, $config)) {
      $this->setFiletype($config[SBScreenshot::FILETYPE]);
    }

    $this->config = $config;
    
    $this->setOptions();
  }

  public function isUsable()
  {
    // Check Screentool aktiv?
    if ($this->isActiv == false) {
      return false;
    }

    return true;
  }

  public function getDirectory()
  {
    return $this->directory;
  }

  public function getFiletype()
  {
    return $this->filetype;
  }

  /**
   * Mit dieser Funktion wird der Screenshot erstellt
   * Die Methode ruft die verschiedenen Implementationen auf
   *
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string $destinationFile
   * @return boolean
   */
  final public function shoot($shootId, $screenshotUrl, $destinationFile)
  {
    $this->clearLastError();

    if ($this->isUsable()) {
      return $this->shootImplementation($shootId, $screenshotUrl, $destinationFile);
    }

    $this->setLastError('screenshoot implementation not usable');
    return false;
  }

  /**
   * Hier wird in den Typen die Erstellung des Screenshots-Task implementiert
   *
   * @param string  $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string  $destinationFile
   * @return boolean
   */
  abstract protected function shootImplementation($shootId, $screenshotUrl, $destinationFile);

  /**
   * Setzt die Optionen, welche nur fuer die entsprechende Einbindung
   * gueltig sind
   */
  abstract protected function setOptions();
  
  /**
   * Prueft, ob die Pflichtfelder in der Config gesetzt sind
   *
   * @param array $config
   */
  protected function checkRequiredOptions(array $config = array())
  {
    $this->checkRequiredOption(SBScreenshot::DIRECTORY, $config);
  }

  /**
   * Prueft, ob eine bestimmtes Pflichtfeld in der Config gesetzt wurde
   *
   * @param string  $optionname
   * @param array   $config
   */
  protected function checkRequiredOption($optionname, array $config = array())
  {
    if (!\array_key_exists($optionname, $config)) {
      throw new InvalidConfigException(
          'Configuration array must have a key for "' . SBScreenshot::DIRECTORY . '"',
          \Seitenbau\Log::ERR
      );
    }
  }
  
  /**
   * aktiviert oder deaktiviert das Screenshot-Tool
   *
   * @param boolean $isActiv
   */
  protected function setActiv($isActiv)
  {
    $this->isActiv = ($isActiv === true || $isActiv == 'yes' || $isActiv == 1)
                   ? true : false;
  }

  protected function setDirectory($directory)
  {
    $this->directory = $directory;
    $this->createDirectory();
  }

  protected function setFiletype($filetype)
  {
    $this->filetype = $filetype;
  }

  /**
   * Erstellt den Ordner, indem die Screenshots gespeichert werden
   */
  protected function createDirectory()
  {
    if ($this->directory != null
        && !\is_dir($this->directory)
    ) {
      mkdir($this->directory);
    }
  }
  
  /**
   * loescht den zuletzt aufgetretenen Fehler
   */
  protected function clearLastError()
  {
    $this->lastError = null;
  }
  
  /**
   * setzt den zuletzt aufgetretenen Fehler
   *
   * @param string $error
   */
  protected function setLastError($error)
  {
    $this->lastError = $error;
  }
  
  /**
   * liefert den zuletzt aufgetretenen Fehler
   *
   * @return string
   */
  public function getLastError()
  {
    return $this->lastError;
  }
}
