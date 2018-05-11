<?php
namespace Rukzuk\Modules;

// composer auto loader
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/src/CheckoutForm.php');
require_once(__DIR__.'/src/CartView.php');
require_once(__DIR__.'/src/ShopSettings.php');
require_once(__DIR__.'/src/CartWithShipping.php');
require_once(__DIR__.'/src/ShopModeStrategy.php');

use \Render\APIs\APIv1\RenderAPI;
use \Render\APIs\APIv1\CSSAPI;
use \Render\APIs\APIv1\HeadAPI;
use \Render\Unit;
use \Render\ModuleInfo;

use \Cart\Cart;
use \Cart\Storage\SessionStore;
use \Cart\CartItem;

use \rz_shop_cart\CartView;
use \rz_shop_cart\CartWithShipping;
use \rz_shop_cart\CheckoutFrom;
use \rz_shop_cart\ShopSettings;
use \rz_shop_cart\ShopModeStrategy;
use \rz_shop_cart\ShopModeStrategy\ShopModeResponse;

/**
 * Shopping Cart with Checkout Handling
 *
 * @package Rukzuk\Modules
 */
class rz_shop_cart extends SimpleModule
{
  /**
   * @var array
   */
  protected $unitContext = array();

  /**
   * @param CSSAPI     $api
   * @param Unit       $unit
   * @param ModuleInfo $moduleInfo
   *
   * @return array
   */
  public function provideUnitData($api, $unit, $moduleInfo)
  {
    $arr = parent::provideUnitData($api, $unit, $moduleInfo);

    $actionAlreadyProcessed = $this->getUnitContext($api, $unit, 'alreadyProcessed', false);
    if (!$actionAlreadyProcessed) {
      $shopModeResponse = $this->processAction($api, $unit, $moduleInfo);
      $this->handleShopModeResponse($api, $unit, $shopModeResponse);
      if ($shopModeResponse->hasRedirect()) {
        $arr['redirect'] = array('url' => $shopModeResponse->getRedirectUrl());
      }
    }

    return $arr;
  }

  /**
   * @param RenderAPI  $renderApi
   * @param Unit       $unit
   * @param ModuleInfo $moduleInfo
   *
   * @throws \Exception
   */
  protected function renderContent($renderApi, $unit, $moduleInfo)
  {
    $i18n = $this->getTranslator($renderApi, $moduleInfo);
    $settings = $this->getShopSettings($renderApi, $i18n);
    $cart = $this->createCart($renderApi, $unit, $settings, $i18n);
    $viewMode = $this->getUnitContext($renderApi, $unit, 'view', 'showCart');

    $wrapper = new HtmlTagBuilder('div');
    $this->showErrors($renderApi, $unit, $wrapper);
    switch ($viewMode) {
      case ShopModeResponse::VIEW_MODE_CART:
        $this->showCart($wrapper, $settings, $cart, $i18n);
        break;

      case ShopModeResponse::VIEW_MODE_CHECKOUT:
        $checkout = $this->createCheckoutForm($unit, $settings, $i18n);
        $checkout->loadFromStore();
        $this->showCheckout($wrapper, $settings, $checkout, $cart, $i18n);
        break;

      case ShopModeResponse::VIEW_MODE_SUCCESS:
        $this->showSuccess($wrapper, $settings);
        break;
    }

    echo $wrapper->toString();

    $renderApi->renderChildren($unit);
  }
  /**
   * @param HtmlTagBuilder   $wrapper
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   * @param Translator       $i18n
   */
  protected function showCheckout($wrapper, $settings, $checkout, $cart, $i18n)
  {
    // load from store
    $checkout->loadFromStore();
    $checkoutWrapper = $this->createCheckoutWrapper()->appendHtml($checkout->toHtml());

    // render cart
    $wrapper->append($this->renderCart($settings, $cart, $i18n, false));
    $wrapper->append($checkoutWrapper);
  }

  /**
   * @param HtmlTagBuilder $wrapper
   * @param ShopSettings   $settings
   */
  protected function showSuccess($wrapper, $settings)
  {
    // output thanks msg
    $wrapper->append(new HtmlTagBuilder('div', array('class' => 'thanksMessage'), array($settings->getThanksMessage())));
  }

