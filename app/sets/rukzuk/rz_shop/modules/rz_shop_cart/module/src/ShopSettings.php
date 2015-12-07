<?php
namespace rz_shop_cart;

use \Render\APIs\APIv1\HeadAPI;
use \Rukzuk\Modules\Translator;
use \Rukzuk\Modules\Mailer;


class ShopSettings
{

  /**
   * @var HeadAPI
   */
  private $api;

  /**
   * @var Translator
   */
  private $i18n;

  public function __construct($api, $i18n)
  {
    $this->api = $api;
    $this->i18n = $i18n;
  }

  /**
   * Returns the shop currency as ISO4217 (3-letter code, e.g EUR, USD, CAD, HKD, CHF)
   *
   * @return mixed
   */
  public function getShopCurrencyCode()
  {
    return $this->getShopSetting('currency', 'EUR');
  }


  /**
   * @param $i18n
   *
   * @return array
   */
  public function getShopPaymentMethods($i18n)
  {
    $shopSettings = $this->getShopSettings($this->api);
    $paymentMethods = array();

    foreach (array('paymentBanktransfer', 'paymentInvoice', 'paymentPayPalExpress') as $method) {
      if (isset($shopSettings[$method]) && $shopSettings[$method]) {
        $paymentMethods[$method] = array(
          'name' => $i18n->translate('payment.' . $method),
          'desc' => $this->getShopSetting($method . 'Desc'),
        );
      }
    }

    return $paymentMethods;
  }

  /**
   * Payment Methods as as Select::options compatible array (with translated texts)
   *
   * @param $i18n
   *
   * @return array
   */
  public function getPaymentMethodsAsOptions($i18n)
  {
    $paymentOptions = array();
    foreach ($this->getShopPaymentMethods($i18n) as $pm => $opts) {
      $paymentOptions[$pm] = $opts['name'];
    }
    return $paymentOptions;
  }

  /**
   * Returns the shop VAT in percent (e.g. 0.1 for 10%)
   *
   * @return float
   */
  public function getShopDefaultVat()
  {
    return (floatval($this->getShopSetting('vat', 19)) / 100.0);
  }

  /**
   * Creates an assoc array from countryList textarea value
   * <code>
   * LOCALE: en
   * SRC: 'DE:{"de":"Deutschland","en":"Germany"}\nAT:Austria\nCH:Switzerland';
   * RETURN: array(
   *     'DE' => 'Germany',
   *     'AT' => 'Austria',
   *     'CH' => 'Switzerland',
   * )
   * </code>
   *
   * @return array
   */
  public function getShippingCountries()
  {
    $countries = array();
    $rows = explode("\n", $this->getShopSetting('countryList'));
    foreach ($rows as $rowCount => $row) {
      $data = explode(':', $row, 2);
      if (count($data) > 1 && strlen($data[0]) == 2) {
        $countries[strtoupper($data[0])] = $this->i18n->translateInput($data[1]);
      }
    }
    return $countries;
  }

  /**
   * Get Shipping Costs (including tax)
   *
   * @return float
   */
  public function getShippingCosts()
  {
    $shippingCosts = floatval($this->getShopSetting('shippingCosts', 0.0));
    return $shippingCosts;
  }

  /**
   * Shipping Tax
   *
   * @return float
   */
  public function getShippingTax()
  {
    return $this->getShippingCosts() - ($this->getShippingCosts() / (1+$this->getShopDefaultVat()));
  }

  /**
   * Link to TOS Page
   *
   * @return string
   */
  public function getTosLink()
  {
    return $this->api->getNavigation()->getPage($this->getShopSetting('tosPage'))->getUrl();
  }

  public function getThanksMessage()
  {
    return $this->getShopSetting('thanksText', 'Purchase complete.');
  }


  public function getEmailNotificationAdr()
  {
    return $this->getShopSetting('emailNotificationAddress');
  }


  public function getEmailConfirmationText()
  {
    return $this->getShopSetting('emailConfirmationText');
  }

  public function getPayPalExpressUsername()
  {
    return $this->getShopSetting('paymentPayPalExpressApiUsername');
  }

  public function getPayPalExpressPassword()
  {
    return $this->getShopSetting('paymentPayPalExpressApiPassword');
  }

  public function getPayPalExpressSignature()
  {
    return $this->getShopSetting('paymentPayPalExpressApiSignature');
  }

  /**
   * Shop Settings Array
   *
   * @return array
   */
  public function getShopSettings()
  {
    return $this->api->getWebsiteSettings('rz_shop');
  }

  /**
   * Single Shop Setting
   *
   * @param string $key
   * @param mixed  $default
   *
   * @return mixed
   */
  public function getShopSetting($key, $default = '')
  {
    $a = $this->getShopSettings();
    return isset($a[$key]) ? $a[$key] : $default;
  }

  /**
   * @return string
   */
  public function getLocalCode()
  {
    return $this->api->getInterfaceLanguage();
  }

  /**
   * @param array $params
   *
   * @return string
   */
  public function getCurrentAbsoluteUrl(array $params)
  {
    return $this->api->getNavigation()->getCurrentPage()->getAbsoluteUrl($params);
  }

  /**
   * @param string $pageId
   * @param array  $params
   *
   * @return string
   */
  public function getPageUrl($pageId, array $params = array())
  {
    return $this->api->getNavigation()->getPage($pageId)->getUrl($params);
  }

  /**
   * @return Mailer
   */
  public function getMailer()
  {
    return new Mailer($this->api);
  }
}
