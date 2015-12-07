<?php


namespace rz_shop_cart;

require_once(__DIR__.'/CartProcessor.php');
require_once(__DIR__.'/ShopModeStrategy/ShopModeResponse.php');

use \Render\APIs\APIv1\HeadAPI;
use \Render\Unit;
use \Render\ModuleInfo;
use \Rukzuk\Modules\Translator;


class ShopModeStrategy
{
  /**
   * @var array
   */
  protected $availableStrategies = array();

  public function __construct()
  {
    $this->availableStrategies = array(
      'BackToCartStrategy',
      'ProcessCartStrategy',
      'CheckoutStrategy',
      'PaymentStrategy',
      'AfterPaymentStrategy',
      'CompleteStrategy',
    );
  }

  /**
   * @param HeadAPI          $api
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   * @param Translator       $translator
   *
   * @return ShopModeStrategy\AbstractShopModeStrategy
   */
  public function getStrategy($api, $settings, $checkout, $cart, $translator)
  {
    $cartProcessor = $this->getCartProcessor($api, $settings);
    $mode = $this->getShopMode($api, $cart, $cartProcessor);
    switch ($mode) {
      case 'toCart':
        $class = $this->getStrategyClassName('BackToCartStrategy');
        return new $class($checkout);

      case 'cart':
        $class = $this->getStrategyClassName('ProcessCartStrategy');
        return new $class($cartProcessor, $cart);

      case 'checkout':
        $class = $this->getStrategyClassName('CheckoutStrategy');
        return new $class();

      case 'payment':
        $class = $this->getStrategyClassName('PaymentStrategy');
        return new $class($settings, $checkout, $cart, $translator);

      case 'afterPayment':
        $class = $this->getStrategyClassName('AfterPaymentStrategy');
        return new $class($settings, $checkout, $cart, $translator);

      case 'complete':
        $class = $this->getStrategyClassName('CompleteStrategy');
        return new $class($settings, $checkout, $cart, $translator);
    }

    throw new \RuntimeException("No strategy for mode '" . $mode . "' found");
  }

  /**
   * @param HeadAPI          $api
   * @param CartWithShipping $cart
   * @param CartProcessor    $cartProcessor
   *
   * @return string
   */
  protected function getShopMode($api, $cart, $cartProcessor)
  {
    $defaultMode = $api->isTemplate() ? 'checkout' : 'cart';
    if ($cart->totalUniqueItems() <= 0) {
      return $defaultMode;
    }
    $mode = $cartProcessor->getMode($defaultMode);
    return $mode;
  }

  /**
   * @param string $strategy
   *
   * @return string
   */
  protected function getStrategyClassName($strategy)
  {
    if (!in_array($strategy, $this->availableStrategies)) {
      throw new \RuntimeException("Strategy '" . $strategy . "' not supported");
    }
    require_once(__DIR__ . '/ShopModeStrategy/' . $strategy . '.php');
    return '\\rz_shop_cart\\ShopModeStrategy\\' . $strategy;
  }

  /**
   * @param HeadAPI      $api
   * @param ShopSettings $settings
   *
   * @return CartProcessor
   */
  protected function getCartProcessor($api, $settings)
  {
    return new CartProcessor($api, $settings);
  }
}

