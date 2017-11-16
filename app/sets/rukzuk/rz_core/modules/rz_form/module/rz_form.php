<?php
namespace Rukzuk\Modules;

use Render\APIs\APIv1\RenderAPI;
use Render\ModuleInfo;
use Render\Unit;

require_once(dirname(__FILE__) . "/lib/http/Request.php");
require_once(dirname(__FILE__) . "/lib/enum/FieldType.php");
require_once(dirname(__FILE__) . "/lib/enum/ListType.php");
require_once(dirname(__FILE__) . "/lib/enum/InputType.php");
require_once(dirname(__FILE__) . "/models/FormSubmit.php");
require_once(dirname(__FILE__) . "/models/ChoiceBox.php");
require_once(dirname(__FILE__) . "/models/Validation.php");
require_once(dirname(__FILE__) . "/models/HoneyPotComponent.php");
require_once(dirname(__FILE__) . "/lib/mailer/FormValueSet.php");
require_once(dirname(__FILE__) . "/lib/gui/Form.php");
require_once(dirname(__FILE__) . "/lib/gui/ButtonField.php");
require_once(dirname(__FILE__) . "/lib/gui/Container.php");
require_once(dirname(__FILE__) . "/lib/gui/TextField.php");
require_once(dirname(__FILE__) . "/lib/gui/Label.php");
require_once(dirname(__FILE__) . "/lib/gui/TextareaField.php");
require_once(dirname(__FILE__) . "/lib/gui/Span.php");
require_once(dirname(__FILE__) . "/lib/gui/Paragraph.php");

/**
 * @package      Rukzuk\Modules\rz_form
 */
class rz_form extends SimpleModule
{

  const ELEMENT_TAG = 'form';
  const MODULE_ID_RZ_FORM_FIELD = 'rz_form_field';
  const MODULE_ID_RZ_FORM_FIELD_TEXT = 'rz_form_field_text';
  const MODULE_ID_RZ_FORM_FIELD_SELECT = 'rz_form_field_select';
  const MODULE_ID_RZ_FORM_FIELD_BUTTON = 'rz_form_field_button';
  const MODULE_ID_RZ_FORM_FIELD_HIDDEN = 'rz_form_field_hidden';

  /**
   * @var \FormSubmit
   */
  private $formSubmit = null;
  /**
   * @var array
   */
  private $formUnits = array();

  /**
   * @var \IRequest
   */
  private $http = null;

  /**
   * After submit redirect support (actual redirect is done in rz_root)
   * @param $api
   * @param $unit
   * @param $moduleInfo
   * @return array
   */
  protected function httpRedirect($api, $unit, $moduleInfo)
  {
    $isRedirect = ($api->getFormValue($unit, 'confirmationType') == 'redirect');
    list($form, $honeyPotComponent) = $this->setupForm($unit);

    if ($isRedirect) {
      try {
        $formSend = $this->checkFormAndSendMail($api, $unit, $honeyPotComponent);
        if ($formSend) {
          $nav = $api->getNavigation();
          $internalUrl = $nav->getPage($api->getFormValue($unit, 'confirmationPageId'))->getUrl();
          return array('url' => $internalUrl);
        }
      } catch (\Exception $e) {
        // do nothing - we don't know what to do if send fails
        // renderContent will try to send the mail again - if it fails again it will show the error.
      }
      return array();
    }
  }

  /**
   * Setup the form objects
   * @param $unit
   * @return array
   */
  private function setupForm($unit)
  {
    $this->http = new \Request();
    $form = new \Form();
    $honeyPotComponent = new \HoneyPotComponent();
    $this->formSubmit = new \FormSubmit();

    $elementProperties = $form->getElementProperties();

    $elementProperties->setId("form" . str_replace("-", "", $unit->getId()));
    $elementProperties->addAttribute('action', $_SERVER['REQUEST_URI'] . '#' . $unit->getId());
    $elementProperties->addAttribute('method', 'post');
    $elementProperties->addAttribute('enctype', 'multipart/form-data');
    $form->add($honeyPotComponent->getHoneyPot());
    $form->add($honeyPotComponent->getFormUnitIdentifier($unit->getId()));
    return array($form, $honeyPotComponent);
  }

