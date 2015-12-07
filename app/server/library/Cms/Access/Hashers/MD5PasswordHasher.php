<?php

namespace Cms\Access\Hashers;

/**
 * Class MD5PasswordHasher (password left)
 *
 * Format: md5l$<salt>$<hash>
 *
 * Compatible with rukzuk.auth.hashers.CustomMD5PasswordHasher
 *
 * @package Cms\Access\Hashers
 */
class MD5PasswordHasher implements IPasswordHasher
{
  const HASH_ALGORITHM = 'md5l';
  const SALT_BYTES = 24;

  /**
   * Creates a hash for the given password
   *
   * @param string      $password the password to hash
   *
   * @param string|null $salt     the salt you want to use
   *
   * @return string             the hashed password (md5$<salt>$<md5-hash>)
   */
  public function create($password, $salt = null)
  {
    $salt = is_null($salt) ? $this->generateSalt() : $salt;
    return self::HASH_ALGORITHM . '$' . $salt . '$' . md5($password . $salt);
  }

  /**
   * Checks if the given password matches the given hash
   *
   * @param string $password  the password to check
   * @param string $good_hash the hash which should be match the password
   *
   * @return boolean             true if $password and $good_hash match, false otherwise
   */
  public function validate($password, $good_hash)
  {
    $goodHashParts = explode('$', $good_hash);
    if (count($goodHashParts) < 3) {
      return false;
    }
    $salt = $goodHashParts[1];
    return ($this->create($password, $salt) === $good_hash);
  }

  /**
   * Generate a random salt
   *
   * @return string
   */
  protected function generateSalt()
  {
    return base64_encode(mcrypt_create_iv(self::SALT_BYTES, MCRYPT_DEV_URANDOM));
  }

  /**
   * Tells weather the password (hash) is usable (checks prefix, format etc.)
   *
   * @param $password
   *
   * @return mixed
   */
  public function isPasswordUsable($password)
  {
    $pwParts = explode('$', $password);
    return (count($pwParts) === 3 && $pwParts[0] == self::HASH_ALGORITHM);
  }
}
