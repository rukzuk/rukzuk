<?php
namespace Cms\Service;

use Cms\Exception as CmsException;
use Cms\Service\Base\Plain as PlainServiceBase;
use Seitenbau\Registry as Registry;
use Seitenbau\RandomGenerator as RandomGenerator;
use Cms\Business\Render as BusinessRender;
use Dual\Render\RenderContext as RenderContext;

/**
 * Stellt die Service-Logik fuer Indexer zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Service
 */
class Indexer extends PlainServiceBase
{
  /**
   * @param  string $websiteId
   * @return string
   */
  public function indexWebsite($websiteId)
  {
    $websiteService = new Website('Website');

    if (!$websiteService->existsWebsiteAlready($websiteId)) {
      throw new CmsException('602', __METHOD__, __LINE__);
    }

    // Zum Rendern muss die Business-Schicht verwendet werden
    $renderBusiness = new BusinessRender('Render');

    $modulService = new Modul('Modul');
    $pageService = new Page('Page');
    $allPageIds = $pageService->getIdsByWebsiteId($websiteId);
    $indexFileOfWebsite = $this->getIndexFileForWebsite($websiteId);
    
    if (is_array($allPageIds) && count($allPageIds) > 0) {
      if (file_exists($indexFileOfWebsite)) {
        $index = \Zend_Search_Lucene::open($indexFileOfWebsite);

        $numberOfIndexedDocuments = $index->numDocs();

        for ($id = 0; $id < $numberOfIndexedDocuments; ++$id) {
          if (!$index->isDeleted($id)) {
            $document = $index->delete($id);
          }
        }

      } else {
        $index = \Zend_Search_Lucene::create($indexFileOfWebsite);
      }
      
      foreach ($allPageIds as $pageId) {
        $pageContent = $this->getPageContent($websiteId, $pageId);

        if ($this->isStoreContentEnabled()) {
          $document = \Zend_Search_Lucene_Document_Html::loadHTML(
              $pageContent,
              true,
              'UTF-8'
          );
        } else {
          $document = \Zend_Search_Lucene_Document_Html::loadHTML(
              $pageContent,
              false,
              'UTF-8'
          );
        }

        $document->addField(
            \Zend_Search_Lucene_Field::unIndexed('md5', md5($pageContent))
        );
        $document->addField(
            \Zend_Search_Lucene_Field::unIndexed('pageId', $pageId)
        );
        $index->addDocument($document);
      }

      $index->commit();
      $index->optimize();
      unset($index);
    }
    return $indexFileOfWebsite;
  }
  
  /**
   * @param  string $websiteId
   * @return string
   */
  private function getIndexFileForWebsite($websiteId)
  {
    $config = Registry::getConfig();
    return $config->indexing->basedir . DIRECTORY_SEPARATOR . $websiteId;
  }
  /**
   * @return boolean
   */
  private function isStoreContentEnabled()
  {
    $config = Registry::getConfig();
    $storeContent = $config->indexing->store->content;
    
    if ($storeContent === "true" ||
        $storeContent === true   ||
        $storeContent === "1"    ||
        $storeContent === 1) {
      return true;
    }
    return false;
  }
  
  /**
   * @return boolean
   */
  public function isIndexingEnabled()
  {
    $config = Registry::getConfig();
    $indexingEnabled = $config->indexing->enabled;
    
    if ($indexingEnabled === "true" ||
        $indexingEnabled === true   ||
        $indexingEnabled === "1"    ||
        $indexingEnabled === 1) {
      return true;
    }
    return false;
  }

  /**
   * @return boolean
   */
  public function isLuceneIndexer()
  {
    $config = Registry::getConfig();
    if ($config->indexing->indexer === 'Lucene') {
      return true;
    }
    return false;
  }

  /**
   * @param  string $websiteId
   * @param  string $pageId
   * @return string Content der Page
   */
  function getPageContent(
      $websiteId,
      $pageId,
      $mode = RenderContext::MODE_PREVIEW
  ) {
    // init
    $content      = null;
    $maxRedirects = 0;
    $timeout      = 5;

    // Ticketurl ermitteln
    $url = $this->createTicketUrl(
        $websiteId,
        'render',
        'page',
        array(
        'websiteid' => $websiteId,
        'pageid'    => $pageId,
        'mode'      => $mode
        )
    );

    // Einlesen ueber curl
    if (function_exists("curl_init") && function_exists("curl_setopt") &&
        function_exists("curl_exec") && function_exists("curl_close") &&
        $ch = curl_init() ) {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_MAXREDIRS, $maxRedirects);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      $content = @curl_exec($ch);
      curl_close($ch);
    } // Einlesen ueber file_get_contents
    elseif (function_exists('file_get_contents')) {
      $opts = array('http' =>
                array(
                  'method'        => 'GET',
                  'max_redirects' => $maxRedirects,
                  'timeout'       => $timeout
                ) );
                $context = stream_context_create($opts);
                $content = @file_get_contents($url, false, $context);
    }

    // Content zurueckgeben
    return $content;
  }

  /**
   * @param  string $websiteId
   * @param  string $url
   * @return String
   */
  private function createTicketUrl($websiteId, $controler, $action, $params)
  {
    // init
    $config = Registry::getConfig();

    // Params als JSON-String
    $paramsAsJson = \Zend_Json::encode($params);
    $params = array(
        $config->request->parameter => $paramsAsJson,
    );
    
    // Ticket aus Render Url erstellen
    $credential = null;
    if ($config->indexing->accessticket->authentication) {
      $credential = array (
        'username' => RandomGenerator::generateString(10),
        'password' => RandomGenerator::generateString(10),
      );
    }
    $ticketUrl = $this->getService('Ticket')->createTicketUrl(
        $websiteId,
        false, // Forwarding
        true,
        array(
        'controller' => $controler,
        'action' => $action,
        'params' => $params,
        ),
        $config->indexing->accessticket->ticketLifetime,
        $config->indexing->accessticket->remainingCalls,
        $config->indexing->accessticket->sessionLifetime,
        $credential,
        $credential
    );
    
    return $ticketUrl;
  }
}
