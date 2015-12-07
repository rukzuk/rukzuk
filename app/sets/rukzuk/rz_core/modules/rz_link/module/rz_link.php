<?php
namespace Rukzuk\Modules;

class rz_link extends SimpleModule
{
  const FALLBACK_URL = '#';

  /**
   * @param HtmlTagBuilder $tag
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  protected function modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo)
  {
    // use a only if not in edit mode (to be able to use inline editor)
    if (!$renderApi->isEditMode()) {
      $tag->setTagName('a');
      $tag->set('href', $this->geturl($renderApi, $unit));
      if ($renderApi->getFormValue($unit, 'openNewWindow')) {
        $tag->set('target', '_blank');
      }
    }

    // add title
    $linkTitle = $renderApi->getFormValue($unit, 'linkTitle');
    if (!empty($linkTitle)) {
      $tag->set('title', $linkTitle);
    }

    // add active/current classes when linking to internal page
    if ($renderApi->getFormValue($unit, 'linkType') == 'page') {
      $pageId = $renderApi->getFormValue($unit, 'pageId');
      if (!empty($pageId)) {
        $nav = $renderApi->getNavigation();
        $navIds = $this->getNavigatorIds($nav);
        if ($this->isPageActive($pageId, $navIds)) {
          $tag->addClass('linkPageActive');
        }
        if ($pageId == $nav->getCurrentPageId()) {
          $tag->addClass('linkPageCurrent');
        }
      }
    }
  }

  /**
   * @param string $pageId
   * @param array  $navigatorIds
   *
   * @return bool
   */
  protected function isPageActive($pageId, array &$navigatorIds)
  {
    return in_array($pageId, $navigatorIds);
  }

  /**
   * Returns navigator ids (breadcrumbtrail) of current page
   *
   * @param \Render\APIs\APIv1\Navigation $navigation
   *
   * @return array
   */
  protected function getNavigatorIds($navigation)
  {
    $currentPage = $navigation->getPage($navigation->getCurrentPageId());
    $navigatorIds = $currentPage->getParentIds();
    $navigatorIds[] = $currentPage->getPageId();
    return $navigatorIds;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $linkTextPos = $renderApi->getFormValue($unit, 'linkTextPosition');
    if ($linkTextPos == 'top') {
      $this->renderLinkText($renderApi, $unit);
    }

    $renderApi->renderChildren($unit);

    if ($linkTextPos == 'bottom') {
      $this->renderLinkText($renderApi, $unit);
    }
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   */
  protected function renderLinkText($renderApi, $unit)
  {
    if ($renderApi->getFormValue($unit, 'enableLinkText')
      && $renderApi->getFormValue($unit, 'linkText') != '') {
      echo $renderApi->getEditableTag($unit, 'linkText', 'span', 'class="linkText"');
    }
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return string
   */
  protected function getUrl($renderApi, $unit)
  {
    $url = self::FALLBACK_URL;
    switch ($renderApi->getFormValue($unit, 'linkType')) {
      case 'page':
        $url = $this->getInternalUrl($renderApi, $unit);
        break;

      case 'external':
        $url = $this->getExternalUrl($renderApi, $unit);
        break;

      case 'download':
        $url = $this->getDownloadUrl($renderApi, $unit);
        break;

      case 'mailto':
        $url = $this->getMailToUrl($renderApi, $unit);
        break;
    }

    if (empty($url)) {
      return self::FALLBACK_URL;
    } else {
      return $url;
    }
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return string
   */
  protected function getInternalUrl($renderApi, $unit)
  {
    $nav = $renderApi->getNavigation();
    $internalUrl = $nav->getPage($renderApi->getFormValue($unit, 'pageId'))->getUrl();
    $anchor = $renderApi->getFormValue($unit, 'pageAnchor', '');
    if (substr($anchor, 0, 1) === '#') {
      $internalUrl .= htmlentities($anchor, ENT_QUOTES, 'UTF-8');
    }
    return $internalUrl;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return null|string
   */
  protected function getExternalUrl($renderApi, $unit)
  {
    $externalUrl = $renderApi->getFormValue($unit, 'externalUrl');
    if (empty($externalUrl)) {
      return null;
    } else {
      return $externalUrl;
    }
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return null|string
   */
  protected function getDownloadUrl($renderApi, $unit)
  {
    $downloadId = $renderApi->getFormValue($unit, 'downloadId');
    if (empty($downloadId)) {
      return null;
    }
    try {
      $mediaItem = $renderApi->getMediaItem($downloadId);
      return $renderApi->getFormValue($unit, 'downloadSaveDialog') ? $mediaItem->getDownloadUrl() : $mediaItem->getUrl();
    } catch (\Exception $doNothing) {
    }
    return null;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return null|string
   */
  protected function getMailToUrl($renderApi, $unit)
  {
    $mailTo = $renderApi->getFormValue($unit, 'mailtoEmail');
    if (empty($mailTo)) {
      return null;
    } else {
      return 'mailto:'.$mailTo;
    }
  }
}
