<?php
namespace Seitenbau;

use Seitenbau\Log as Log;
use Seitenbau\Logger as Logger;
use \Zend_Config as Config;

/**
 * @package      Seitenbau
 * @subpackage   Ftp
 */
class Ftp
{
  const FTP_DIRECTORY_SEPARATOR =  '/';
  /**
   * @var Ftp Stream
   */
  private $connectionId;
  /**
   * @var Zend_Config
   */
  private $ftpConfig;
  /**
   * @var Seitenbau\Logger
   */
  private $logger;
  /**
   * @var string
   */
  private $lastError;

  /**
   * @param \Zend\Config     $ftpConfig
   * @param Seitenbau\Logger $logger
   */
  public function __construct(Config $ftpConfig, Logger $logger = null)
  {
    $this->ftpConfig = $ftpConfig;
    $this->logger = $logger;
    $this->lastError = '';
  }

  /**
   * @return string   Letze-Fehlermeldung
   */
  public function getLastError()
  {
    return $this->lastError;
  }

  /**
   * @param string   Fehlermeldung aufnehmen
   */
  public function error($error, $methode = '', $line = '', $log = false)
  {
    $this->lastError = $error;
    if ($log && $this->logger !== null) {
      $this->logger->log($methode, $line, $error, Log::ERR);
    }
  }

  /**
   * @param string   Fehlermeldung aufnehmen
   */
  public function debug($log, $methode = '', $line = '')
  {
    if ($log && $this->logger !== null) {
      $this->logger->log($methode, $line, $log, Log::DEBUG);
    }
  }

  /**
   * @return Ftp Stream
   * @throws \Exception
   */
  public function connect()
  {
    if (!function_exists('ftp_connect')) {
      $exceptionMessage = 'Ftp extension is not enabled';
      $this->lastError = $exceptionMessage;
      throw new \Exception($exceptionMessage);
    }

    if ($this->ftpConfig === null) {
      $exceptionMessage = 'Ftp config not set';
      $this->lastError = $exceptionMessage;
      throw new \Exception($exceptionMessage);
    }
    
    $connectionId = @ftp_connect(
        $this->ftpConfig->get('host')
    );

    if (!$connectionId) {
      $e = error_get_last();
      $exceptionMessage = sprintf(
          "Ftp connection to '%s' failed: %s",
          $this->ftpConfig->get('host'),
          ( isset($e['message']) ? $e['message'] : '' )
      );
      $this->lastError = $exceptionMessage;
      throw new \Exception($exceptionMessage);
    }

    $loginResult = @ftp_login(
        $connectionId,
        $this->ftpConfig->get('username'),
        $this->ftpConfig->get('password')
    );

    if (!$loginResult) {
      $e = error_get_last();
      $exceptionMessage = sprintf(
          "Ftp login for user '%s' to '%s' failed: %s",
          $this->ftpConfig->get('username'),
          $this->ftpConfig->get('host'),
          ( isset($e['message']) ? $e['message'] : '' )
      );
      $this->lastError = $exceptionMessage;
      throw new \Exception($exceptionMessage);
    }

    $pasvResult = @ftp_pasv(
        $connectionId,
        true
    );

    if (!$pasvResult) {
      $e = error_get_last();
      $exceptionMessage = sprintf(
          "Switch to passive mode failed: %",
          ( isset($e['message']) ? $e['message'] : '' )
      );
      $this->lastError = $exceptionMessage;
      throw new \Exception($exceptionMessage);
    }
    
    $this->connectionId = $connectionId;
    return $connectionId;
  }

  public function close()
  {
    @ftp_close($this->connectionId);
  }
  
