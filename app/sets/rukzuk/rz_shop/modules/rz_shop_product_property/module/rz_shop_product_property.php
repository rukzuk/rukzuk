<?php
namespace Rukzuk\Modules;

use Render\APIs\APIv1\HeadAPI;
use Render\APIs\APIv1\RenderAPI;
use Render\Unit;

/**
 * Class rz_shop_product_property
 * @package Rukzuk\Modules
 */
class rz_shop_product_property extends SimpleModule
{

  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    if ($this->isInsideProductList($renderApi, $unit)) {
      // TODO: remove global!
      global $currentProductPageId;
      $this->renderTeaserContent($renderApi, $unit, $moduleInfo, $currentProductPageId);
    } else {
      $pageId = $renderApi->getNavigation()->getCurrentPageId();
      if ($this->isProductPage($renderApi, $pageId)) {
        $this->renderTeaserContent($renderApi, $unit, $moduleInfo, $pageId);
      } elseif ($renderApi->isEditMode()) {
        // show error
        $i18n = new Translator($renderApi, $moduleInfo, $renderApi->getInterfaceLocale());
        echo HtmlTagBuilder::div(
          HtmlTagBuilder::button($i18n->translate('msg.moduleOnlyWorkingOnProductPages')
          )->set(array('style' => 'cursor: default;')))->set(array('class' => 'RUKZUKmissingInputHint'))->toString();
      }
    }
  }

  private function isInsideProductList($renderApi, $unit)
  {
    $teaserListUnit = $renderApi->getParentUnit($unit);
    while (isset($teaserListUnit) && $renderApi->getModuleInfo($teaserListUnit)->getId() !== 'rz_shop_product_list') {
      $teaserListUnit = $renderApi->getParentUnit($teaserListUnit);
    }

    return isset($teaserListUnit);
  }

  /**
   * @param RenderAPI $renderApi
   * @param $unit
   * @param $moduleInfo
   * @param string $pageId
   */
  private function renderTeaserContent($renderApi, $unit, $moduleInfo, $pageId)
  {
    $i18n = new Translator($renderApi, $moduleInfo, $renderApi->getLocale());
    $navigation = $renderApi->getNavigation();
    $page = $navigation->getPage($pageId);
    $type = $renderApi->getFormValue($unit, 'type');
    $htmlOutput = null;

    switch ($type) {
      case 'pageTitle':
        $htmlOutput = $this->getHeadlineTag($renderApi, $unit, $this->getPageTitle($page, $i18n), $this->getUrl($renderApi, $page));
        break;
      case 'description':
        $htmlOutput = $this->getTextTag($renderApi, $unit, $this->getDescription($page, $i18n), $this->getUrl($renderApi, $page));
        break;
      case 'date':
        $htmlOutput = $this->getDateTag($renderApi, $unit, $this->getDate($page));
        break;
      case 'link':
        $htmlOutput = $this->getLinkTag($renderApi, $unit, $this->getUrl($renderApi, $page));
        break;
      case 'image':
        $htmlOutput = $this->getMediaTag($renderApi, $unit, $moduleInfo, $this->getMediaId($page), $this->getPageTitle($page, $i18n), $this->getUrl($renderApi, $page));
        break;
      case 'price':
        $htmlOutput = $this->getPriceTag($renderApi, $this->getPrice($page), $i18n);
        break;
      case 'addcart':
        $htmlOutput = $this->getAddCartTag($renderApi, $unit, $moduleInfo, $page, $i18n);
        break;
    }
    if ($htmlOutput) {
      echo $htmlOutput->toString();
    }
  }

  private function getMediaTag($renderApi, $unit, $moduleInfo, $mediaId, $altText, $url)
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
    $return = $responsiveImageBuilder->getImageTag($image, $modifications, array('class' => 'imageModuleImg', 'title' => $api->getFormValue($unit, 'imageTitle'), 'alt' => $altText));
    if ($api->getFormValue($unit, 'enableImageLink')) {
      $return = new HtmlTagBuilder('a', array('href' => $url,), array($return));
    }

    return $return;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param $unit
   * @param $content
   * @param $url
   * @return HtmlTagBuilder
   */
  public function getHeadlineTag($renderApi, $unit, $content, $url)
  {
    $charLimit = $renderApi->getFormValue($unit, 'headlineCharLimit');
    if ($charLimit > 0) {
      $content = $this->trimText($content, $charLimit);
    }
    if ($renderApi->getFormValue($unit, 'enableHeadlineLink')) {
      $content = new HtmlTagBuilder('a', array('href' => $url), array($content));
    }

    return new HtmlTagBuilder($renderApi->getFormValue($unit, 'headlineHtmlElement'), array('class' => 'teaserHeadline'), array($content));
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param $unit
   * @param $content
   * @param $url
   * @return HtmlTagBuilder
   */
  public function getTextTag($renderApi, $unit, $content, $url)
  {
    $charLimit = $renderApi->getFormValue($unit, 'textCharLimit');
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

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param $unit
   * @param $content
   * @return null|HtmlTagBuilder
   */
  public function getDateTag($renderApi, $unit, $content)
  {
    $return = null;
    $timestamp = (int)$content;
    if ($timestamp > 0) {
      $datetime = strftime('%F', $timestamp);
      $datetimeString = strftime($renderApi->getFormValue($unit, 'dateFormat'), $timestamp);
      $return = new HtmlTagBuilder('time', array('datetime' => $datetime, //insert pubdate attribute?
        'class' => 'teaserDate'), array($datetimeString));
    }

    return $return;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param $unit
   * @param $url
   * @return HtmlTagBuilder
   */
  public function getLinkTag($renderApi, $unit, $url)
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

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param $unit
   * @param \Render\APIs\APIv1\MediaImage $image
   * @return array
   */
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

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param float                        $price
   * @param Translator                   $i18n
   *
   * @return HtmlTagBuilder
   */
  private function getPriceTag($renderApi, $price, $i18n)
  {
    if (empty($price) && $renderApi->isEditMode()) {
      $price = 42.23;
    }
    return new HtmlTagBuilder('span', array('class' => 'pd-price'), array(
      $this->formatCurrency($renderApi, $i18n, floatval($price)))
    );
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param Unit $unit
   * @param $moduleInfo
   * @param \Render\APIs\APIv1\Page $productPage
   * @param Translator $i18n
   *
   * @return HtmlTagBuilder|void
   */
  private function getAddCartTag($renderApi, $unit, $moduleInfo, $productPage, $i18n)
  {
    $shopSettings = $renderApi->getWebsiteSettings('rz_shop');
    $cartPageId = $shopSettings['cartPage'];
    if (!$cartPageId) {
      return HtmlTagBuilder::div(
        HtmlTagBuilder::button($i18n->translate('msg.noCartPage')
        )->set(array('style' => 'cursor: default;')))->set(array('class' => 'RUKZUKmissingInputHint'));
    }

    $cartText = $i18n->translateInput($renderApi->getFormValue($unit, 'cartText'));
    $cartUrl = $renderApi->getNavigation()->getPage($cartPageId)->getUrl();
    return $this->displayCartButton($cartText, $productPage, $cartUrl, ($renderApi->isEditMode() || $renderApi->isTemplate()), $i18n);
  }

  /**
   * @param $text
   * @param \Render\APIs\APIv1\Page $productPage
   * @param string $cartUrl
   * @param bool $preventSubmit
   * @param Translator $i18n
   *
   * @return HtmlTagBuilder
   */
  private function displayCartButton($text, $productPage, $cartUrl, $preventSubmit, $i18n)
  {
    // form
    $form = new HtmlTagBuilder('form', array('action' => $cartUrl, 'method' => 'POST'),
      array(
        new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'productPageId', 'value' => $productPage->getPageId())),
        new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'addToCart')),
      )
    );

    // variants select
    if ($this->isVariantsEnabled($productPage)) {
      $form->append($this->buildVariantSelect(explode("\n", $this->getPageAttribute($productPage, 'variants'))));
    }

    // cart button
    $cartBtn = new HtmlTagBuilder('button', array('type' => 'submit', 'class' => 'pd-addToCartButton'), array($text));
    if ($preventSubmit) {
      $cartBtn->addClass('pd-preventSubmit');
      $cartBtn->set('onClick', 'alert("' . $i18n->translate('msg.editTplModeError') . '"); return false;');
    }
    $form->append($cartBtn);

    return $form;
  }

  /**
   * Returns the shop currency as ISO4217 (3-letter code, e.g EUR, USD, CAD, HKD, CHF)
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @return string
   */
  private function getShopCurrency($api)
  {
    $websiteSettings = $api->getWebsiteSettings('rz_shop');
    return isset($websiteSettings['currency']) ? $websiteSettings['currency'] : 'EUR';
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param Translator                   $translator
   * @param float                        $amount
   *
   * @return string
   */
  private function formatCurrency($api, $translator, $amount)
  {
    return $translator->formatCurrency($amount, $this->getShopCurrency($api));
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @param $property
   * @return mixed
   */
  private function getPageAttribute($page, $property)
  {
    $attributes = $page->getPageAttributes();
    return isset($attributes[$property]) ? $attributes[$property] : null;
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @return bool
   */
  private function isVariantsEnabled($page)
  {
    return $this->getPageAttribute($page, 'enableVariants') === true &&
    trim($this->getPageAttribute($page, 'variants')) != '';
  }

  /**
   * @param $variants
   * @return HtmlTagBuilder
   */
  private function buildVariantSelect($variants)
  {
    $variantSelect = new HtmlTagBuilder('select', array('name' => 'variant', 'class' => 'pd-addToCartVariants'));
    foreach ($variants as $variant) {
      $variantSelect->append(new HtmlTagBuilder('option', array('value' => $variant), array($variant)));
    }
    return $variantSelect;
  }

  private function isProductPage($renderApi, $pageId = null)
  {
    $navigation = $renderApi->getNavigation();
    $pageId = is_null($pageId) ? $navigation->getCurrentPageId() : $pageId;
    $pageType = $navigation->getPage($pageId)->getPageType();
    return ($pageType == 'rz_shop_product');
  }

  private function isTemplateId($pageId)
  {
    return (substr($pageId, 0, 4) === 'TPL-');
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @param Translator $i18n
   * @return mixed
   */
  private function getPageTitle($page, $i18n)
  {
    return $this->isTemplateId($page->getPageId()) ? $i18n->translate('placeholder.pageTitle') : $page->getTitle();
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @param Translator $i18n
   * @return mixed
   */
  private function getDescription($page, $i18n)
  {
    return $this->isTemplateId($page->getPageId()) ?
      $i18n->translate('placeholder.pageDescription') . ' - Lorem ipsum dolor sit amet, eos ea soleat causae.' :
      $page->getDescription();
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @return string
   */
  private function getDate($page)
  {
    $date = $this->isTemplateId($page->getPageId()) ? '01/01/1970' : $page->getDate();
    return $date;
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @return mixed
   */
  private function getMediaId($page)
  {
    return $page->getMediaId();
  }

  /**
   * @param \Render\APIs\APIv1\Page $page
   * @return mixed
   */
  private function getPrice($page)
  {
    if ($this->isTemplateId($page->getPageId())) {
      return 42.23;
    }
    return $this->getPageAttribute($page, 'price');
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\APIs\APIv1\Page $page
   * @return string
   */
  private function getUrl($renderApi, $page)
  {
    return $renderApi->isEditMode() ? 'javascript:void(0);' : $page->getUrl();
  }
}
