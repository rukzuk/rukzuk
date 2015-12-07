<?php
namespace Cms\Validator;

use \Orm\Data\TemplateSnippet as OrmTemplateSnippet;

class TemplateSnippetId extends UniqueId
{
  public function __construct()
  {
    parent::__construct(OrmTemplateSnippet::ID_PREFIX, OrmTemplateSnippet::ID_SUFFIX);
  }
}