  /**
   * @param HtmlTagBuilder   $wrapper
   * @param ShopSettings     $settings
   * @param CartWithShipping $cart
   * @param Translator       $i18n
   */
  protected function showCart($wrapper, $settings, $cart, $i18n)
  {
    $wrapper->append($this->renderCart($settings, $cart, $i18n, true));
    $wrapper->append($this->cartButtonBar($cart, $i18n));
  }


  /**
   * Cart Table
   *
   * @param ShopSettings     $settings
   * @param CartWithShipping $cart
   * @param Translator       $i18n
   * @param bool             $edit - show edit input fields
   *
   * @return HtmlTagBuilder
   */
  protected function renderCart($settings, $cart, $i18n, $edit = false)
  {
    $cartView = new CartView($settings, $i18n);
    return $cartView->renderCart($cart, $edit);
  }


  /**
   * @param Cart       $cart
   * @param Translator $i18n
   *
   * @return HtmlTagBuilder
   */
  protected function cartButtonBar($cart, $i18n)
  {
    // Checkout button in Cart Mode
    $checkoutForm = new HtmlTagBuilder('form', array('action' => '', 'method' => 'POST', 'class' => 'checkoutForm'));
    $buttonBar = new HtmlTagBuilder('div', array('class' => 'cartButtonBar'));
    $checkoutBtn = new HtmlTagBuilder('button', array('class' => 'primary', 'name' => 'mode', 'value' => 'checkout', 'type' => 'submit'), array($i18n->translate('button.showCheckout')));
    if ($cart->totalItems() === 0) {
      $checkoutBtn->set('style', 'display:none;');
    }
    $buttonBar->append($checkoutBtn);
    $checkoutForm->append($buttonBar);
    return $checkoutForm;
  }

  /**
   * @param HeadAPI        $api
   * @param Unit           $unit
   * @param HtmlTagBuilder $wrapper
   */
  protected function showErrors($api, $unit, $wrapper)
  {
    $errors = $this->getUnitContext($api, $unit, 'errors', array());
    foreach ($errors as $errorMessage) {
      $wrapper->append(HtmlTagBuilder::div($errorMessage)->set('class', 'errorMessage'));
    }
  }

  /**
   * @param Cart       $cart
   * @param Translator $i18n
   */
  protected function addTemplateFakeData($cart, $i18n)
  {
    $cart->add(new CartItem(array(
      'name' => $i18n->translate('fake.cartItem1.name'),
      'pageId' => null,
      'price' => 100,
      'tax' => 19,
      'variant' => null,
    )));
    $cart->add(new CartItem(array(
      'name' => $i18n->translate('fake.cartItem2.name'),
      'pageId' => null,
      'price' => 150,
      'tax' => 27,
      'variant' => null,
    )));
    $cart->add(new CartItem(array(
      'name' => $i18n->translate('fake.cartItem3.name'),
      'pageId' => null,
      'price' => 200,
      'tax' => 38,
      'variant' => null,
    )));
  }

  /**
   * @return HtmlTagBuilder
   */
  protected function createCheckoutWrapper()
  {
    $checkoutWrapper = new HtmlTagBuilder('div', array('class' => 'checkoutForm'));
    return $checkoutWrapper;
  }

  /**
   * Creates a up to date cart object
   *
   * @param HeadAPI      $api
   * @param Unit         $unit
   * @param ShopSettings $settings
   * @param Translator   $i18n
   *
   * @return CartWithShipping
   */
  protected function createCart($api, $unit, $settings, $i18n)
  {
    $cart = new CartWithShipping('CART_' . $unit->getId(), new SessionStore(), $settings->getShippingCosts(), $settings->getShippingTax(), $settings->getShippingScalePriceData());

    // TEMPLATE (Use Fake Data)
    if ($api->isTemplate()) {
      $this->addTemplateFakeData($cart, $i18n);
      return $cart; // end
    }

    $cart->restoreSilent();

    return $cart;
  }