  protected function callFtpFunction($fncName)
  {
    // ConnectionId muss vorhanden sein
    if ($this->connectionId === null) {
      return false;
    }
    
    // Parameter aufbereiten
    $args = func_get_args();
    array_shift($args);
    array_unshift($args, $this->connectionId);

    // Datei kopieren
    $secCount = 0;
    do {
      // Ist bereits ein Fehler aufgetreten -> Verbindung neu aufbauen
      if ($secCount > 0) {
        $this->error(
            sprintf("Trying to reconnect to ftp server (count:%d)", $secCount),
            __METHOD__,
            __LINE__,
            true
        );
        try {
          $this->close();
          sleep(1);
          $this->connect();
          array_shift($args);
          array_unshift($args, $this->connectionId);
        } catch (Exception $e) {
          // do nothing
          return;
        }
      }
      
      // Ftp-Funktion ausfuehren
      $ret = @call_user_func_array($fncName, $args);
      if ($ret === false) {
      // Caller ermitteln
        $backtrace = debug_backtrace();

        // Fehler ausgeben
        $e = error_get_last();
        $this->error(
            sprintf(
                "%s failed for (%s): %s",
                $fncName,
                implode(', ', $args),
                ( isset($e['message']) ? $e['message'] : '' )
            ),
            $backtrace[1]['class'].'::'.$backtrace[1]['function'],
            $backtrace[0]['line'],
            true
        );
      }
    } // Bei Fehler: Maximal 5 Versuche durchfuehren
    while ($ret === false && ++$secCount < 5) {
    }

    // Fehler
    if ($ret === false) {
    // Fehler
      return false;
    }
    
    // Wert zurueckgeben
    return $ret;
  }

  /**
   * @param string  $directory
   * @param boolean
   */
  public function existsDirectory($directory)
  {
    if ($this->connectionId === null) {
      return false;
    }

    // Get the current working directory
    $origin = $this->callFtpFunction('ftp_pwd');
    if ($origin === false) {
      return false;
    }

    // Attempt to change directory, suppress errors
    if (@ftp_chdir($this->connectionId, $directory)) {
    // If the directory exists, set back to origin
        @ftp_chdir($this->connectionId, $origin);
        return true;
    }

    // Directory does not exist
    return false;
  }

  /**
   * @param string  $filePathname
   * @param boolean
   */
  public function existsFile($filePathname)
  {
    if ($this->connectionId === null) {
      return false;
    }

    // versuchen die Dateigroesse ermitteln
    if (-1 != $this->callFtpFunction('ftp_size', $filePathname)) {
    // Datei vorhanden
        return true;
    }

    // Datei existiert nicht
    return false;
  }

  /**
   * @param  string  $directory
   * @return boolean
   */
  public function removeDirectory($directory)
  {
    if ($this->connectionId === null) {
      return false;
    }
    $directoryFilePath = preg_replace(
        '/(.+?)\/*$/',
        '\\1/',
        $directory
    );

    // Verzeichnis vorhanden?
    if (!$this->existsDirectory($directory)) {
    // Verzeichnis nicht vorhanden -> muss auch nicht geloescht werden
      return true;
    }

    // Verzeichnis auslesen
    $list = $this->listFiles($directoryFilePath);

    // Darunterliegende Dateien und Verziehcnisse loeschen
    if ($list !== false && count($list) > 0) {
      foreach ($list as $item) {
      // . und .. ueberspringen
        if (!preg_match('/\.+$/', $item['text'])) {
        // Verzeichniss?
          if ($item['isDir']) {
            if (!$this->removeDirectory($directoryFilePath . $item['text'])) {
              return false;
            }
          } else {
            // Datei Loeschen
            if (!$this->removeFile($directoryFilePath . $item['text'])) {
              return false;
            }
          }
        }
      }
    }

    // Verzeichnis selbst loeschen
    if (!$this->callFtpFunction('ftp_rmdir', $directoryFilePath)) {
      return false;
    }

    // Erfolgreich
    return true;
  }

  /**
   * @param  string  $file
   * @return boolean
   */
  public function removeFile($file)
  {
    if ($this->connectionId === null) {
      return false;
    }

    // Datei vorhanden?
    if (!$this->existsFile($file)) {
    // Datei nicht vorhanden -> muss auch nicht geloescht werden
      return true;
    }

    // Datei Loeschen
    if (!$this->callFtpFunction('ftp_delete', $file)) {
      return false;
    }
    
    // Erfoglreich
    return true;
  }
  
