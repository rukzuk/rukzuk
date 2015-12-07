<?php
namespace Rukzuk\Modules;
use Render\Unit;
use Rukzuk\Modules\ChildModuleDependency;

class rz_form_field_select extends SimpleModule{

	/**
	 * @var \FormSubmit
	 */
	private $formSubmit = null;

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

		$this->formSubmit 	= new \FormSubmit();
		$fieldId     = 'field' . $unit->getId();
		$properties  = $unit->getFormValues();
		$labelText   = $properties["fieldLabel"];
		$listType    = $properties["listType"]; //select, checkbox, radio
		$postRequest = $this->getPostValue($unit);

		$choiceBox = new \ChoiceBox();
		if( $listType === \ListType::RADIO || $listType === \ListType::CHECKBOX ){
			$required = $renderApi->getFormValue($unit, 'enableRequired');
			$formField = $choiceBox->getRadioCheckbox($renderApi, $unit, $fieldId, $postRequest, $required);
		}elseif( $listType === \ListType::DROP_DOWN ){
			$formField = $choiceBox->getSelectField($renderApi, $unit, $fieldId, $postRequest);
		}

		$label = new \Label();
		$labelProperties = $label->getElementProperties();
		$labelProperties->addAttribute( "for", $fieldId );
		$label->add( new \Span( $labelText ) );

		if( $formField ) {
			$elementProperties = $formField->getElementProperties();
			$wrapper = new \Container();
			$wrapper->add( $label );
			$wrapper->add( $formField );
			echo $wrapper->renderElement();
		}
		$renderApi->renderChildren( $unit );
	}

	private function getPostValue( Unit $unit ){
		foreach( $this->formSubmit->getPostValues() as $value){
			if( $value->getKey() === $unit->getId()){
				return $value->getValue();
			}
		}
	}

}
