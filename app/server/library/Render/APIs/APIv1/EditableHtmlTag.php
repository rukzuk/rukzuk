<?php


namespace Render\APIs\APIv1;

use Render\APIs\APIv1\MediaItemNotFoundException;

class EditableHtmlTag
{
  const REGEX_LINK = '/(<\s*a\s+[^>]*?data-cms-link-type\s*=\s*["\'](?:internalPage|internalMedia|internalMediaDownload|mail|external)["\'][^>]*?>)/i';
  const REGEX_DATA_ATTRIBUTE = '/(\A| )data-cms-(link|link-type|link-anchor)\s*=\s*("([^"]*)"|\'([^\']*)\')/i';
  const REGEX_HREF = '/(\A| )href\s*=\s*("[^"]*"|\'[^\']*\')/i';

  /**
   * @var RenderAPI
   */
  private $renderAPI;

  /**
   * @param RenderAPI $renderAPI
   */
  public function __construct(RenderAPI $renderAPI)
  {
    $this->renderAPI = $renderAPI;
  }

  /**
   * Returns the given html tag with the content for the WYSIWYG editor.
   * All links at content html will be fixed. All helper attributes will
   * be removed if the current renderings happens inside of the rukzuk
   * cms edit mode.
   *
   * @param mixed  $key         key of the requested form value
   * @param string $tag         tag name that will be created around the editable html code
   * @param string $attributes  attributes for the created tag
   * @param string $content     editable html code
   *
   * @return string
   */
  public function getEditableTag($key, $tag, $attributes, $content)
  {
    $allAttributes = array();
    if (!empty($attributes)) {
      $allAttributes[] = $attributes;
    }
    if ($this->isEditMode()) {
      $allAttributes[] = 'data-cms-editable="'.$key.'"';
    }

    return sprintf(
        '<%1$s %2$s>%3$s</%1$s>',
        (empty($tag) ? 'div' : $tag),
        implode(' ', $allAttributes),
        $this->getHtmlWithReplacedLinks($content)
    );
  }

  /**
   * @param string $html
   *
   * @return mixed
   */
  protected function getHtmlWithReplacedLinks($html)
  {
    return preg_replace_callback(
        self::REGEX_LINK,
        array($this, 'replaceLinkTagCallback'),
        $html
    );
  }

  /**
   * Returns the replaced link tag
   *
   * @param $matches
   *
   * @return string
   */
  protected function replaceLinkTagCallback($matches)
  {
    $tagCode = $matches[1];

    $cmsDataAttributes = $this->findCmsDataAttributes($tagCode);
    $newHref = $this->getNewLinkHref($cmsDataAttributes);
    $tagCode = $this->getLinkWithNewHref($tagCode, htmlentities($newHref, ENT_COMPAT, 'UTF-8', false));

    if (!$this->isEditMode()) {
      // removed cms-data attributes at none edit mode
      $tagCode = $this->removeCmsDataAttributes($tagCode);
    }

    return $tagCode;
  }

  /**
   * @param $code
   *
   * @return array
   */
  protected function findCmsDataAttributes($code)
  {
    $cmsData = array(
      'link-type' => null,
      'link' => null,
      'link-anchor' => null,
    );
    if (preg_match_all(self::REGEX_DATA_ATTRIBUTE, $code, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $nextAttribute) {
        $cmsData[strtolower($nextAttribute[2])] = '';
        if (isset($nextAttribute[4])) {
          $cmsData[strtolower($nextAttribute[2])] .= $nextAttribute[4];
        }
        if (isset($nextAttribute[5])) {
          $cmsData[strtolower($nextAttribute[2])] .= $nextAttribute[5];
        }
      }
    }
    return $cmsData;
  }

  /**
   * @param $cmsDataAttributes
   *
   * @return string
   */
  protected function getNewLinkHref($cmsDataAttributes)
  {
    $linkType = strtolower($cmsDataAttributes['link-type']);
    if ($linkType == 'internalpage') {
      // get internal page url
      $newHref = $this->getPageUrl($cmsDataAttributes['link']);
    } elseif ($linkType == 'internalmediadownload') {
      // get media item download url
      $newHref = $this->getMediaItemDownloadUrl($cmsDataAttributes['link']);
    } elseif ($linkType == 'internalmedia') {
      // get media item stream url
      $newHref = $this->getMediaItemStreamUrl($cmsDataAttributes['link']);
    } elseif ($linkType == 'mail') {
      $newHref = 'mailto:'.$cmsDataAttributes['link'];
    } else {
      $newHref = $cmsDataAttributes['link'];
    }

    // add anchor
    if (!empty($cmsDataAttributes['link-anchor'])) {
      $newHref .= $cmsDataAttributes['link-anchor'];
    }

    if (empty($newHref)) {
      $newHref = '#';
    }

    return $newHref;
  }

  /**
   * @param $code
   *
   * @return mixed
   */
  protected function removeCmsDataAttributes($code)
  {
    return preg_replace(self::REGEX_DATA_ATTRIBUTE, '', $code);
  }

  /**
   * @param $code
   * @param $newHref
   *
   * @return mixed
   */
  protected function getLinkWithNewHref($code, $newHref)
  {
    return preg_replace(self::REGEX_HREF, ' href="'.$newHref.'"', $code);
  }

  /**
   * @return \Render\APIs\APIv1\RenderAPI
   */
  protected function getRenderAPI()
  {
    return $this->renderAPI;
  }

  /**
   * @return bool
   */
  protected function isEditMode()
  {
    return $this->getRenderAPI()->isEditMode();
  }

  /**
   * @param string $pageId
   *
   * @return string
   */
  protected function getPageUrl($pageId)
  {
    return $this->getRenderAPI()->getNavigation()->getPage($pageId)->getUrl();
  }

  /**
   * @param string $mediaId
   *
   * @return string
   */
  protected function getMediaItemDownloadUrl($mediaId)
  {
    try {
      return $this->getRenderAPI()->getMediaItem($mediaId)->getDownloadUrl();
    } catch (MediaItemNotFoundException $_e) {
      return '';
    }
  }

  /**
   * @param string $mediaId
   *
   * @return string
   */
  protected function getMediaItemStreamUrl($mediaId)
  {
    try {
      return $this->getRenderAPI()->getMediaItem($mediaId)->getUrl();
    } catch (MediaItemNotFoundException $_e) {
      return '';
    }
  }
}