  /**
   * @param  string   $directory
   * @param  string   $baseDir
   * @param  array    $newFileList
   * @param  array    $oldFileList
   * @return boolean
   */
  public function transferLocalFiles($directory, $baseDir, &$newFileList, &$oldFileList)
  {
    if ($this->connectionId === null) {
      return false;
    }
    
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($directory),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    $remoteDirectory = preg_replace(
        '/^\/*(.+?)\/*$/',
        '/\\1/',
        $baseDir
    );

    $createdRemoteDirectories = array();

    while ($iterator->valid()) {
      if (!$iterator->isDot()) {
        if ($iterator->isDir()
            && $iterator->getSubPathName() === $iterator->getFilename()) {
          $remoteDir = $remoteDirectory . $iterator->getSubPathName();
          // Doppelte Slashes korrigieren
          $remoteDir = preg_replace(
              '/'.preg_quote(self::FTP_DIRECTORY_SEPARATOR, '/').'+/',
              self::FTP_DIRECTORY_SEPARATOR,
              $remoteDir
          );

          // Verzeichnis in die Liste aufnehmen
          $newFileList[$iterator->getSubPathname()] = array('file'  => false);

          if (!in_array($remoteDir, $createdRemoteDirectories)) {
            if (!$this->createDirectory($remoteDir)) {
              return false;
            }
            $createdRemoteDirectories[] = $remoteDir;
          }
        } elseif ($iterator->isDir()
                && $iterator->getSubPathName() !== $iterator->getFilename()) {
          $remoteDir = $remoteDirectory .
          str_replace(DIRECTORY_SEPARATOR, '/', $iterator->getSubPathName());
          // Doppelte Slashes korrigieren
          $remoteDir = preg_replace(
              '/'.preg_quote(self::FTP_DIRECTORY_SEPARATOR, '/').'+/',
              self::FTP_DIRECTORY_SEPARATOR,
              $remoteDir
          );

          // Verzeichnis in die Liste aufnehmen
          $newFileList[$iterator->getSubPathname()] = array('file'  => false);

          if (!in_array($remoteDir, $createdRemoteDirectories)) {
            if (!$this->createDirectory($remoteDir)) {
              return false;
            }
            $createdRemoteDirectories[] = $remoteDir;
          }
        }
        if ($iterator->isFile()) {
          $localSourceFile = $iterator->key();

          $ftpDestinationFile = $remoteDirectory
          . str_replace(DIRECTORY_SEPARATOR, '/', $iterator->getSubPath())
          . self::FTP_DIRECTORY_SEPARATOR . basename($iterator->key());

          // Doppelte Slashes korrigieren
          $ftpDestinationFile = preg_replace(
              '/'.preg_quote(self::FTP_DIRECTORY_SEPARATOR, '/').'+/',
              self::FTP_DIRECTORY_SEPARATOR,
              $ftpDestinationFile
          );
          

          // Datei in die Liste aufnehmen
          $curFilePathname = $iterator->getSubPathname();
          $newFileList[$curFilePathname] = array(
          'file'  => true,
          'md5'   => md5_file($localSourceFile),
          );

          // Muss die Datei kopiert werden
          $copyFile = true;
          if (is_array($oldFileList)
            && isset($oldFileList[$curFilePathname])
            && is_array($oldFileList[$curFilePathname])
            && isset($oldFileList[$curFilePathname]['md5'])
            && $oldFileList[$curFilePathname]['md5'] == $newFileList[$curFilePathname]['md5'] ) {
            // Datei muesste nicht aktualisiert werden -> Dateigroesse pruefen
            $remoteSize = $this->callFtpFunction('ftp_size', $ftpDestinationFile);
            if ($remoteSize !== false && $remoteSize != -1 && $remoteSize == filesize($localSourceFile)) {
              // Datei hat auch die gleiche Groesse -> nicht hichladen
              $copyFile = false;
            }
          }

          // Datei kopieren
          if ($copyFile) {
            if (!$this->transferLocalFile($localSourceFile, $ftpDestinationFile)) {
              // Fehler
                return false;
            }
          }
        }
      }
      $iterator->next();
    }

    return true;
  }

  /**
   * @param  string   $localFilePathname
   * @param  string   $remoteFilePathname
   * @return boolean
   */
  public function transferLocalFile($localFilePathname, $remoteFilePathname)
  {
    if ($this->connectionId === null) {
      return false;
    }

    // Datei uploaden
    $ftpPut = $this->callFtpFunction(
        'ftp_put',
        $remoteFilePathname,
        $localFilePathname,
        FTP_BINARY
    );
    // Fehler beim Upload
    if ($ftpPut === false) {
    // Fehler
      return false;
    }

    return true;
  }

  /**
   * @param  string  $directory
   * @return boolean
   */
  public function createDirectory($directory)
  {
    // Verzeichnis bereits vorhanden?
    if ($this->existsDirectory($directory)) {
    // Verzeichnis vorhanden -> nicht anlegen
      return true;
    }

    // Verzeichnis anlegen
    if (!$this->callFtpFunction('ftp_mkdir', $directory)) {
      return false;
    }

    return true;
  }

