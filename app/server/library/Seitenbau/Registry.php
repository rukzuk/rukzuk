<?php
namespace Seitenbau;

use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use \Zend_Locale as Locale;

/**
 * @package      Seitenbau
 * @subpackage   Registry
 */
class Registry extends \Zend_Registry
{
  /**
   * Speichert ein Logger-Objekt
   * @var \Seitenbau\Logger
   */
  private static $logger;
  
  /**
   * Speichert ein ActionLogger-Objekt
   * @var \Seitenbau\Logger\Action
   */
  private static $actionLogger;

  /**
   * Speichert ein Konfigurations-Objekt
   * @var \Zend_Config
   */
  private static $config;

  /**
   * Speichert einen Doctrine2 Entity Manager
   * @var \Doctrine\ORM\EntityManager
   */
  private static $entityManager;

  /**
   * Speichert eine Zend_Locale
   * @var \Zend_Locale
   */
  private static $locale;

  /**
   * Speichert eine Zend_Db_Adapter_Pdo Instanz
   * @var \Zend_Db_Adapter_Pdo
   */
  private static $dbAdapter;

  /**
   * Setzt das Logger-Objekt
   * @param \Seitenbau\Logger $logger Das Logger Objekt
   */
  public static function setLogger(Logger $logger)
  {
    self::$logger = $logger;
  }

  /**
   * Gibt das Logger-Objekt zurueck
   * @return \Seitenbau\Logger
   */
  public static function getLogger()
  {
    return self::$logger;
  }
  /**
   * Setzt das ActionLogger-Objekt
   * @param \Seitenbau\Logger\Action $logger Das Logger Objekt
   */
  public static function setActionLogger($logger)
  {
    self::$actionLogger = $logger;
  }

  /**
   * Gibt das ActionLogger-Objekt zurueck
   * @return \Seitenbau\Logger\Action
   */
  public static function getActionLogger()
  {
    return self::$actionLogger;
  }

  /**
   * Setzt das Konfigurations-Objekt
   * @param \Zend_Config $config Konfigurations-Objekt
   */
  public static function setConfig(\Zend_Config $config)
  {
    self::$config = $config;
  }

  /**
   * Gibt das Konfigurations-Objekt zurueck
   * @return \Zend_Config
   */
  public static function getConfig()
  {
    return self::$config;
  }
  /**
   * Setzt den Doctrine2 Entity Manager
   * @param \Doctrine\ORM\EntityManager $manager
   */
  public static function setEntityManager(DoctrineEntityManager $manager)
  {
    self::$entityManager = $manager;
  }
  /**
   * Gibt das Konfigurations-Objekt zurueck.
   * @return \Doctrine\ORM\EntityManager
   */
  public static function getEntityManager()
  {
    return self::$entityManager;
  }
  /**
   * Setzt den Zend_Locale
   * @param \Zend_Locale $locale
   */
  public static function setLocale(Locale $locale)
  {
    self::$locale = $locale;
  }
  /**
   * Gibt die Zend_Locale zurueck
   * @return \Zend_Locale
   */
  public static function getLocale()
  {
    return self::$locale;
  }
  /**
   * @param \Zend_Db_Adapter_Pdo_Mysql $dbAdapter
   */
  public static function setDbAdapter($dbAdapter)
  {
    self::$dbAdapter = $dbAdapter;
  }
  /**
   * @return \Zend_Db_Adapter_Pdo_Mysql
   */
  public static function getDbAdapter()
  {
    return self::$dbAdapter;
  }
  
  /**
   * @return \Zend_Controller_Plugin_Abstract
   */
  public static function getActionControllerPlugin($pluginName)
  {
    $pluginName = 'Cms\Controller\Plugin\\' . $pluginName;
    $plugin = \Zend_Controller_Front::getInstance()->getPlugin($pluginName);
    if ($plugin == false) {
      throw new \Cms\Exception(
          -30,
          __METHOD__,
          __LINE__,
          array('plugin' => $pluginName)
      );
    }
    return $plugin;
  }

  /**
   * returns the base url for the instance (e.g. https://cms.rukzuk.com)
   *
   * @param bool $useInternalUrl
   *
   * @return string
   */
  public static function getBaseUrl($useInternalUrl = false)
  {
    # get base url from config
    $config = self::getConfig();
    if ($config instanceof \Zend_Config) {
      if ($useInternalUrl) {
        if (isset($config->internalWebhost) && !empty($config->internalWebhost)) {
          return $config->internalWebhost;
        }
      }
      if (isset($config->webhost) && !empty($config->webhost)) {
        return $config->webhost;
      }
    }
    return self::getBaseUrlFromServerConfig();
  }

  /**
   * returns the base url for the instance given by SERVER variable
   *
   * @return string
   */
  public static function getBaseUrlFromServerConfig()
  {
    # get base url from SERVER variable
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
    return $scheme . $_SERVER['HTTP_HOST'];
  }

  public static function isSpaceExpired()
  {
  }
}
