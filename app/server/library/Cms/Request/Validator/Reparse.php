<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Reparse as Request;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;

/**
 * Reparse request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Reparse extends Base
{
  /**
   * validate the website action request
   *
   * @param Cms\Request\Reparse\Website $actionRequest
   */
  protected function validateMethodWebsite(Request\Website $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'id');
  }
}
