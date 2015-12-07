<?php
namespace Rukzuk\Modules;
class rz_page_property extends SimpleModule
{

  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    if ($this->isInsideTeaserList($renderApi, $unit)) {
      $this->renderTeaserContent($renderApi, $unit, $moduleInfo);
    } else {
      global $currentTeaserPageId;
      $navigation = $renderApi->getNavigation();
      $currentTeaserPageId = $navigation->getCurrentPageId();
      $this->renderTeaserContent($renderApi, $unit, $moduleInfo);
    }
  }

  private function isInsideTeaserList($renderApi, $unit)
  {
    $teaserListUnit = $renderApi->getParentUnit($unit);
    while (isset($teaserListUnit) && $renderApi->getModuleInfo($teaserListUnit)->getId() !== 'rz_page_list') {
      $teaserListUnit = $renderApi->getParentUnit($teaserListUnit);
    }

    return isset($teaserListUnit);
  }

  private function renderTeaserContent($renderApi, $unit, $moduleInfo)
  {
    // TODO find better solution to communicate with rz_teaser_list
    global $currentTeaserPageId;
    $navigation = $renderApi->getNavigation();
    $page = $navigation->getPage($currentTeaserPageId);
    $url = $renderApi->isEditMode() ? 'javascript:void(0);' : $page->getUrl();
    $type = $renderApi->getFormValue($unit, 'type');
    $htmlOutput = null;
    switch ($type) {
      case 'pageTitle':
        $i18n = new Translator($renderApi, $moduleInfo);
        $pageTitle = substr($page->getPageId(), 0, 4) === 'TPL-' ? $i18n->translate('placeholder.pageTitle') : $page->getTitle();
        $htmlOutput = $this->getHeadlineTag($renderApi, $unit, $moduleInfo ,$pageTitle, $url);
        break;
      case 'description':
        $htmlOutput = $this->getTextTag($renderApi, $unit, $moduleInfo, $page->getDescription(), $url);
        break;
      case 'date':
        $htmlOutput = $this->getDateTag($renderApi, $unit, $moduleInfo, $page->getDate());
        break;
      case 'link':
        $htmlOutput = $this->getLinkTag($renderApi, $unit, $moduleInfo, $url);
        break;
      case 'image':
        $htmlOutput = $this->getMedia($renderApi, $unit, $moduleInfo, $page->getMediaId(), $page->getTitle(), $url);
        break;
    }
    if ($htmlOutput) {
      echo $htmlOutput->toString();
    }
  }

  private function getMedia($renderApi, $unit, $moduleInfo, $mediaId, $altText, $url)
  {
    return $this->getResponsiveImageTag($renderApi, $unit, $moduleInfo, $mediaId, $altText, $url);
  }

  public function getResponsiveImageTag($api, $unit, $moduleInfo, $mediaId, $altText, $url)
  {
    $image = null;
    $modifications = array();
    if (!empty($mediaId)) {
      try {
        $image = $api->getMediaItem($mediaId)->getImage();
        $modifications = $this->getImageModifications($api, $unit, $image);
      } catch (\Exception $e) {
        $image = null;
        $modifications = array();
      }
    }
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit, $moduleInfo);
    $return = $responsiveImageBuilder->getImageTag($image, $modifications, array('class' => 'teaserImage', 'title' => $api->getFormValue($unit, 'imageTitle'), 'alt' => $altText));
    if ($api->getFormValue($unit, 'enableImageLink')) {
      $return = new HtmlTagBuilder('a', array('href' => $url,), array($return));
    }

    return $return;
  }

  public function getHeadlineTag($renderApi, $unit, $moduleInfo, $content, $url)
  {
    $charLimit = $renderApi->getFormValue($unit, 'headlineCharLimit');
    if (empty($content) && $renderApi->isEditMode()) {
      $i18n = new Translator($renderApi, $moduleInfo);
      $content = $i18n->translate('placeholder.pageTitle');
    }
    if ($charLimit > 0) {
      $content = $this->trimText($content, $charLimit);
    }
    if ($renderApi->getFormValue($unit, 'enableHeadlineLink')) {
      $content = new HtmlTagBuilder('a', array('href' => $url), array($content));
    }

    return new HtmlTagBuilder($renderApi->getFormValue($unit, 'headlineHtmlElement'), array('class' => 'teaserHeadline'), array($content));
  }

  public function getTextTag($renderApi, $unit, $moduleInfo, $content, $url)
  {
    $charLimit = $renderApi->getFormValue($unit, 'textCharLimit');
    if (empty($content) && $renderApi->isEditMode()) {
      $i18n = new Translator($renderApi, $moduleInfo);
      $description = $i18n->translate('placeholder.pageDescription');
      $content = $description . ' - Lorem ipsum dolor sit amet, eos ea soleat causae. Pro elitr eleifend prodesset ad, etiam volutpat no per, vim ea consul denique. Ullum lobortis evertitur ne vim, has audire incorrupte theophrastus at. Labitur vivendum electram pro et, sed movet accusata gloriatur at. Amet oratio repudiandae cu vis.';
    }
    if ($charLimit > 0) {
      $content = $this->trimText($content, $charLimit);
    }
    // add space if link will get appended
    if ($renderApi->getFormValue($unit, 'enableTextLink')) {
      $content .= ' ';
    }
    $return = new HtmlTagBuilder('p', array('class' => 'teaserText'), array($content));
    if ($renderApi->getFormValue($unit, 'enableTextLink')) {
      $return->append(new HtmlTagBuilder('a', array('href' => $url, 'class' => 'teaserTextLink'), array($renderApi->getFormValue($unit, 'textLinkLabel'))));
    }

    return $return;
  }

  public function getDateTag($renderApi, $unit, $moduleInfo, $content)
  {
    $return = null;
    if (empty($content) && $renderApi->isEditMode()) {
      $content = '01/01/1970';
    }
    $timestamp = (int)$content;
    if ($timestamp > 0) {
      $datetime = strftime('%F', $timestamp);
      $datetimeString = strftime($renderApi->getFormValue($unit, 'dateFormat'), $timestamp);
      $return = new HtmlTagBuilder('time', array('datetime' => $datetime, //insert pubdate attribute?
        'class' => 'teaserDate'), array($datetimeString));
    }

    return $return;
  }

  public function getLinkTag($renderApi, $unit, $moduleInfo, $url)
  {
    return new HtmlTagBuilder('a', array('href' => $url, 'class' => 'teaserLink'), array($renderApi->getFormValue($unit, 'linkLabel')));
  }

  /**
   * trims text to a space then adds ellipses if desired
   * by http://www.ebrueggeman.com/blog/abbreviate-text-without-cutting-words-in-half
   *
   * @param string $input text to trim
   * @param int $length in characters to trim to
   * @param bool $ellipses if ellipses (...) are to be added
   *
   * @return string
   */
  private function trimText($input, $length, $ellipses = true)
  {
    //no need to trim, already shorter than trim length
    if (strlen($input) <= $length) {
      return $input;
    }
    //find last space within length
    $lastSpace = strrpos(substr($input, 0, $length), ' ');
    $trimmedText = substr($input, 0, $lastSpace);
    //add ellipses
    if ($ellipses) {
      $trimmedText .= '…';
    }

    return $trimmedText;
  }

  private function getImageModifications($api, $unit, $image)
  {
    $modifications = array();
    $width = (int)$image->getWidth();
    $height = (int)$image->getHeight();
    $globalHeightPercent = str_replace('%', '', $api->getFormValue($unit, 'imgHeight'));
    if ($globalHeightPercent == 0) {
      $heightPercent = $height / $width * 100;
    } else {
      $heightPercent = $globalHeightPercent;
    }
    $cropHeight = ($width * (int)$heightPercent) / 100;
    $modifications['resize'] = array('width' => $width, 'height' => $cropHeight);
    // apply quality
    if ($api->getFormValue($unit, 'enableImageQuality')) {
      $modifications['quality'] = $api->getFormValue($unit, 'imageQuality');
    }

    return $modifications;
  }

}
