<?php
namespace Cms\Response;

use \Seitenbau\Registry;

/**
 * Publish Response Angaben einer Website
 *
 * @package      Cms
 * @subpackage   Response
 */

class Publish implements IsResponseData
{

  // internal or external
  public $type = null;

  // external (currently FTP/SFTP)
  public $url = null;
  public $protocol = null;
  public $host = null;
  public $username = null;
  public $password = null;
  public $basedir = null;

  // internal (rukzuk-Webhosting)
  public $cname = null;


  public function __construct($data)
  {
    if (is_array($data)) {
      $this->setValuesFromArray($data);
    }
  }

  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  public function getHost()
  {
    return $this->host;
  }

  public function setHost($host)
  {
    $this->host = $host;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function setUsername($username)
  {
    $this->username = $username;
  }

  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Setzt das Publish-Attribut
   *
   * Dabei wird das Passwort Sternchen ersetzt
   *
   * @param type $password
   */
  public function setPassword($password)
  {
    if (!empty($password)) {
      $this->password = '*****';
    }
  }

  public function getBasedir()
  {
    return $this->basedir;
  }

  public function setBasedir($basedir)
  {
    $this->basedir = $basedir;
  }

  /**
   * @return string
   */
  public function getProtocol()
  {
    return $this->protocol;
  }

  /**
   * @param string $protocol
   */
  public function setProtocol($protocol)
  {
    $this->protocol = $protocol;
  }

  public function getUrl()
  {
    return $this->url;
  }

  public function setUrl($url)
  {
    $this->url = $url;
  }

  public function getCname()
  {
    return $this->cname;
  }

  public function setCname($cname)
  {
    $this->cname = $cname;
  }

  private function setValuesFromArray(array $data)
  {
    // type
    if (array_key_exists('type', $data)) {
      $this->setType($data['type']);
    }

    // external
    if (array_key_exists('url', $data)) {
      $this->setUrl($data['url']);
    }
    if (array_key_exists('protocol', $data)) {
      $this->setProtocol($data['protocol']);
    }
    if (array_key_exists('host', $data)) {
      $this->setHost($data['host']);
    }
    if (array_key_exists('username', $data)) {
      $this->setUsername($data['username']);
    }
    if (array_key_exists('password', $data)) {
      $this->setPassword($data['password']);
    }
    if (array_key_exists('basedir', $data)) {
      $this->setBasedir($data['basedir']);
    }

    // internal
    if (array_key_exists('cname', $data)) {
      $this->setCname($data['cname']);
    }
  }
}
