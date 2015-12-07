<?php
namespace Cms\Access\Hashers;

/**
 * Password hashing with PBKDF2
 * Aims to be compatible with 'django.contrib.auth.hashers.PBKDF2PasswordHasher'
 *
 * based on work of TheBlintOne and havoc AT defuse.ca (www: https://defuse.ca/php-pbkdf2.htm)
 */

class PBKDF2PasswordHasher implements IPasswordHasher
{
  const HASH_ALGORITHM = 'pbkdf2_sha256';
  // These constants may be changed without breaking existing hashes.
  const PBKDF2_ITERATIONS = 12000;
  const PBKDF2_SALT_BYTES = 24;
  const PBKDF2_HASH_BYTES = 24;

  const HASH_SECTIONS = 4;
  const HASH_ALGORITHM_INDEX = 0;
  const HASH_ITERATION_INDEX = 1;
  const HASH_SALT_INDEX = 2;
  const HASH_PBKDF2_INDEX = 3;
  const HASH_SEPARATOR = '$';


  /**
   * Creates a hash for the given password
   *
   * @param string      $password the password to hash
   *
   * @param string|null $salt     salt, if not provided a random salt is generated (make sure the salt is as random as possible)
   *
   * @throws \Exception
   * @return string             the hashed password in format "algorithm:iterations:salt:hash"
   */
  public function create($password, $salt = null)
  {
    $salt = is_null($salt) ? $this->generateSalt() : $salt;
    return self::HASH_ALGORITHM . self::HASH_SEPARATOR . self::PBKDF2_ITERATIONS . self::HASH_SEPARATOR . $salt . self::HASH_SEPARATOR .
    base64_encode($this->hash(
        $this->extractSubAlgorithm(self::HASH_ALGORITHM),
        $password,
        $salt,
        self::PBKDF2_ITERATIONS,
        self::PBKDF2_HASH_BYTES,
        true
    ));
  }

  /**
   * Checks if the given password matches the given hash created by PBKDF::create( string )
   *
   * @param string $password  the password to check
   * @param string $good_hash the hash which should be match the password
   *
   * @return boolean             true if $password and $good_hash match, false otherwise
   *
   * @see self::create
   */
  public function validate($password, $good_hash)
  {
    $params = explode(self::HASH_SEPARATOR, $good_hash);
    if (count($params) < self::HASH_SECTIONS) {
      return false;
    }
    $pbkdf2 = base64_decode($params[self::HASH_PBKDF2_INDEX]);
    return $this->slowEquals(
        $pbkdf2,
        $this->hash(
            $this->extractSubAlgorithm($params[self::HASH_ALGORITHM_INDEX]),
            $password,
            $params[self::HASH_SALT_INDEX],
            (int)$params[self::HASH_ITERATION_INDEX],
            strlen($pbkdf2),
            true
        )
    );
  }

  /**
   * Generate a random salt
   *
   * @return string
   */
  protected function generateSalt()
  {
    return base64_encode(mcrypt_create_iv(self::PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));
  }

  public function isPasswordUsable($password)
  {
    return (strpos($password, self::HASH_ALGORITHM) === 0);
  }

  /**
   * Converts pbkdf2_sha256 to sha256
   *
   * @param string $full_algorithm_id
   *
   * @return string
   */
  protected function extractSubAlgorithm($full_algorithm_id)
  {
    return str_replace('pbkdf2_', '', $full_algorithm_id);
  }

  /**
   * Compares two strings $a and $b in length-constant time
   *
   * @param string $a the first string
   * @param string $b the second string
   *
   * @return boolean     true if they are equal, false otherwise
   */
  protected function slowEquals($a, $b)
  {
    $diff = strlen($a) ^ strlen($b);
    for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
      $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
  }

  /**
   * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
   *
   * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
   *
   * This implementation of PBKDF2 was originally created by https://defuse.ca
   * With improvements by http://www.variations-of-shadow.com
   * Added support for the native PHP implementation by TheBlintOne
   *
   * @param string  $algorithm  the hash algorithm to use. Recommended: SHA256
   * @param string  $password   the Password
   * @param string  $salt       a salt that is unique to the password
   * @param int     $count      iteration count. Higher is better, but slower. Recommended: At least 1000
   * @param int     $key_length the length of the derived key in bytes
   * @param boolean $raw_output [optional] (default false)    if true, the key is returned in raw binary format. Hex encoded otherwise
   *
   * @return string                                           a $key_length-byte key derived from the password and salt,
   *                                                          depending on $raw_output this is either Hex encoded or raw binary
   * @throws \Exception                                        if the hash algorithm are not found or if there are invalid parameters
   */
  protected function hash($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
  {
    $algorithm = strtolower($algorithm);
    if (!in_array($algorithm, hash_algos(), true)) {
      throw new \Exception('PBKDF2 ERROR: Invalid hash algorithm: "' . $algorithm . '"');
    }
    if ($count <= 0 || $key_length <= 0) {
      throw new \Exception('PBKDF2 ERROR: Invalid parameters.');
    }

    // use the native implementation of the algorithm if available
    if (function_exists("hash_pbkdf2")) {
      return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
    }

    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);

    $output = "";
    for ($i = 1; $i <= $block_count; $i++) {
      // $i encoded as 4 bytes, big endian.
      $last = $salt . pack("N", $i);
      // first iteration
      $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
      // perform the other $count - 1 iterations
      for ($j = 1; $j < $count; $j++) {
        $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
      }
      $output .= $xorsum;
    }

    if ($raw_output) {
      return substr($output, 0, $key_length);
    } else {
      return bin2hex(substr($output, 0, $key_length));
    }
  }
}
