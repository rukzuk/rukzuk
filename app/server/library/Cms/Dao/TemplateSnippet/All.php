<?php


namespace Cms\Dao\TemplateSnippet;

use Cms\Dao\TemplateSnippet as TemplateSnippetDaoInterface;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

/**
 * dao for merging multiple template snippet dao
 *
 * @package Cms\Dao\TemplateSnippet
 */
class All implements TemplateSnippetDaoInterface
{
  protected $daoFilesystem;
  protected $daoDoctrine;

  public function __construct()
  {
    $this->daoDoctrine = new Doctrine();
    $this->daoFilesystem = new Filesystem();
  }

  /**
   * returns all TemplateSnippets of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $orderDirection
   *
   * @return  DataTemplateSnippet[]
   */
  public function getAll(TemplateSnippetSource $snippetSource, $orderDirection = null)
  {
    $filesystemSnippets = $this->daoFilesystem->getAll($snippetSource);
    $doctrineSnippets = $this->daoDoctrine->getAll($snippetSource);
    $allSnippets = $this->mergeSnippets($snippetSource, $filesystemSnippets, $doctrineSnippets);
    $this->sortSnippetsByName($allSnippets, $orderDirection);
    return $allSnippets;
  }

  /**
   * returns the specified "Template Snippets" of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   *
   * @return  DataTemplateSnippet[]
   */
  public function getByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    $users = array();
    foreach ($ids as $id) {
      $users[] = $this->getById($snippetSource, $id);
    }
    return $users;
  }

  /**
   * return the TemplateSnippets of the given id and Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return  DataTemplateSnippet
   */
  public function getById(TemplateSnippetSource $snippetSource, $id)
  {
    if ($this->daoDoctrine->existsSnippet($snippetSource, $id)) {
      $snippet = $this->daoDoctrine->getById($snippetSource, $id);
      if ($this->daoFilesystem->existsSnippet($snippetSource, $id)) {
        $snippet->setOverwritten(true);
      }
      return $snippet;
    }
    return $this->daoFilesystem->getById($snippetSource, $id);
  }

  /**
   * deletes the TemplateSnippets of the given ids and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   */
  public function deleteByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    foreach ($ids as $id) {
      $this->delete($snippetSource, $id);
    }
  }

  /**
   * deletes the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   */
  public function delete(TemplateSnippetSource $snippetSource, $id)
  {
    if ($this->daoDoctrine->existsSnippet($snippetSource, $id)) {
      return $this->daoDoctrine->delete($snippetSource, $id);
    }
    return $this->daoFilesystem->delete($snippetSource, $id);
  }

  /**
   * deletes all TemplateSnippets of the given website id
   *
   * @param TemplateSnippetSource $snippetSource
   */
  public function deleteByWebsiteId(TemplateSnippetSource $snippetSource)
  {
    return $this->daoDoctrine->deleteByWebsiteId($snippetSource);
  }

  /**
   * creates a new TemplateSnippet
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   */
  public function create(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    return $this->daoDoctrine->create($snippetSource, $snippet);
  }

  /**
   * updates the TemplateSnippet of the given id and website id.
   * if snippet exists only at filesystem, create the snippet in db.
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   */
  public function update(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    $snippetId = $snippet->getId();
    $existsAtFilesystem = $this->daoFilesystem->existsSnippet($snippetSource, $snippetId);
    $existsAtDoctrine = $this->daoDoctrine->existsSnippet($snippetSource, $snippetId);
    if ($existsAtFilesystem && !$existsAtDoctrine) {
      return $this->daoDoctrine->create($snippetSource, $snippet);
    }
    return $this->daoDoctrine->update($snippetSource, $snippet);
  }

  /**
   * copy TemplateSnippets of the given ids and website id into another website
   *
   * @param TemplateSnippetSource $snippetSourceFrom
   * @param TemplateSnippetSource $snippetSourceTo
   * @param array                 $ids
   *
   * @return boolean
   */
  public function copyToNewWebsite(
      TemplateSnippetSource $snippetSourceFrom,
      TemplateSnippetSource $snippetSourceTo,
      array $ids = array()
  ) {
    return $this->daoDoctrine->copyToNewWebsite($snippetSourceFrom, $snippetSourceTo, $ids);
  }

  /**
   * Checks if there is a template snippet under the given TemplateSnippet Id and Website Id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   */
  public function existsSnippet(TemplateSnippetSource $snippetSource, $id)
  {
    if ($this->daoDoctrine->existsSnippet($snippetSource, $id)) {
      return true;
    }
    return $this->daoFilesystem->existsSnippet($snippetSource, $id);
  }

  /**
   * search over the TemplateSnippets content and returns the found TemplateSnippets
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $needle
   *
   * @return  DataTemplateSnippet[]
   */
  public function searchInContent(TemplateSnippetSource $snippetSource, $needle)
  {
    $filesystemSnippets = $this->daoFilesystem->searchInContent($snippetSource, $needle);
    $doctrineSnippets = $this->daoDoctrine->searchInContent($snippetSource, $needle);
    $foundSnippets = $this->mergeSnippets($snippetSource, $filesystemSnippets, $doctrineSnippets);
    return $foundSnippets;
  }

  /**
   * @param array   $snippets
   * @param string  $orderDirection
   */
  protected function sortSnippetsByName(array &$snippets, $orderDirection)
  {
    if (!is_string($orderDirection)) {
      return;
    }
    $orderDirection = strtoupper($orderDirection);
    if ($orderDirection == 'ASC') {
      usort($snippets, function ($a, $b) {
        return strnatcasecmp($a->getName(), $b->getName());
      });
    } elseif ($orderDirection == 'DESC') {
      usort($snippets, function ($a, $b) {
        return (strnatcasecmp($a->getName(), $b->getName()) * -1);
      });
    }
  }

  /**
   * @param TemplateSnippetSource $snippetSource
   * @param DataTemplateSnippet[] $filesystemSnippets
   * @param DataTemplateSnippet[] $doctrineSnippets
   *
   * @return DataTemplateSnippet[]
   */
  protected function mergeSnippets(
      TemplateSnippetSource $snippetSource,
      array $filesystemSnippets,
      array $doctrineSnippets
  ) {
    $snippets = array();
    foreach ($filesystemSnippets as $fsSnippet) {
      $snippets[$fsSnippet->getId()] = $fsSnippet;
    }
    foreach ($doctrineSnippets as $daoSnippet) {
      $snippetId = $daoSnippet->getId();
      if (isset($snippets[$snippetId]) || $this->daoFilesystem->existsSnippet($snippetSource, $snippetId)) {
        $daoSnippet->setOverwritten(true);
      }
      $snippets[$snippetId] = $daoSnippet;
    }
    return array_values($snippets);
  }
}
