<?php
namespace Test\Seitenbau;

use Seitenbau\Registry,
    Test\Seitenbau\System\Helper as SystemHelper;
use Test\Rukzuk\ConfigHelper;

class Optin
{
  /**
   * @param string $mailsFromFileTransportDirectory 
   */
  public static function clearMailsFromFileTransports($mailsFromFileTransportDirectory)
  {
    $clearCommand = 'rm /tmp/ZendMail* 2> /dev/null &';
    
    if (strstr($clearCommand, $mailsFromFileTransportDirectory)) {
      SystemHelper::user_proc_exec($clearCommand);
    }
  }
  /**
   * @param string $mailsFromFileTransportDirectory 
   * @return integer
   */
  public static function getMailsCount($mailsFromFileTransportDirectory)
  {
    $filesGlob = $mailsFromFileTransportDirectory 
      . DIRECTORY_SEPARATOR . 'ZendMail*';
    $mailsCountCommand = sprintf("ls -1 %s 2> /dev/null | wc -l", $filesGlob);
    list($error, $output, $exitCode) = SystemHelper::user_proc_exec($mailsCountCommand);
    
    return (int) $output[0];
  }
  /**
   * @param  string $content 
   * @return string
   */
  public static function getRenewCodeFromMailContent($content)
  {
    $content = str_replace("=\r\n", "", $content);
    $content = str_replace("=\n", "", $content);
    if (preg_match('/\?t=3D([a-z0-9]+)/i', $content, $hits))
    {
      return $hits[1];
    }
    return '';
  }
  /**
   * @param  string $content 
   * @return string
   */
  public static function getOptinCodeFromMailContent($content)
  {
    $content = str_replace("=\r\n", "", $content);
    $content = str_replace("=\n", "", $content);
    if (preg_match('/\?t=3D([a-z0-9]+)/i', $content, $hits))
    {
      return $hits[1];
    }
    return '';
  }
  /**
   * @param  mixed $value
   * @return mixed
   */
  public static function changeConfiguredUserMailActiveStatus($value)
  {
    $formerUserMailActiveStatus = Registry::getConfig()->user->mail->activ;

    ConfigHelper::mergeIntoConfig(array('user' => array('mail' => array('activ' => $value))));

    \PHPUnit_Framework_Assert::assertEquals(
      $value, Registry::getConfig()->user->mail->activ
    );
    return $formerUserMailActiveStatus;
  }
  /**
   * @param  string  $mailsFromFileTransportDirectory
   * @param  boolean $removeDate
   * @return array
   */
  public static function getFileMailsContent($mailsFromFileTransportDirectory, 
    $removeDate = true)
  {
    $filesGlob = $mailsFromFileTransportDirectory 
      . DIRECTORY_SEPARATOR . 'ZendMail*';
    
    $mailContents = array();
    $filenames = glob($filesGlob, GLOB_NOSORT);
    
    foreach ($filenames as $filename) {
      if ($removeDate) {
        $mailContent = file_get_contents($filename);
        $mailContentLines = explode("\r", $mailContent);
        unset($mailContentLines[3]);
        $mailContents[] = implode("\r", $mailContentLines);
      } else {
        $mailContents[] = file_get_contents($filename);
      }
    }
    
    return $mailContents;
  }
  /**
   * @param  string  $mode
   * @param  integer $value
   * @return mixed
   */
  public static function changeConfiguredLifetime($mode, $value)
  {
    $formerLifetime = Registry::getConfig()->optin->lifetime->$mode;

    ConfigHelper::mergeIntoConfig(array('optin' => array('lifetime' => array($mode => $value))));

    \PHPUnit_Framework_Assert::assertEquals(
      $value, Registry::getConfig()->optin->lifetime->$mode
    );
    return $formerLifetime;
  }
  
  /**
   * @param  string   $mailTemplateFile
   * @param  array    $replace
   * @return string
   */
  public static function createMailContentFromMailTemplate($mailTemplateFile, array $replace)
  {
    return str_replace(array_keys($replace), array_values($replace), file_get_contents($mailTemplateFile));
  }
  
  /**
   * @param  string   $mailContent
   * @return string
   */
  public static function clearLineBreaksInMailContent($mailContent)
  {
    return str_replace(array("=\r\n", "=\n"), "", $mailContent);
  }
}