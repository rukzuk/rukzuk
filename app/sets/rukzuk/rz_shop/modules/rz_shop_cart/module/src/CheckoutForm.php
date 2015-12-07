<?php
namespace rz_shop_cart;

use \Cart\Storage\Store;
use \FormManager\Form;
use \FormManager\Fields\Field;
use \Rukzuk\Modules\Translator;
use \FormManager\Inputs;
use \FormManager\Attributes;


/**
 * Checkout From - addresses, comments and other checkout related data
 *
 * @package rz_shop_cart
 */
class CheckoutFrom extends Form
{

  /**
   * The cart id.
   *
   * @var string
   */
  private $id;

  /**
   * Cart storage implementation.
   *
   * @var \Cart\Storage\Store
   */
  private $store;

  /**
   * @param string     $id
   * @param Store      $store
   * @param Translator $i18n
   * @param array      $countries
   * @param array      $paymentMethods
   * @param string     $tosUrl
   */
  public function __construct($id, $store, $i18n, $countries = array(), $paymentMethods = array(), $tosUrl = '')
  {
    $this->id = $id;
    $this->store = $store;

    $this->updateLocale($i18n);

    $this->attr([
      'action' => '',
      'method' => 'POST'
    ]);

    $this->add([
      // buttons
      'modeToCart' => Field::submit()->val('toCart')->html($i18n->translate('button.backToCart'))
        ->render(function ($e) {
          // set name to avoid setting name from form
          $e->attr(['name' => 'mode']);
          return '<div class="cartButtonBar">' . $e . '</div>';
      }),

      // customer and payment
      'customer' => Field::group([
        'email' => Field::email()->required()->label($i18n->translate('checkoutForm.customer.email') .' *'),
        'telephone' => Field::tel()->label($i18n->translate('checkoutForm.customer.tel')),
        'paymentMethod' => Field::select()->options($paymentMethods)->label($i18n->translate('checkoutForm.paymentMethod')),
      ])->render(function ($group) {
        return '<div class="group customer"><h3>' . $group->label . '</h3>' . $group->childrenToHtml() . '</div>';
      })->label($i18n->translate('checkoutForm.customer.title')),

      // addresses
      'billing' => $this->createAddress($countries, $i18n)->label($i18n->translate('checkoutForm.billingAdr.title')),
      'shipping' => $this->createShippingAddress($countries, $i18n)->label($i18n->translate('checkoutForm.shippingAdr.title')),

      // comment
      'extra' => Field::group([
        'comment' => Field::textarea()->rows(8),
      ])->render(function ($group) {
        return '<div class="group"><h3>' . $group->label . '</h3>' . $group->childrenToHtml() . '</div>';
      })->label($i18n->translate('checkoutForm.customer.comment')),

      'accept_tos' => Field::checkbox()->required()->label(str_replace('%s', $tosUrl, $i18n->translate('checkoutForm.tosAccept')))->render(function ($field) {
        return "<label class='checkbox small'>$field->input " . $field->label() . '</label>';
      }),

      // buttons
      'modePayment' => Field::submit()->val('payment')->addClass('primary')->html($i18n->translate('button.doCheckout'))
        ->render(function ($e) {
          // set name to avoid setting name from form
          $e->attr(['name' => 'mode']);
          return '<div class="cartButtonBar">' . $e . '</div>';
      }),
    ]);
  }

  /**
   * Store content using Storage
   */
  public function save()
  {
    $this->store->put($this->id, $this->val());
  }

  /**
   * Load from Store.
   */
  public function loadFromStore()
  {
    $data = $this->store->get($this->id);
    // exclude accept tos
    unset($data['accept_tos']);
    $this->load($data);
  }

  private function createAddress($countries, $i18n)
  {
    return Field::group([
      'name' => Field::text()->required()->label($i18n->translate('checkoutForm.adr.name') . ' *'),
      'company' => Field::text()->label($i18n->translate('checkoutForm.adr.company')),
      'street' => Field::text()->required()->label($i18n->translate('checkoutForm.adr.street') . ' *'),
      'zip' => Field::text()->required()->label($i18n->translate('checkoutForm.adr.zip') . ' *'),
      'city' => Field::text()->required()->label($i18n->translate('checkoutForm.adr.city') . ' *'),
      'country' => Field::select()->required()->options($countries)->label($i18n->translate('checkoutForm.adr.country') . ' *'),
    ])->render(function ($group) {
      return '<div class="adrGroup billing"><h3>' . $group->label . '</h3>' . $group->childrenToHtml() . '</div>';
    });
  }

  private function createShippingAddress($countries, $i18n)
  {
    return Field::group([
      'use_shipping_address' => Field::checkbox()->label($i18n->translate('checkoutForm.adr.customShippingAdr'))->render(function ($field) {
        return "<h3><label class='checkbox'>$field->input " . $field->label() . '</label></h3>';
      }),
      'name' => Field::text()->label($i18n->translate('checkoutForm.adr.name')),
      'company' => Field::text()->label($i18n->translate('checkoutForm.adr.company')),
      'street' => Field::text()->label($i18n->translate('checkoutForm.adr.street')),
      'zip' => Field::text()->label($i18n->translate('checkoutForm.adr.zip')),
      'city' => Field::text()->label($i18n->translate('checkoutForm.adr.city')),
      'country' => Field::select()->options($countries)->label($i18n->translate('checkoutForm.adr.country')),
    ])->render(function ($group) {
      return '<div class="adrGroup">' . $group->childrenToHtml() . '</div>';
    });
  }

  protected function updateLocale($i18n)
  {
    Inputs\Email::$error_message = $i18n->translate('formManager.input.email'); // 'This value is not a valid email';
    Inputs\Number::$error_message = $i18n->translate('formManager.input.number'); // 'This value is not a valid number';

    Attributes\Max::$error_message = $i18n->translate('formManager.attr.max'); // 'The max value allowed is %s';
    Attributes\Maxlength::$error_message = $i18n->translate('formManager.attr.maxLength'); // 'The max length allowed is %s';
    Attributes\Min::$error_message = $i18n->translate('formManager.attr.min'); // 'The min value allowed is %s';
    Attributes\Pattern::$error_message = $i18n->translate('formManager.attr.pattern'); // 'This value is not valid';
    Attributes\Required::$error_message = $i18n->translate('formManager.attr.required'); // 'This value is required';
  }

  /**
   * E-Mail of the Buyer
   *
   * @return string
   */
  public function getBuyerEmail()
  {
    return $this['customer']['email']->val();
  }

  /**
   * Returns the selected payment method
   *
   * @return string
   */
  public function getPaymentMethod()
  {
    return $this['customer']['paymentMethod']->val();
  }

  /**
   * Returns the shipping address.
   * If no separate address is given, the billing address will be returned.
   *
   * @return array
   */
  public function getShippingAddress()
  {
    if ($this['shipping']['use_shipping_address']->val() === 'on') {
      $adr = $this['shipping'];
    } else {
      $adr = $this['billing'];
    }
    $customer = $this['customer'];
    return array(
      'name' => $adr['name']->val(),
      'company' => $adr['company']->val(),
      'street' => $adr['street']->val(),
      'zip' => $adr['zip']->val(),
      'city' => $adr['city']->val(),
      'country' => $adr['country']->val(),
      'telephone' => $customer['telephone']->val(),
      'email' => $customer['email']->val(),
    );
  }
}