  /**
   * @param  string  $directory
   * @param  boolean $recursive
   * @return boolean
   */
  public function listFiles($directory, $recursive = false)
  {
    if ($this->connectionId === null) {
      return false;
    }

    $rawfiles = $this->callFtpFunction('ftp_rawlist', "-a " . $directory);
    if ($rawfiles === false) {
      return false;
    }

    $structure = array();

    foreach ($rawfiles as $rawfile) {
      if (!empty($rawfile)) {
        $info = preg_split("/[\s]+/", $rawfile, 9);
        $curFile = array(
            'text'   => $info[8],
            'isDir'  => $info[0]{0} == 'd',
            'size'   => $this->listFilesByteConvert($info[4]),
            'chmod'  => $this->listFilesChmodNum($info[0]),
            'date'   => strtotime($info[6] . ' ' . $info[5] . ' ' . $info[7]),
            'raw'    => $info
        );
        if ($recursive && $curFile['isDir']
            && $curFile['text'] != '.' && $curFile['text'] != '..') {
          $children = $this->listFiles($directory.self::FTP_DIRECTORY_SEPARATOR.$curFile['text'], true);
          if (count($children) > 0) {
            $curFile['children'] = $children;
          }
        }
        $structure[] = $curFile;
      }
    }

    return $structure;
  }

  /**
   * @param string  $filename   Pfad der einzulesenden Datei
   * @param string  $content    Filecontent
   * @return boolean
   */
  public function getFileContents($filename, &$content)
  {
    if ($this->connectionId === null) {
      return false;
    }
    
    // Datei vorhanden?
    if ($this->existsFile($filename)) {
    // Datei einlesen
      $tempHandle = fopen('php://temp', 'r+b');
      if ($this->callFtpFunction('ftp_fget', $tempHandle, $filename, FTP_BINARY, 0)) {
        rewind($tempHandle);
        $content = stream_get_contents($tempHandle);
        return true;
      }
    }

    // Fehler
    return false;
  }

  /**
   * @param string  $filename   Pfad der schreibenden Datei
   * @param string  $content    Filecontent
   * @return boolean
   */
  public function putFileContents($filename, &$content)
  {
    if ($this->connectionId === null) {
      return false;
    }

    // Datei einlesen
    if ($tempHandle = fopen('php://temp', 'w+b')) {
      fwrite($tempHandle, $content);
      rewind($tempHandle);
      if (!$this->callFtpFunction('ftp_fput', $filename, $tempHandle, FTP_BINARY, 0)) {
      // Fehler
        return true;
      }
    }

    // Datei erfolgreich uebertragen
    return false;
  }

  /**
   * @return boolean
   */
  public function chmodDirectories($directory, $chmodItems, $recursive = false)
  {
    if ($this->connectionId === null) {
      return false;
    }

    $remoteDirectory = preg_replace(
        '/^\/*(.+?)\/*$/',
        '/\\1/',
        $directory
    );
    
    if (isset($chmodItems) && is_array($chmodItems)) {
      foreach ($chmodItems as $accessMode => $items) {
        $currentAccessMode = octdec(str_pad($accessMode, 4, '0', STR_PAD_LEFT));

        foreach ($items as $nextItem) {
          $remoteDir = $remoteDirectory . $nextItem;
          if (!$this->callFtpFunction('ftp_chmod', $currentAccessMode, $remoteDir)) {
          // Fehler
            return false;
          }

          if ($recursive) {
            $chmodChildrenItems = array();
            $children = $this->listFiles($remoteDir);
            foreach ($children as $child) {
              if ($child != '.' && $child !=  '..') {
                $chmodChildrenItems[$accessMode][] = $child['text'];
              }
            }
            $this->chmodDirectories($remoteDir, $chmodChildrenItems, false);
          }
        }
      }
    }

    return true;
  }
  
  private function listFilesByteConvert($bytes)
  {
      $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
      $exp = floor(log($bytes) / log(1024));
      $size = 0;
    if ($exp < 0 || $exp >= count($symbol)) {
      $exp = 0;
    }
    if ($exp > 0) {
      $size = ($bytes / pow(1024, $exp));
    }
      return sprintf('%.2f ' . $symbol[$exp], $bytes);
  }
  
  private function listFilesChmodNum($chmod)
  {
      $trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
      $chmod = substr(strtr($chmod, $trans), 1);
      $array = str_split($chmod, 3);
      return array_sum(str_split($array[0])) . array_sum(str_split($array[1])) . array_sum(str_split($array[2]));
  }
}
