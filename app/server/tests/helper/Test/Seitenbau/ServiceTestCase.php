<?php
namespace Test\Seitenbau;

use Cms\Validator\UniqueId as UniqueIdValidator,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * ServiceTestCase
 *
 * @package      Application
 * @subpackage   Controller
 */
class ServiceTestCase extends TransactionTestCase
{

  public static function setUpBeforeClass()
  {
    parent::setUpBeforeClass();
    // Uebersetzung vorhanden?
    if (!Registry::isRegistered('Zend_Translate'))
    {
      // Locale erweitern; Lade Uebersetzungen und speichere Translate Objekt in der Registry
      $lang = 'de';
      $locale = new \Zend_Locale();
      $locale->setLocale($lang);
      $langFile = APPLICATION_PATH . '/translations/' . $lang . '.php';
      Registry::set(
        'Zend_Translate', 
        new \Zend_Translate('array', $langFile, $locale->getLanguage())
      );
    }
  }

  /**
   * @param  string $websiteId
   * @param  array  $medias
   * @return boolean
   */
  protected function createTestMedias($websiteId, array $medias)
  {
    $config = Registry::getConfig();
    $testMediaDirectory = $config->media->files->directory;
    if (is_dir($testMediaDirectory))
    {
      $testWebsiteMediaDirectory = $testMediaDirectory
        . DIRECTORY_SEPARATOR . $websiteId;
      if (!is_dir($testWebsiteMediaDirectory))
      {
        mkdir($testWebsiteMediaDirectory);
      }

      foreach ($medias as $name)
      {
        $testMediaFile = $testWebsiteMediaDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testMediaFile, '');
      }
      return true;
    }
    return false;
  }
  /**
   * @param Orm\Iface\Data\Uuidable $uuidable
   * @param string $id
   * @return boolean
   */
  protected function validateUniqueId($uuidable, $id)
  {
    $uniqueIdValidator = new UniqueIdValidator(
      $uuidable::ID_PREFIX,
      $uuidable::ID_SUFFIX
    );

    if (!$uniqueIdValidator->isValid($id))
    {
      return false;
    }
    return true;
  }
  /**
   * @param  string $uri
   * @return string
   */
  protected function changeUserOptinUri($uri)
  {
    $formerUri = Registry::getConfig()->user->mail->optin->uri;
    $this->mergeIntoConfig(array('user' => array('mail' => array('optin' => array('uri' => $uri)))));
    $this->assertSame($uri, Registry::getConfig()->user->mail->optin->uri);
    return $formerUri;
  }
  /**
   * @param  string $uri
   * @return string
   */
  protected function changeUserRenewPasswordUri($uri)
  {
    $formerUri = Registry::getConfig()->user->mail->renew->password->uri;
    $this->mergeIntoConfig(array('user' => array('mail' => array('renew' => array('password' => array('uri' => $uri))))));
    $this->assertSame($uri, Registry::getConfig()->user->mail->renew->password->uri);
    return $formerUri;
  }
  
  /**
   * checks if an exception is thrown inside the tested callback
   *
   * @param callable    $testMethod
   * @param array       $testMethodParameters
   * @param string      $expectedExceptionClass
   * @param callable    $checkExceptionMethod
   */
  protected function assertException($testMethod, array $testMethodParameters, 
          $expectedExceptionClass, $checkExceptionMethod = null)
  {
    try {
      // call test methode
      return call_user_func_array($testMethod, $testMethodParameters);

    } catch(\Exception $actualException) {
      
      if (!($actualException instanceof $expectedExceptionClass)) {
        $this->fail('The expected exception '.$expectedExceptionClass.' has not been raised.', $actualException);
      }
      
      if (is_callable($checkExceptionMethod)) {
        $message = null;
        if (!$checkExceptionMethod($actualException, $message)) {
          if (isset($message)) {
            $this->fail($message);    
          } else {
            $this->fail('Checking exception failed.', $actualException);
          }
        }
      }
      
      // test successfully
      return;
    }
    
    // no exception raised
    $this->fail('The expected exception '.$expectedExceptionClass.' has not been raised.');    
  }
}