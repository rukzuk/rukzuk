<?php
namespace Cms\Business;

/**
 * Stellt die Business-Logik fuer Indexer zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */
class Indexer extends Base\Service
{
  const INDEXER_INDEX_ACTION = 'INDEXER_INDEX_ACTION';

  /**
   * @param string $websiteId
   */
  public function indexWebsite($websiteId)
  {
    return $this->getService()->indexWebsite($websiteId);
  }
}