  /**
   * Check for a valid form and send email
   * @param $renderApi
   * @param $unit
   * @param $honeyPotComponent
   * @return bool
   * @throws \Exception
   */
  private function checkFormAndSendMail($renderApi, $unit, $honeyPotComponent)
  {
    $postRequest = $this->formSubmit->getPostValues();

    if ($this->formSubmit->isValid($renderApi, $unit)
      && count($postRequest) > 0
      && $honeyPotComponent->isValidHoneyPot($postRequest)
      && $this->hasValidFormData($renderApi, $unit)
    ) {
      $this->formSubmit->setFieldLabelsToFormValueSet($renderApi);
      try {
        $this->sendEmail($renderApi, $unit, $postRequest);
        return true;
      } catch (\Exception $e) {
        throw $e;
      }
    }
    return false;
  }


  /**
   * @param RenderAPI  $renderApi
   * @param Unit       $unit
   * @param ModuleInfo $moduleInfo
   */

  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $isNotRedirect = ($renderApi->getFormValue($unit, 'confirmationType') != 'redirect');
    list($form, $honeyPotComponent) = $this->setupForm($unit);

    $formSend = false;
    try {
      $formSend = $this->checkFormAndSendMail($renderApi, $unit, $honeyPotComponent);
    } catch (\Exception $e) {
      $errorText = new \Span();
      $errorText->setContent("Unable to send email:<br />" . $e->getMessage());
      $errorContainer = new \Container();
      $errorContainer->add($errorText);
      $errorContainer->getElementProperties()->addClass('vf__main_error');
      $form->add($errorContainer);
    }

