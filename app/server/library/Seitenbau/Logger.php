<?php
namespace Seitenbau;

/**
 * Statische Wrapperklasse für Zend_Log
 *
 * @package      Seitenbau
 * @subpackage   Logger
 */
class Logger
{
  /**
   * Speichert ein Objekt der Klasse Zend_Log
   *
   * @var Zend_Log
   */
   private $logger = null;
  /**
   * @var integer
   */
   private $level = 0;

  /**
   * Konstruktor
   * @param Zend_Log $logger Objekt der Klasse Zend_Log
   */
   public function __construct(\Zend_Log $logger)
   {
     $this->logger = $logger;
   }

  /**
   * Loggt eine Nachricht
   *
   * @param string $method Funktions- oder Methodenname
   * @param int $line Zeilennummer
   * @param string $message Meldung
   * @param int $priority Level
   * @param string $logid ID des Logeintrag
   */
    public function log($method, $line, $message, $priority, $logid = '')
    {
      $this->doLog($method, $line, $message, null, $priority, $logid);
    }

  /**
   * Loggt eine Datenmenge (Array, assoziatives Array, Objekt)
   *
   * @param string $method Funktion oder Methode
   * @param integer $line Zeilennummer
   * @param string $info Information/Meldung
   * @param mixed $data Datenmenge (Array/Objekt)
   * @param integer $priority Debug-Level
   * @param string $logid ID des Logeintrag
   */
    public function logData($method, $line, $info, $data, $priority, $logid = '')
    {
      if ((int) $priority > (int) $this->level) {
        return;
      }
      $this->doLog($method, $line, $info, $data, $priority, $logid);
    }
  /**
   * Loggt eine Exception inkl. Stacktrace
   *
   * @param string $method Funktion/Methode
   * @param integer $line Zeilennummer
   * @param Exception $exc Exception
   * @param integer $priority Debug-Level
   */
    public function logException($method, $line, \Exception $exc, $priority, $logid = '', $logData = null)
    {
      if ((int) $priority > (int) $this->level) {
        return;
      }
      $excClass = get_class($exc);

      $message = $exc->getMessage();
      if (method_exists($exc, 'getData')) {
        $data = $exc->getData();
        if (is_array($data) && count($data) > 0) {
          $message = $this->replaceVars($message, $data);
        }
      }

      $data = array(
      'file'  => $exc->getFile(),
      'line'  => $exc->getLine(),
      'code'  => $exc->getCode(),
      'trace' => explode("\n", $exc->getTraceAsString()),
      );
      if (isset($logData)) {
        $data['data'] = $logData;
      }

      $this->doLog($method, $line, $excClass . ': ' . $message, $data, $priority, $logid);
    }
  /**
   * Setzt das Logger-Objekt
   *
   * @param Zend_Log $logger Objekt der Klasse Zend_Log
   */
    public function setLogger(\Zend_Log $logger)
    {
      $this->logger = $logger;
    }
  /**
   * Liefert das Logger-Objekt zurueck
   *
   * @return Zend_Log Objekt der Klasse Zend_Log
   */
    public function getLogger()
    {
      return $this->logger;
    }
  /**
   * Setzt das Logging-Level
   *
   * @param integer $level Logging Level
   */
    public function setLevel($level)
    {
      $this->level = $level;
    }
  /**
   * Liefert das Logging-Level zurueck
   *
   * @return integer
   */
    public function getLevel()
    {
      return $this->level;
    }

  /**
   * @return int
   */
    public function createLogId()
    {
      return sprintf('%d', rand(100000, 999999));
    }

    protected function replaceVars($message, $data)
    {
      if (is_array($data) && count($data) > 0) {
        foreach ($data as $key => $value) {
          if (is_string($value)) {
            $message = str_replace('{' . $key . '}', $value, $message);
          }
        }
      }
      return $message;
    }

  /**
   * Schreibt einen Log-Eintrag
   *
   * @param string $method Funktions- oder Methodenname
   * @param int $line Zeilennummer
   * @param string $message Meldung
   * @param mixed $data Zusaetzliche Informationen
   * @param int $priority Level
   * @param string $logid ID des Logeintrag
   */
    protected function doLog($method, $line, $message, $data, $priority, $logid = '')
    {
      if ((int) $priority > (int) $this->level) {
        return;
      }

      $this->logger->setEventItem('data', (isset($data) ? print_r($data, true) : null));
      $this->logger->setEventItem('method', $method);
      $this->logger->setEventItem('line', $line);
      $this->logger->setEventItem('logid', $logid);
      $this->logger->setEventItem('sessionId', session_id());
      $this->logger->log($message, $priority);
    }
}
