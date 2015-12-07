<?php
namespace Rukzuk\Modules;

use Render\Unit;
use Rukzuk\Modules\ChildModuleDependency;

class rz_form_field_text extends SimpleModule
{

  /**
   * @var \FormSubmit
   */
  private $formSubmit = null;

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $child = new ChildModuleDependency();
    if ($child->isInsideModule($renderApi, $unit, 'rz_form')) {
      $this->renderFormFieldContent($renderApi, $unit);
    } else {
      $i18n = new Translator($renderApi, $moduleInfo);
      $msg = $i18n->translate('error.moduleOnlyWorkingInForm');
      $errorTag = new HtmlTagBuilder('div', array('class' => 'RUKZUKmissingInputHint'), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
      echo $errorTag->toString();
    }
  }

  private function renderFormFieldContent($renderApi, $unit)
  {
    $this->formSubmit = new \FormSubmit();
    $fieldId = 'field' . $unit->getId();
    $properties = $unit->getFormValues();
    $labelText = $properties["fieldLabel"];
    $fieldType = $properties["textType"]; //input,list,textarea
    $postRequest = $this->getPostValue($unit);

    if (($properties['type'] === \InputType::STRING && $fieldType !== FieldType::TEXTAREA)
      || $properties['type'] === \InputType::EMAIL
      || $properties['type'] === \InputType::NUMERIC
    ) {
      $formField = new \TextField();
      $elementProperties = $formField->getElementProperties();
      $elementProperties->setId($fieldId);
      $elementProperties->addAttribute("name", $fieldId);
      $elementProperties->addAttribute('value', $postRequest);

      if (isset($properties['type'])) {
        if ($properties['type'] === \InputType::EMAIL) {
          $elementProperties->addAttribute("type", \InputType::EMAIL);
        }
        if ($properties['type'] === \InputType::NUMERIC) {
          $elementProperties->addAttribute("type", \InputType::NUMERIC);
        }
      }
    } elseif ($fieldType === FieldType::TEXTAREA) {
      $formField = new \TextareaField();
      $elementProperties = $formField->getElementProperties();
      $elementProperties->setId($fieldId);
      $elementProperties->addAttribute("name", $fieldId);
      $formField->setContent($postRequest);
    }

    $label = new \Label();
    $labelProperties = $label->getElementProperties();
    $labelProperties->addAttribute("for", $fieldId);
    $label->add(new \Span($labelText));

    if ($formField) {
      $wrapper = new \Container();
      $wrapper->add($label);
      $wrapper->add($formField);
      $elementProperties = $formField->getElementProperties();
      if ($this->formSubmit->isValid($renderApi, $unit) && !$this->isValidValue($unit, $postRequest)) {
        $elementProperties->addClass('vf__error');
        $wrapper->add($this->getErrorMessage($unit, $postRequest));
      }
      $this->setRequiredField($renderApi, $unit, $elementProperties);
      $this->setPlaceholderText($renderApi, $unit, $elementProperties);
      echo $wrapper->renderElement();
    }

    $renderApi->renderChildren($unit);
  }

  /**
   * @param $unit
   * @return \AbstractComponent
   */
  private function getErrorMessage($unit)
  {
    $errorMsg = new \Paragraph();
    $properties = $errorMsg->getElementProperties();
    $properties->addClass('vf__error');
    $validation = new \Validation();
    $errorMsg->setContent($validation->getNotVaildValueMessage($unit));

    return $errorMsg;
  }

  private function isValidValue($unit, $postRequest)
  {
    $result = true;
    $validation = new \Validation();
    if (!$validation->isValidValue($unit, $postRequest)) {
      $result = false;
    }

    return $result;
  }

  private function setPlaceholderText($renderApi, $unit, $elementProperties)
  {
    if ($renderApi->getFormValue($unit, 'enablePlaceholder')) {
      $elementProperties->addAttribute("placeholder", $renderApi->getFormValue($unit, 'placeholderText'));
    }
  }

  private function setRequiredField($renderApi, $unit, $elementProperties)
  {
    if ($renderApi->getFormValue($unit, 'enableRequired')) {
      $elementProperties->addAttribute("required", null);
    }
  }

  private function getPostValue(Unit $unit)
  {
    foreach ($this->formSubmit->getPostValues() as $value) {
      if ($value->getKey() === $unit->getId()) {
        return $value->getValue();
      }
    }
  }

}
