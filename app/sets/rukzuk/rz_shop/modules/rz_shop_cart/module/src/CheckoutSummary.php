<?php
namespace rz_shop_cart;

use \Rukzuk\Modules\HtmlTagBuilder;
use \Rukzuk\Modules\Translator;

/**
 * Class CheckoutSummary
 *
 * @package rz_shop_cart
 */
class CheckoutSummary
{
  private $i18n;
  private $checkout;
  private $paymentMethods;

  /**
   * @param Translator   $i18n
   * @param CheckoutFrom $checkout
   * @param array        $paymentMethods
   * @param array        $shippingCountries
   */
  public function __construct($i18n, $checkout, $paymentMethods, $shippingCountries)
  {
    $this->i18n = $i18n;
    $this->checkout = $checkout;
    $this->paymentMethods = $paymentMethods;
    $this->shippingCountries = $shippingCountries;
  }

  /**
   * Render Checkout Summary
   *
   * @return HtmlTagBuilder
   */
  public function renderCheckoutSummary()
  {
    /** @var HtmlTagBuilder $result */
    $result = HtmlTagBuilder::div();

    $checkout = $this->checkout;

    // Customer and Payment
    $customer = $checkout['customer'];
    $result->append(HtmlTagBuilder::h2($customer->label()));

    $paymentMethod = $this->paymentMethods[$customer['paymentMethod']->val()];

    $result->append($this->buildKeyValueTable(array(
      $customer['email']->label() => $customer['email']->val(),
      $customer['telephone']->label() => $customer['telephone']->val(),
      $customer['paymentMethod']->label() => HtmlTagBuilder::div(HtmlTagBuilder::h3($paymentMethod['name']))->appendText($paymentMethod['desc']),
    )));

    // Addresses
    $billing = $checkout['billing'];
    $shipping = $checkout['shipping'];

    $result->append(HtmlTagBuilder::h2($billing->label()));
    $result->append($this->renderAddress($billing));

    $result->append(HtmlTagBuilder::h2($shipping->label()));
    if ($shipping['use_shipping_address']->val() === 'on') {
      $result->append($this->renderAddress($shipping));
    } else {
      $result->append($this->i18n->translate('checkoutSummary.shippingAdrSameAsBilling'));
    }

    // Comment
    $extra = $checkout['extra'];
    $result->append(HtmlTagBuilder::h2($extra->label()));
    $result->appendText($extra['comment']->val());

    return $result;
  }

  protected function renderAddress($adr)
  {
    return $this->buildKeyValueTable(array(
      $adr['name']->label() => $adr['name']->val(),
      $adr['company']->label() => $adr['company']->val(),
      $adr['street']->label() => $adr['street']->val(),
      $adr['zip']->label() => $adr['zip']->val(),
      $adr['city']->label() => $adr['city']->val(),
      $adr['country']->label() => $this->shippingCountries[$adr['country']->val()],
    ));
  }

  protected function buildKeyValueTable($items)
  {
    $table = HtmlTagBuilder::table();

    foreach ($items as $k => $v) {
      $table->append(HtmlTagBuilder::tr(HtmlTagBuilder::td($k), HtmlTagBuilder::td($v)));
    }

    return $table;
  }

}
