<?php

namespace library\Cms\Access\Hashers;


use Cms\Access\Hashers\MD5PasswordHasher;

/**
 * Class MD5PasswordHasherTest
 *
 * @package library\Cms\Access\Hashers
 */
class MD5PasswordHasherTest extends \PHPUnit_Framework_TestCase
{

  public function test_createPassword()
  {
    $ph = new MD5PasswordHasher();
    $pwHash = $ph->create('some%Password', 'FAKE_SALT');
    $this->assertEquals($pwHash, 'md5l$FAKE_SALT$1cdc401f80c9a4e737d181d18a17b9c0');
  }

  public function test_passwordShouldBeValid()
  {
    $ph = new MD5PasswordHasher();
    $this->assertTrue($ph->validate('some%Password', 'md5l$FAKE_SALT$1cdc401f80c9a4e737d181d18a17b9c0'));
    $this->assertFalse($ph->validate('some%Password', '1cdc401f80c9a4e737d181d18a17b9c0'));
  }

  public function test_passwordIsUsable()
  {
    $ph = new MD5PasswordHasher();
    $this->assertTrue($ph->isPasswordUsable('md5l$FAKE_SALT$1cdc401f80c9a4e737d181d18a17b9c0'));
    $this->assertFalse($ph->isPasswordUsable('md5$butOneDollaIsMissing'));
    $this->assertFalse($ph->isPasswordUsable('3k4jlkas34'));
    $this->assertFalse($ph->isPasswordUsable('a$b$c$d'));
    $this->assertFalse($ph->isPasswordUsable('md5$b$c$d'));
  }

}
