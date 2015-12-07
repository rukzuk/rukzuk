<?php


namespace rz_shop_cart\ShopModeStrategy;


class ShopModeResponse
{
  const VIEW_MODE_CART = 'showCart';
  const VIEW_MODE_CHECKOUT = 'showCheckout';
  const VIEW_MODE_SUCCESS = 'showSuccess';

  /**
   * @var string
   */
  private $viewMode = self::VIEW_MODE_CART;

  /**
   * @var string
   */
  private $redirectUrl;

  /**
   * @var string[]
   */
  private $errors = array();

  /**
   * @param string|null $viewMode
   * @param string|null $redirectUrl
   */
  public function __construct($viewMode = null, $redirectUrl = null)
  {
    if (!is_null($viewMode)) {
      $this->viewMode = $viewMode;
    }
    if (!is_null($redirectUrl)) {
      $this->redirectUrl = $redirectUrl;
    }
  }

  /**
   * @param string $viewMode
   */
  public function setViewMode($viewMode)
  {
    $this->viewMode = $viewMode;
  }

  /**
   * @return string
   */
  public function getViewMode()
  {
    return $this->viewMode;
  }

  /**
   * @param string $redirectUrl
   */
  public function setRedirectUrl($redirectUrl)
  {
    $this->redirectUrl = $redirectUrl;
  }

  /**
   * @return string
   */
  public function getRedirectUrl()
  {
    return $this->redirectUrl;
  }

  /**
   * @return bool
   */
  public function hasRedirect()
  {
    return !empty($this->redirectUrl);
  }

  /**
   * @param $error
   */
  public function addError($error)
  {
    $this->errors[] = $error;
  }

  /**
   * @return string[]
   */
  public function getErrors()
  {
    return $this->errors;
  }
}