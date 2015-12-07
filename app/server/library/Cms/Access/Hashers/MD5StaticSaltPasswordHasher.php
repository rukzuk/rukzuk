<?php

namespace Cms\Access\Hashers;

/**
 * Class MD5StaticSaltPasswordHasher
 *
 * @package Cms\Access\Hashers
 */
class MD5StaticSaltPasswordHasher implements IPasswordHasher
{
  const HASH_ALGORITHM = '';
  // static salt (do not change unless you want to break current hashes)
  const SALT_FOR_PASSWORD = 'SALTFORPASSWORDHASH';

  /**
   * Creates a hash for the given password
   *
   * @param string      $password the password to hash
   *
   * @param string|null $salt     unsupported as the salt fixed (for backwards compatibility)
   *
   * @return string             the hashed password (md5)
   */
  public function create($password, $salt = null)
  {
    return md5($password . self::SALT_FOR_PASSWORD);
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
    return ($this->create($password) === $good_hash);
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
    return (strlen($password) === 32);
  }
}
