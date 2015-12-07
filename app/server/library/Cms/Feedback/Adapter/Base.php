<?php
namespace Cms\Feedback\Adapter;

/**
 * Feedback Adapter Base
 *
 * @package      Cms
 */

abstract class Base
{
  
  /**
   * Schickt das Feedback ab
   *
   * @return  boolean true bei Erfolg, sonst false
   */
  abstract function send(\Cms\Feedback $feedback);
}
