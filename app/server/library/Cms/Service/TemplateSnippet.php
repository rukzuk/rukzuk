<?php
namespace Cms\Service;

use Cms\Dao\TemplateSnippet\TemplateSnippetSource;
use Cms\Exception;
use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;

/**
 * TemplateSnippet service
 *
 * @package      Cms
 * @subpackage   Service
 *
 * @method \Cms\Dao\TemplateSnippet getDao
 */
class TemplateSnippet extends DaoServiceBase
{
  const SNIPPET_SUBDIRECTORY = '_snippets';

  /**
   * returns all TemplateSnippets of the given Website
   *
   * @param   string  $websiteId
   * @param   string  $orderDirection
   *
   * @return  \Cms\Data\TemplateSnippet[]
   */
  public function getAll($websiteId, $orderDirection = 'ASC')
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->getAll($snippetSource, $orderDirection);
  }

  /**
   * returns the specified "Template Snippets" of the given Website
   *
   * @param   string  $websiteId
   * @param   array   $ids
   *
   * @return  \Cms\Data\TemplateSnippet[]
   */
  public function getByIds($websiteId, $ids)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->getByIds($snippetSource, $ids);
  }

  /**
   * return the TemplateSnippets of the given id and Website
   *
   * @param string  $websiteId
   * @param string  $id
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function getById($websiteId, $id)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->getById($snippetSource, $id);
  }

  /**
   * deletes the TemplateSnippets of the given ids and website id
   *
   * @param string $websiteId
   * @param array  $ids
   */
  public function delete($websiteId, $ids)
  {
    if (!is_array($ids)) {
      $ids = array($ids);
    }
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->deleteByIds($snippetSource, $ids);
  }

  /**
   * @param string  $websiteid
   */
  public function deleteByWebsiteId($websiteId)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->deleteByWebsiteId($snippetSource);
  }

  /**
   * creates a new TemplateSnippet
   *
   * @param string $websiteId
   * @param array  $columnsValues
   * @param bool   $useIdFromColumnsValues
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function create($websiteId, array $columnsValues, $useIdFromColumnsValues = false)
  {
    $snippet = new DataTemplateSnippet();
    $this->setAttributesToTemplateSnippet($snippet, $columnsValues);
    if ($useIdFromColumnsValues) {
      $snippet->setId($columnsValues['id']);
    }
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->create($snippetSource, $snippet);
  }

  /**
   * updates the TemplateSnippet of the given id and website id
   *
   * @param string  $websiteId
   * @param string  $id
   * @param array   $columnsValues
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function update($websiteId, $id, array $columnsValues)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    $snippet = $this->getById($websiteId, $id);
    $this->setAttributesToTemplateSnippet($snippet, $columnsValues);
    return $this->getDao()->update($snippetSource, $snippet);
  }
  
  
  /**
   * copy TemplateSnippets of the given ids and website id into another website
   *
   * @param string  $fromWebsiteId
   * @param string  $toWebsiteId
   * @param array   $snippetIds
   *
   * @return boolean
   */
  public function copyToNewWebsite($fromWebsiteId, $toWebsiteId, array $snippetIds = array())
  {
    if (count($snippetIds) > 0) {
      $this->delete($toWebsiteId, $snippetIds);
    }
    $snippetSourceFrom = $this->getSnippetSource($fromWebsiteId);
    $snippetSourceTo = $this->getSnippetSource($toWebsiteId);
    return $this->getDao()->copyToNewWebsite($snippetSourceFrom, $snippetSourceTo, $snippetIds);
  }

  /**
   * Checks if there is a template snippet under the given TemplateSnippet Id and Website Id
   *
   * @param  string $websiteId
   * @param  string $id
   *
   * @return boolean
   */
  public function existsSnippet($websiteId, $id)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->existsSnippet($snippetSource, $id);
  }

  /**
   * search over the TemplateSnippets content and returns the found TemplateSnippets
   *
   * @param string $websiteId
   * @param string $needle
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function searchInContent($websiteId, $needle)
  {
    $snippetSource = $this->getSnippetSource($websiteId);
    return $this->getDao()->searchInContent($snippetSource, $needle);
  }

  /**
   * @param \Cms\Data\TemplateSnippet
   * @param array $attributes
   */
  protected function setAttributesToTemplateSnippet(
      DataTemplateSnippet $snippet,
      array $attributes
  ) {
    if (isset($attributes['name'])) {
      $snippet->setName($attributes['name']);
    }
    if (isset($attributes['description'])) {
      $snippet->setDescription($attributes['description']);
    }
    if (isset($attributes['category'])) {
      $snippet->setCategory($attributes['category']);
    }
    if (isset($attributes['content'])) {
      $contentString = (is_array($attributes['content']))
        ? \Zend_Json::encode($attributes['content'])
        : $attributes['content'];
      $snippet->setContent($contentString);
    }
  }

  /**
   * @param string $websiteId
   *
   * @return TemplateSnippetSource
   */
  protected function getSnippetSource($websiteId)
  {
    $sources = array();
    try {
      $packageService = $this->getPackageService();
      $packages = $packageService->getAll($websiteId);
      foreach ($packages as $package) {
        $sources = array_merge($sources, $package->getTemplateSnippetsSource());
      }
    } catch (Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return new TemplateSnippetSource($websiteId, $sources);
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    return $this->getService('Package');
  }
}
