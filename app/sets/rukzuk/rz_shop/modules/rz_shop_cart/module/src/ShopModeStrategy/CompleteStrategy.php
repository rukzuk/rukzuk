<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractShopModeStrategy.php');
require_once(__DIR__ . '/../CheckoutSummary.php');

use \Rukzuk\Modules\HtmlTagBuilder;
use \Rukzuk\Modules\Mailer;
use \Rukzuk\Modules\Translator;
use \rz_shop_cart\CartView;
use \rz_shop_cart\CheckoutSummary;
use \rz_shop_cart\ShopSettings;
use \rz_shop_cart\CheckoutFrom;
use \rz_shop_cart\CartWithShipping;


class CompleteStrategy extends AbstractShopModeStrategy
{
  /**
   * @var ShopSettings
   */
  private $settings;
  /**
   * @var CheckoutFrom
   */
  private $checkout;
  /**
   * @var CartWithShipping
   */
  private $cart;
  /**
   * @var Translator
   */
  private $translator;

  /**
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   * @param Translator       $translator
   */
  public function __construct($settings, $checkout, $cart, $translator)
  {
    $this->settings = $settings;
    $this->checkout = $checkout;
    $this->cart = $cart;
    $this->translator = $translator;
  }

  public function process()
  {
    $this->checkout->loadFromStore();
    $this->handleCompleteCheckout();
    $this->cart->clear();
    return new ShopModeResponse(ShopModeResponse::VIEW_MODE_SUCCESS);
  }

  /**
   * Called after a successful checkout, sends mail, etc.
   */
  protected function handleCompleteCheckout()
  {
    // shop owner notification address
    $notificationAddress = $this->settings->getEmailNotificationAdr();
    if ($notificationAddress == '') {
      throw new \RuntimeException($this->translator->translate('doCheckout.fail'));
    }

    $buyerEmail = $this->checkout->getBuyerEmail();
    $confirmation = $this->createCheckoutConfirmation();

    // send notification email to shop owner
    $this->sendNotificationMail($confirmation, $buyerEmail, $notificationAddress);

    // send confirmation email to buyer
    $this->sendConfirmationMail($confirmation, $buyerEmail, $notificationAddress);
  }

  /**
   * @return HtmlTagBuilder
   */
  protected function createCheckoutConfirmation()
  {
    // checkout confirmation
    $confirmation = new HtmlTagBuilder('div');
    $cartView = $this->renderCart();
    $checkoutSummary = $this->renderCheckoutSummary();

    $confirmation->append($cartView);
    $confirmation->append($checkoutSummary);
    return $confirmation;
  }

  /**
   * Cart Table
   *
   * @return HtmlTagBuilder
   */
  protected function renderCart()
  {
    $cartView = new CartView($this->settings, $this->translator);
    return $cartView->renderCart($this->cart, false);
  }

  /**
   * Send email to shop owner
   *
   * @param HtmlTagBuilder $confirmation
   * @param string         $buyerEmail
   * @param string         $shopOwnerNotificationAdr
   */
  protected function sendNotificationMail($confirmation, $buyerEmail, $shopOwnerNotificationAdr)
  {
    $htmlBody = $this->getEmailBody($confirmation);
    $subject = $this->translator->translate('email.notificationSubjectOwner');

    $mailer = $this->getMailer();
    $mailer->setHtmlBody($htmlBody);
    $mailer->setSubject($subject);
    $mailer->setFrom($shopOwnerNotificationAdr);
    $mailer->addReplyTo($buyerEmail);
    $mailer->addTo($shopOwnerNotificationAdr);
    $mailer->send();
  }

  /**
   * Send email to buyer
   *
   * @param HtmlTagBuilder $confirmation
   * @param string         $buyerEmail
   * @param string         $shopOwnerNotificationAdr
   */
  protected function sendConfirmationMail($confirmation, $buyerEmail, $shopOwnerNotificationAdr)
  {
    $emailConfirmationText = $this->settings->getEmailConfirmationText();
    $htmlBody = $this->getEmailBody($confirmation, $emailConfirmationText);
    $subject = $this->translator->translate('email.confirmationSubjectBuyer');

    $mailer = $this->getMailer();
    $mailer->setHtmlBody($htmlBody);
    $mailer->setSubject($subject);
    $mailer->setFrom($shopOwnerNotificationAdr);
    $mailer->addReplyTo($shopOwnerNotificationAdr);
    $mailer->addTo($buyerEmail);
    $mailer->send();
  }

  /**
   * @return HtmlTagBuilder
   */
  protected function renderCheckoutSummary()
  {
    $paymentMethods = $this->settings->getShopPaymentMethods($this->translator);
    $shippingCountries = $this->settings->getShippingCountries();
    $checkoutSummary = new CheckoutSummary($this->translator, $this->checkout,
      $paymentMethods, $shippingCountries);
    return $checkoutSummary->renderCheckoutSummary();
  }

  /**
   * @return Mailer
   */
  protected function getMailer()
  {
    return $this->settings->getMailer();
  }

  /**
   * @param HtmlTagBuilder $confirmation
   * @param string|null    $emailConfirmationText
   *
   * @return string
   */
  protected function getEmailBody($confirmation, $emailConfirmationText = null)
  {
    if (empty($emailConfirmationText)) {
      return $confirmation->toString();
    } else {
      $emailBody = HtmlTagBuilder::div()->appendText($emailConfirmationText)->append($confirmation);
      return $emailBody->toString();
    }
  }
}