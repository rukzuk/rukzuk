<?php
namespace rz_shop_cart;


use \Cart\CartItem;
use \Rukzuk\Modules\HtmlTagBuilder;
use \Rukzuk\Modules\Translator;

/**
 * Class CartView
 *
 * @package rz_shop_cart
 */
class CartView
{
  private $i18n;
  private $currency;
  private $settings;

  /**
   * @param ShopSettings $settings
   * @param Translator   $i18n
   */
  public function __construct($settings, $i18n)
  {
    $this->i18n = $i18n;
    $this->settings = $settings;
    $this->currency = $settings->getShopCurrencyCode();
  }

  /**
   * Cart Table
   *
   * @param CartWithShipping $cart
   * @param bool             $edit - show edit input fields
   *
   * @return HtmlTagBuilder
   */
  public function renderCart($cart, $edit = false)
  {
    $cartWrapper = new HtmlTagBuilder('div', array('class' => 'cartOverview'));
    $cartTable = new HtmlTagBuilder('table', array('class' => 'cartTable'));
    $cartTable->append($this->createTableHeader());
    $cartWrapper->append($cartTable);

    // empty cart
    if ($cart->totalUniqueItems() === 0) {
      $this->addEmptyCartRow($cartTable);
    } else {
      /** @var CartItem $item */
      foreach ($cart->all() as $item) {
        $cartTable->append($this->renderCartRow($edit, $item));
      }
      $this->addCartSummary($cart, $cartTable);
    }

    return $cartWrapper;
  }

  protected function buildRemoveButton($cartItemId)
  {
    $removeAction = new HtmlTagBuilder('form', array('action' => '', 'method' => 'POST'), array(
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'mode', 'value' => 'cart')),
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'updateAmount')),
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'amount', 'value' => 0)),
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'cartItemId', 'value' => $cartItemId)),
      new HtmlTagBuilder('input', array('type' => 'submit', 'value' => '×', 'class' => 'rowRemoveButton', 'title' => $this->i18n->translate('cartTable.removeRow')))
    ));
    return $removeAction;
  }

  protected function buildAmountField($cartItemId, $amount)
  {
    $amountField = new HtmlTagBuilder('form', array('action' => '', 'method' => 'POST'), array(
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'mode', 'value' => 'cart')),
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'updateAmount')),
      new HtmlTagBuilder('input', array('type' => 'hidden', 'name' => 'cartItemId', 'value' => $cartItemId)),
      new HtmlTagBuilder('input', array('type' => 'number', 'name' => 'amount', 'value' => $amount, 'min' => '1', 'max' => '999', 'onChange' => '$(this.form).submit();')),
    ));
    return $amountField;
  }

  /**
   * @param $edit
   * @param $item
   *
   * @return HtmlTagBuilder
   */
  protected function renderCartRow($edit, $item)
  {
    $variantDisplay = '';
    if ($item->get('variant')) {
      $variantDisplay = new HtmlTagBuilder('span', array('class' => 'variant'), array($item->get('variant')));
    }

    // link to product
    if ($edit) {
      $pageUrl = $this->settings->getPageUrl($item->get('pageId'));
      $productName = new HtmlTagBuilder('a', array('href' => $pageUrl), array($item->get('name')));
    } else {
      $productName = $item->get('name');
    }

    $cartRow = new HtmlTagBuilder('tr', array('class' => 'itemRow'), array(
      new HtmlTagBuilder('td', array('class' => 'name'), array($productName, ' ', $variantDisplay)),
      new HtmlTagBuilder('td', array('class' => 'number amount'), array(
        $edit ? $this->buildAmountField($item->id, $item->quantity) : strval($item->quantity)
      )),
      new HtmlTagBuilder('td', array('class' => 'number'), array($this->formatCurrency($item->getSinglePrice()))),
      new HtmlTagBuilder('td', array('class' => 'number'), array($this->formatCurrency($item->getTotalPrice()))),
      new HtmlTagBuilder('td', array('class' => 'action'), array(
        $edit ? $this->buildRemoveButton($item->id) : ''
      )),

    ));
    return $cartRow;
  }

  /**
   * Format Currency
   *
   * @param float $amount - value money
   *
   * @return string
   */
  protected function formatCurrency($amount)
  {
    return $this->i18n->formatCurrency($amount, $this->currency);
  }

  /**
   * @return HtmlTagBuilder
   */
  protected function createTableHeader()
  {
    $cartHeader = new HtmlTagBuilder('tr', array(), array(
      new HtmlTagBuilder('th', array('class' => 'name'), array($this->i18n->translate('cartTable.productName'))),
      new HtmlTagBuilder('th', array('class' => 'amount'), array($this->i18n->translate('cartTable.amount'))),
      new HtmlTagBuilder('th', array('class' => 'number'), array($this->i18n->translate('cartTable.price'))),
      new HtmlTagBuilder('th', array('class' => 'number'), array($this->i18n->translate('cartTable.rowSum'))),
      new HtmlTagBuilder('th', array(), array('')),
    ));
    return $cartHeader;
  }

  /**
   * @param CartWithShipping $cart
   * @param                  $cartTable
   */
  protected function addCartSummary($cart, $cartTable)
  {
    $shippingCostsDisplay = new HtmlTagBuilder('tr', array('class' => 'shippingSum'), array(
      new HtmlTagBuilder('td', array('colspan' => '3', 'class' => 'number sumText'), array($this->i18n->translate('cartTable.shippingCosts'))),
      new HtmlTagBuilder('td', array('class' => 'number'), array($this->formatCurrency($cart->shipping()))),
      new HtmlTagBuilder('td', array('class' => 'action'), array('')),
    ));
    $cartTable->append($shippingCostsDisplay);

    // SUM
    $sumTableElement = new HtmlTagBuilder('tr', array('class' => 'fullSum'), array(
      new HtmlTagBuilder('td', array('colspan' => '3', 'class' => 'number sumText'), array($this->i18n->translate('cartTable.fullPrice'))),
      new HtmlTagBuilder('td', array('class' => 'number'), array($this->formatCurrency($cart->totalWithShipping()))),
      new HtmlTagBuilder('td', array('class' => 'action'), array('')),
    ));
    $cartTable->append($sumTableElement);

    // VAT
    $sumVatTableElement = new HtmlTagBuilder('tr', array('class' => 'vatSum'), array(
      new HtmlTagBuilder('td', array('colspan' => '4', 'class' => 'number sumText'), array(
        str_replace('%s', $this->formatCurrency($cart->taxWithShipping()), $this->i18n->translate('cartTable.includingVAT'))
      )),
      new HtmlTagBuilder('td', array('class' => 'action'), array('')),
    ));
    $cartTable->append($sumVatTableElement);
  }

  /**
   * @param $cartTable
   */
  private function addEmptyCartRow($cartTable)
  {
    $cartTable->append(new HtmlTagBuilder('tr', array(), array(
      new HtmlTagBuilder('td', array('colspan' => '4', 'class' => 'emptyCart'), array($this->i18n->translate('cartTable.emptyCart'))),
    )));
  }
}