    if ($formSend) {
      if ($isNotRedirect) {
        $confirmationText = new \Span();
        $confirmationText->setContent(preg_replace('/\n/', '<br>', $renderApi->getFormValue($unit, 'confirmationText')));
        $confirmationContainer = new \Container();
        $confirmationContainer->add($confirmationText);
        $confirmationContainer->getElementProperties()->addClass('confirmationText');
        $form->add($confirmationContainer);
        echo $form->renderElement();
      }
    } else {
      echo $form->renderElementProgressive($renderApi, $unit);
    }
  }

  /**
   * @param RenderAPI $renderApi
   * @param Unit      $unit
   * @param array     $postRequest <FormValueSetSet>
   */
  private function sendEmail($renderApi, $unit, $postRequest)
  {
    $senderEmail = $renderApi->getFormValue($unit, 'senderMail');
    $senderEmailUnitId = $this->findSenderEmailUnitId($renderApi, $unit);

    $realSenderEmail = '';
    foreach ($postRequest as $formValueSet) {
      if ($senderEmailUnitId == $formValueSet->getKey()) {
        $realSenderEmail = $formValueSet->getValue();
      }
    }

    if ($renderApi->getFormValue($unit, 'enableAutoresponder') && ($realSenderEmail !== '')) {
      $autoresponderText = $renderApi->getFormValue($unit, 'autoresponderText');
      $autoresponderText = '<html><head><title></title></head><body>'.preg_replace('/\\n/', "<br>", $this->getAutoresponderText($postRequest, $autoresponderText)).'</body></html>';
      $autoresponderSubject = $renderApi->getFormValue($unit, 'autoresponderSubject');
      $autoresponderSubject = $this->getAutoresponderText($postRequest, $autoresponderSubject);
      $mailer = new Mailer($renderApi);
      $mailer->setFrom($senderEmail);
      $mailer->addTo($realSenderEmail);
      $mailer->setSubject($autoresponderSubject);
      $mailer->setHtmlBody($autoresponderText);
      $mailer->send();
    }
    if ($realSenderEmail !== '') {
      $senderEmail = $realSenderEmail;
    }
    $mailer = new Mailer($renderApi);
    $mailer->setFrom($senderEmail);

    $recipientMail = explode(",", $renderApi->getFormValue($unit, 'recipientMail'));
    foreach ($recipientMail as $email) {
      $mailer->addTo(trim($email));
    }

    $mailer->setSubject($renderApi->getFormValue($unit, 'mailSubject'));
    $mailer->setHtmlBody($this->getMailBody($postRequest));
    $mailer->send();

  }



  /**
   * @param array $postRequest <FormValueSetSet>
   *
   * @return string
   */
  private function getMailBody($postRequest)
  {
    $ignoreKeys = array('formUnitIdentifier', \HoneyPotComponent::HONEY_POT_NAME);

    $message = '<html><head><title></title></head><body>';
    foreach ($postRequest as $formValueSet) {
      /*@var $formValueSet FormValueSetSet */
      if (!in_array($formValueSet->getKey(), $ignoreKeys)) {
        $value = $formValueSet->getValue();
        if (is_array($value)) {
          $value = join(", ", $value);
        }
        $message .= $formValueSet->getName().": ".preg_replace('/\\n/', "<br>", $value)."<br>";
      }
    }
    $message .= '</body></html>';

    return $message;
  }

  /**
   * @param array $postRequest <FormValueSetSet>
   *
   * @return string
   */
  private function getAutoresponderText($postRequest, $autoresponderText)
  {

    $ignoreKeys = array('formUnitIdentifier', \HoneyPotComponent::HONEY_POT_NAME);
    foreach ($postRequest as $formValueSet) {
      /*@var $formValueSet FormValueSetSet */
      if (!in_array($formValueSet->getKey(), $ignoreKeys)) {
        $key = preg_quote($formValueSet->getName(), "/");
        $pattern = '/{{'.$key.'}}/';
        $value = $formValueSet->getValue();
        if (is_array($value)) {
          $value = join(", ", $value);
        }
        $autoresponderText = preg_replace($pattern, $value, $autoresponderText);
      }
    }
    $pattern = '/{{(.*?)}}\n/';
    $autoresponderText = preg_replace($pattern, '', $autoresponderText);
    $pattern = '/{{(.*?)}}/';
    $autoresponderText = preg_replace($pattern, '', $autoresponderText);
    return $autoresponderText;
  }

  /**
   * Validate form data
   *
   * @param $renderApi
   * @param $unit
   *
   * @return bool
   */
  private function hasValidFormData($renderApi, $unit)
  {
    $result = true;
    foreach ($this->formSubmit->getPostValues() as $postValue) {
      if ($thisUnit = $renderApi->getUnitById($postValue->getKey())) {
        $validation = new \Validation();
        if (!$validation->isValidValue($thisUnit, $postValue->getValue())) {
          $result = false;
          break;
        }
      }
    }

    if (!$this->compareFormUnits($renderApi, $unit)) {
      $result = false;
    }

    return $result;
  }

  /**
   * Compare the unit form field collection with post values.
   *
   * @param $renderApi
   * @param $unit
   *
   * @return boolean false if post values do not contain a unit form field
   */
  private function compareFormUnits($renderApi, $unit)
  {
    $result = true;
    $this->collectUnitFormFields($renderApi, $unit);
    foreach ($this->formUnits as $formUnit) {
      $formValues = $formUnit->getFormValues();
      // workaround to catch non-required not selected checkboxes
      if (!isset($formValues['enableRequired']) || !$formValues['enableRequired']) {
        $found = true;
      } else {
        $found = array_filter($this->formSubmit->getPostValues(), function ($postValue) use (&$formUnit) {
          return $postValue->getKey() === $formUnit->getId();
        });
      }
      if (!$found) {
        $result = false;
        break;
      }
    }
    return $result;
  }

  /**
   * Collect all valid unit form fields.
   *
   * @param      $renderApi
   * @param Unit $unit
   */
  private function collectUnitFormFields($renderApi, Unit $unit)
  {
    foreach ($renderApi->getChildren($unit) as $child) {
      /*@var $child Unit */
      if (strstr($child->getModuleId(), self::MODULE_ID_RZ_FORM_FIELD)) {
        $this->formUnits[] = $child;
      } else if ($child) {
        $this->collectUnitFormFields($renderApi, $child);
      }
    }
  }

  /**
   * look for text field marked as email sender address.
   *
   * @param      $renderApi
   * @param Unit $unit
   */
  private function findSenderEmailUnitId($renderApi, Unit $unit)
  {
    foreach ($renderApi->getChildren($unit) as $child) {
      /*@var $child Unit */
      if (strstr($child->getModuleId(), self::MODULE_ID_RZ_FORM_FIELD)) {
        if ($renderApi->getFormValue($child, 'isSenderEmail')) {
          return $child->getId();
        }
      } else if ($child) {
        $unitId = $this->findSenderEmailUnitId($renderApi, $child);
        if ($unitId) {
          return $unitId;
        }
      }
    }
    return null;
  }

}