<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Publish as Request;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Request\Validator\Error;

/**
 * Publish request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */
class Publish extends Base
{
  /**
   * validate the website action request
   *
   * @param Cms\Request\Publish\Website $actionRequest
   */
  protected function validateMethodWebsite(Request\Website $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');
  }
}