  /**
   * @param Unit         $unit
   * @param ShopSettings $settings
   * @param Translator   $i18n
   *
   * @return CheckoutFrom
   */
  protected function createCheckoutForm($unit, $settings, $i18n)
  {
    $checkout = new CheckoutFrom(
      'CHECKOUT_' . $unit->getId(),
      new SessionStore(),
      $i18n,
      $settings->getShippingCountries(),
      $settings->getPaymentMethodsAsOptions($i18n),
      $settings->getTosLink(),
      $settings->getPrivacyLink()
    );
    return $checkout;
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit               $unit
   * @param string                     $key
   * @param mixed                      $value
   */
  protected function setUnitContext($api, $unit, $key, $value)
  {
    $unitId = $unit->getId();
    if (!isset($this->unitContext[$unitId])) {
      $this->unitContext[$unitId] = array();
    }
    $this->unitContext[$unitId][$key] = $value;
  }


  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit               $unit
   * @param string                     $key
   *
   * @return mixed
   */
  protected function getUnitContext($api, $unit, $key, $default = null)
  {
    $unitId = $unit->getId();
    if (!isset($this->unitContext[$unitId])) {
      return $default;
    }
    if (!isset($this->unitContext[$unitId][$key])) {
      return $default;
    }
    return $this->unitContext[$unitId][$key];
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit               $unit
   * @param string[]                   $errors
   */
  protected function addErrorMessageToUnitContext($api, $unit, $errors)
  {
    $allErrors = $this->getUnitContext($api, $unit, 'errors', array());
    foreach ($errors as $errorMsg) {
      if (!empty($errorMsg)) {
        $allErrors[] = $errorMsg;
      }
    }
    $this->setUnitContext($api, $unit, 'errors', $allErrors);
  }

  /**
   * @param $renderApi
   * @param $i18n
   *
   * @return ShopSettings
   */
  protected function getShopSettings($renderApi, $i18n)
  {
    $settings = new ShopSettings($renderApi, $i18n);
    return $settings;
  }

  /**
   * @param HeadAPI    $api
   * @param Unit       $unit
   * @param ModuleInfo $moduleInfo
   *
   * @return ShopModeResponse
   */
  protected function processAction($api, $unit, $moduleInfo)
  {
    setlocale(LC_NUMERIC, 'C');
    try {
      $strategy = $this->getStrategy($api, $unit, $moduleInfo);
      return $strategy->process();
    } catch (\Exception $e) {
      $shopModeResponse = new ShopModeResponse();
      $shopModeResponse->addError($e->getMessage());
      return $shopModeResponse;
    }
  }

  /**
   * @param HeadAPI    $api
   * @param Unit       $unit
   * @param ModuleInfo $moduleInfo
   *
   * @return ShopModeStrategy\AbstractShopModeStrategy
   */
  protected function getStrategy($api, $unit, $moduleInfo)
  {
    $translator = $this->getTranslator($api, $moduleInfo, $api->getLocale());
    $settings = $this->getShopSettings($api, $translator);
    $cart = $this->createCart($api, $unit, $settings, $translator);
    $checkout = $this->createCheckoutForm($unit, $settings, $translator);

    $shopModeFactory = new ShopModeStrategy();
    return $shopModeFactory->getStrategy($api, $settings, $checkout, $cart, $translator);
  }

  /**
   * @param HeadAPI          $api
   * @param Unit             $unit
   * @param ShopModeResponse $shopModeResponse
   */
  protected function handleShopModeResponse($api, $unit, $shopModeResponse)
  {
    $this->setUnitContext($api, $unit, 'alreadyProcessed', true);
    $this->setUnitContext($api, $unit, 'view', $shopModeResponse->getViewMode());
    $this->addErrorMessageToUnitContext($api, $unit, $shopModeResponse->getErrors());
  }

  /**
   * @param HeadAPI    $api
   * @param ModuleInfo $moduleInfo
   *
   * @return Translator
   */
  protected function getTranslator($api, $moduleInfo)
  {
    return new Translator($api, $moduleInfo, $api->getLocale());
  }
}
