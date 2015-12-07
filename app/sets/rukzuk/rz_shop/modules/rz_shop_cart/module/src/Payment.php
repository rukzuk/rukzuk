<?php
namespace rz_shop_cart;

use \Rukzuk\Modules\Translator;
use \rz_shop_cart\Payment\AbstractGateway;

/**
 * Class Payment
 *
 * @package rz_shop_cart
 */
class Payment
{
  /**
   * @param string           $gateway
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   * @param Translator       $i18n
   *
   * @return AbstractGateway
   * @throws RuntimeException
   */
  public static function create($gateway, $settings, $checkout, $cart, $i18n)
  {
    $class = self::getGatewayClass($gateway);
    if (!class_exists($class)) {
      throw new \RuntimeException("Payment gateway class '" . $class . "' not found");
    }
    return new $class($settings, $checkout, $cart, $i18n);
  }

  /**
   * @param string $gateway
   *
   * @return array
   */
  private static function getGatewayClass($gateway)
  {
    switch ($gateway) {
      case 'paymentInvoice':
      case 'paymentBanktransfer':
        $className = 'OfflineGateway';
        break;
      case 'paymentPayPalExpress':
        $className = 'PayPalExpressGateway';
        break;
      default:
        throw new \RuntimeException("Payment gateway '" . $gateway . "' not supported");
        break;
    }
    require_once(__DIR__ . '/Payment/' . $className . '.php');
    return '\\rz_shop_cart\\Payment\\' . $className;
  }
}
