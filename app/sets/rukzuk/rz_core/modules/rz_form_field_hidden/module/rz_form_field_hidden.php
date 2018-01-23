<?php
namespace Rukzuk\Modules;

use Render\Unit;
use Rukzuk\Modules\ChildModuleDependency;

class rz_form_field_hidden extends SimpleModule{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent( $renderApi, $unit, $moduleInfo ) {

    $child = new ChildModuleDependency();
    if ($child->isInsideModule($renderApi, $unit, 'rz_form')) {
      $this->renderFormFieldContent($renderApi, $unit);
    } else {
      $i18n = new Translator($renderApi, $moduleInfo);
      $msg = $i18n->translate('error.moduleOnlyWorkingInForm');
      $errorTag = new HtmlTagBuilder('div', array(
        'class' => 'RUKZUKmissingInputHint'
      ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
      echo $errorTag->toString();
    }
  }

  private function renderFormFieldContent($renderApi, $unit){

    $fieldId    = 'field' . $unit->getId();
    $properties  = $unit->getFormValues();
    $value = '';

    if ($properties['type'] == 'text') {
      $value = $properties["text"];
    } else {
      $nav = $renderApi->getNavigation();
      $currentPage = $nav->getPage($nav->getCurrentPageId());
      if ($properties['pagePropertyType'] == 'title') {
        $value = $currentPage->getTitle();
      } else if ($properties['pagePropertyType'] == 'description') {
        $value = $currentPage->getDescription();
      } else {
        $value = strftime('%d.%m.%Y', $currentPage->getDate());
      }
    }

    $formField = new \TextField();
    $elementProperties = $formField->getElementProperties();
    $elementProperties->setId( $fieldId );
    $elementProperties->addAttribute("name", $fieldId );
    $elementProperties->addAttribute("type", \InputType::HIDDEN);
    $elementProperties->addAttribute("value", $value );

    $wrapper = new \Container();
    $wrapper->add($formField);
    echo $wrapper->renderElement();

    $renderApi->renderChildren( $unit );
  }

}