<?php
namespace rz_shop_cart;

use \Cart\Cart;
use \Cart\CartItem;
use \Render\APIs\APIv1\RenderAPI;

/**
 * Class CartProcessor
 *
 * @package rz_shop_cart
 */
class CartProcessor
{

  private $api;
  private $defaultVat;

  /**
   * @param HeadAPI      $api
   * @param ShopSettings $settings
   */
  public function __construct($api, $settings)
  {
    $this->api = $api;
    $this->defaultVat = $settings->getShopDefaultVat();
  }

  /**
   * @param string $defaultMode
   *
   * @return string
   */
  public function getMode($defaultMode = null)
  {
    $mode = $this->getFromPOST('mode');
    if (!empty($mode)) {
      return $mode;
    }
    $mode = $this->getFromGET('mode');
    if (!empty($mode)) {
      return $mode;
    }
    return $defaultMode;
  }

  /**
   * Extract POST Data
   *
   * @param      $key
   * @param null $fallback
   *
   * @return null
   */
  protected function getFromPOST($key, $fallback = null)
  {
    return isset($_POST[$key]) ? $_POST[$key] : $fallback;
  }

  /**
   * Extract POST Data
   *
   * @param      $key
   * @param null $fallback
   *
   * @return null
   */
  protected function getFromGET($key, $fallback = null)
  {
    return isset($_GET[$key]) ? $_GET[$key] : $fallback;
  }

  /**
   * Add or update elements in session cart
   *
   * @param Cart $cart
   */
  public function handlePOST($cart)
  {
    // get action
    $action = $this->getFromPOST('action');
    if (is_null($action)) {
      return;
    }

    switch ($action) {
      // add to cart
      case 'addToCart':
        $this->handlePOSTAddToCart($cart,
          $this->getFromPOST('productPageId'),
          $this->getFromPOST('variant')
        );
        break;
      // update amount (0 = remove)
      case 'updateAmount':
        $this->handlePOSTUpdateAmount($cart,
          $this->getFromPOST('cartItemId'),
          intval($this->getFromPOST('amount', 0))
        );
        break;
      // clear cart
      case 'clearCart':
        $cart->clear();
        break;
    }
  }

  /**
   * Create Cart Item From Product Data
   *
   * @param string $productPageId - PAGE-...-PAGE
   * @param string $variant
   *
   * @return CartItem
   */
  protected function buildCartItemFromProductData($productPageId, $variant)
  {
    $productData = $this->getProductData($productPageId);

    // VAT
    $netPrice = $productData['price'] / (1+$this->getDefaultVat());

    $cartItem = new CartItem(array(
      'name' => $productData['_name'],
      'pageId' => $productPageId,
      'price' => $netPrice,
      'tax' => $productData['price'] - $netPrice,
      'variant' => $variant,
    ));

    return $cartItem;
  }

  /**
   * @param Cart   $cart
   * @param string $productPageId
   * @param string $variant
   */
  protected function handlePOSTAddToCart($cart, $productPageId, $variant)
  {
    if (is_null($productPageId)) {
      return;
    }
    $cartItem = $this->buildCartItemFromProductData($productPageId, $variant);
    $cart->add($cartItem);
  }

  /**
   * @param Cart   $cart
   * @param string $cartItemId
   * @param int    $amount
   */
  protected function handlePOSTUpdateAmount($cart, $cartItemId, $amount)
  {
    if (!$cart->has($cartItemId)) {
      return;
    }

    if ($amount === 0) {
      $cart->remove($cartItemId);
    } else {
      $cart->update($cartItemId, 'quantity', intval($amount));
    }
  }

  /**
   * Get Data of a Product
   *
   * @param $pageId
   *
   * @return array
   */
  protected function getProductData($pageId)
  {
    try {
      $page = $this->api->getNavigation()->getPage($pageId);
      return $page->getPageAttributes();
    } catch (\Exception $e) {
      return array();
    }
  }

  /**
   * @return float
   */
  protected function getDefaultVat()
  {
    return $this->defaultVat;
  }

}