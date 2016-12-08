<?php
namespace Rukzuk\Modules;

use Render\Unit;
use Rukzuk\Modules\ChildModuleDependency;

class rz_form_field_button extends SimpleModule{

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

		$buttonId    = 'field' . $unit->getId();
		$properties  = $unit->getFormValues();
		$buttonLabel = $properties["fieldLabel"];

		$formField = new \ButtonField();
		$elementProperties = $formField->getElementProperties();
		$elementProperties->setId( $buttonId );
		$elementProperties->addClass( "submitButton" );
		//$elementProperties->addAttribute( "name", $buttonId );
		$elementProperties->addAttribute( "value", $buttonLabel );

		$wrapper = new \Container();
		$wrapper->add($formField);
		echo $wrapper->renderElement();

		$renderApi->renderChildren( $unit );
	}

}
