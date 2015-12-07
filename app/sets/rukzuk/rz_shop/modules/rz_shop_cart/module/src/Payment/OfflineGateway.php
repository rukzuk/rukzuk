<?php
namespace rz_shop_cart\Payment;

require_once(__DIR__ . '/AbstractGateway.php');

/**
 * Class OfflineGateway
 *
 * @package rz_shop_cart
 */
class OfflineGateway extends AbstractGateway
{
  /**
   * @param array $params
   */
  public function purchase(array $params)
  {
    $this->setRedirectUrl();
    $this->setSuccessful(true);
  }

  /**
   * @param array $params
   */
  public function completePurchase(array $params = array())
  {
    $this->setRedirectUrl();
    $this->setSuccessful(true);
  }
}
