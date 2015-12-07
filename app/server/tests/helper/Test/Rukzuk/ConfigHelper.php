<?php


namespace Test\Rukzuk;


use Seitenbau\Registry;

class ConfigHelper
{
  /**
   * @var \Zend_Config
   */
  static private $originalConfig;

  /**
   * @var array
   */
  static private $originalConfigAsArray;

  /**
   * @return bool
   */
  public static function hasOriginalConfig()
  {
    return !is_null(self::$originalConfig);
  }

  /**
   * @param \Zend_Config $config
   */
  public static function setOriginalConfig(\Zend_Config $config)
  {
    self::$originalConfig = clone ($config);
    self::$originalConfig->setReadOnly();
    self::$originalConfigAsArray = self::$originalConfig->toArray();
  }

  /**
   * restore the original config
   */
  public static function restoreConfig()
  {
    Registry::setConfig(self::$originalConfig);
  }

  /**
   * @param array|\Zend_Config $config
   * @throws \Exception
   */
  public static function mergeIntoConfig($config)
  {
    if (is_array($config)) {
      $config = new \Zend_Config($config);
    } elseif(!($config instanceof \Zend_Config)) {
      throw new \Exception('no config given.');
    }
    $curConfig = Registry::getConfig();
    $newConfig = new \Zend_Config($curConfig->toArray(), true);
    $newConfig->merge($config);
    $newConfig->setReadOnly();
    Registry::setConfig($newConfig);
  }

  /**
   * enables global sets support
   *
   * @param string|null $baseDirectory
   *
   * @throws \Exception
   */
  public static function enableGlobalSets($baseDirectory = null)
  {
    $setsConfig = array(
      'enabled' => true,
    );
    if (is_string($setsConfig)) {
      $setsConfig['directory'] = $baseDirectory;
    }

    self::mergeIntoConfig(array(
      'item' => array(
        'sets' => $setsConfig
      )));
  }

  /**
   * disables global sets support
   */
  public static function disableGlobalSets()
  {
    self::mergeIntoConfig(array(
      'item' => array(
        'sets' => array(
          'enabled' => false
    ))));
  }

  /**
   * insert owner into config
   *
   * @param array $owner
   * @return array
   */
  public static function setOwner(array $owner = array())
  {
    $owner = self::addValuesToUserArray($owner, 'owner');
    self::mergeIntoConfig(array('owner' => $owner));
    return $owner;
  }

  /**
   * remove owner in config
   * @throws \Exception
   */
  public static function removeOwner()
  {
    self::removeValue(array('owner'));
  }

  /**
   * insert user into config
   *
   * @param array $user
   * @return array
   */
  public static function addConfigUser(array $user = array())
  {
    $user = self::addValuesToUserArray($user, 'user-'.time());
    $allConfigUsers = Registry::getConfig()->get('users', new \Zend_Config(array()))->toArray();
    $allConfigUsers[] = $user;
    self::mergeIntoConfig(array('users' => $allConfigUsers));
    return $user;
  }

  /**
   * remove all users in config
   * @throws \Exception
   */
  public static function removeAllConfigUsers()
  {
    self::removeValue(array('users'));
  }

  /**
   * fills the missing user attributes
   *
   * @param array   $user
   * @param string  $userName
   *
   * @return array
   */
  protected static function addValuesToUserArray(array $user, $userName)
  {
    if (!array_key_exists('id', $user)) {
      $user['id'] = \Seitenbau\UniqueIdGenerator::v4();
    }
    if (!array_key_exists('email', $user)) {
      $user['email'] = $userName.'@rukzuk.com';
    }
    if (!array_key_exists('password', $user)) {
      $user['password'] = 'pbkdf2_sha256$12000$COkz0I0VMpdd$WaetCgn3IiuXXIqN50pbB9c571Tef53kW04bPCso7Xg='; // == 123
    }
    if (!array_key_exists('firstname', $user)) {
      $user['firstname'] = $userName;
    }
    if (!array_key_exists('lastname', $user)) {
      $user['lastname'] = 'rukzuk';
    }
    if (!array_key_exists('language', $user)) {
      $user['language'] = 'en';
    }

    return $user;
  }

  /**
   * @param \Zend_Config $config
   *
   * @return \Zend_Config
   */
  public static function getWritableConfig(\Zend_Config $config)
  {
    return new \Zend_Config($config->toArray(), true);
  }

  /**
   * @param array $pathToValue
   */
  public static function removeValue(array $pathToValue)
  {
    $config = Registry::getConfig()->toArray();

    if (count($pathToValue) <= 0) {
      $config = array();
    } else {
      $configPart =& $config;
      foreach (array_slice($pathToValue, 0, -1) as $key) {
        if (!array_key_exists($key, $configPart)) {
          return;
        }
        $configPart =& $configPart[$key];
      }
      unset($configPart[end($pathToValue)]);
    }

    $newConfig = new \Zend_Config($config, true);
    $newConfig->setReadOnly();
    Registry::setConfig($newConfig);
  }

  /**
   * disables default page type support
   */
  public static function disableDefaultPageType()
  {
    self::removeValue(array('pageType', 'defaultPageType'));
  }
}
