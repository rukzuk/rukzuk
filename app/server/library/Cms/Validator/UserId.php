<?php
namespace Cms\Validator;

use \Orm\Data\User as UserData;

class UserId extends UniqueId
{
  public function __construct()
  {
    parent::__construct(
        UserData::ID_PREFIX,
        UserData::ID_SUFFIX,
        '/^(%s)?[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}(%s)?$/'
    );
  }
}
