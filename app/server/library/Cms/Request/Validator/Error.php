<?php
namespace Cms\Request\Validator;

/**
 * request validator error
 *
 * @package    Cms
 * @subpackage Request Validator
 */

class Error extends \Cms\Exception
{
  protected $messages = array();

  public function __construct($field, $value, array $messages = array())
  {
    $data = array(
      'field' => $field,
      'value' => is_array($value) ? json_encode($value) : $value
    );

    parent::__construct(3, __METHOD__, __LINE__, $data);

    $this->message = $this->message . ' - ' . $this->getMessagesAsString($messages);
  }

  public function getMessages()
  {
    return $this->messages;
  }

  public function setMessages($messages)
  {
    $this->messages = $messages;
  }

  public function getMessagesAsString(array $messages)
  {
    return \implode(', ', $messages);
  }
}
