<?php


namespace Cms\Dao;

use Cms\Dao\TemplateSnippet\TemplateSnippetSource;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

interface TemplateSnippet
{
  /**
   * returns all TemplateSnippets of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $orderDirection
   *
   * @return  DataTemplateSnippet[]
   */
  public function getAll(TemplateSnippetSource $snippetSource, $orderDirection = null);

  /**
   * returns the specified "Template Snippets" of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   *
   * @return  DataTemplateSnippet[]
   */
  public function getByIds(TemplateSnippetSource $snippetSource, array $ids);

  /**
   * return the TemplateSnippets of the given id and Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return  DataTemplateSnippet
   */
  public function getById(TemplateSnippetSource $snippetSource, $id);

  /**
   * deletes the TemplateSnippets of the given ids and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   */
  public function deleteByIds(TemplateSnippetSource $snippetSource, array $ids);

  /**
   * deletes the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   */
  public function delete(TemplateSnippetSource $snippetSource, $id);

  /**
   * deletes all TemplateSnippets of the given website id
   *
   * @param TemplateSnippetSource $snippetSource
   */
  public function deleteByWebsiteId(TemplateSnippetSource $snippetSource);

  /**
   * creates a new TemplateSnippet
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   */
  public function create(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet);

  /**
   * updates the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   */
  public function update(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet);

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
  );

  /**
   * Checks if there is a template snippet under the given TemplateSnippet Id and Website Id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   */
  public function existsSnippet(TemplateSnippetSource $snippetSource, $id);

  /**
   * search over the TemplateSnippets content and returns the found TemplateSnippets
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $needle
   *
   * @return  DataTemplateSnippet[]
   */
  public function searchInContent(TemplateSnippetSource $snippetSource, $needle);
}
