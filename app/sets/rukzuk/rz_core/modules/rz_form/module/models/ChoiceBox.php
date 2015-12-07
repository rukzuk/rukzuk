<?php
require_once( dirname( __FILE__ ) . "/ListOptions.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/Fieldset.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/RadioButtonField.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/Paragraph.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/Span.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/Label.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/CheckboxField.php" );
require_once( dirname( __FILE__ ) . "/../lib/enum/ListType.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/SelectField.php" );
require_once( dirname( __FILE__ ) . "/../lib/gui/OptionField.php" );
require_once( dirname( __FILE__ ) . "/../models/Validation.php" );
require_once( dirname( __FILE__ ) . "/IChoiceBox.php" );
require_once( dirname( __FILE__ ) . "/IListOptions.php" );
require_once( dirname( __FILE__ ) . "/../lib/http/Request.php" );
require_once( dirname( __FILE__ ) . "/../models/FormSubmit.php" );
/**
 */
class ChoiceBox implements IChoiceBox{

	/**
	 * @var IListOptions
	 */
	private $listOptions = null;

	/**
	 * @var \FormSubmit
	 */
	private $formSubmit = null;

	public function __construct(){
		$this->setListOptions(new ListOptions());
		$this->formSubmit 	= new \FormSubmit();
	}

	public function getRadioCheckbox( $renderApi, $unit, $fieldId, $postRequestValue = null, $required = false ){
		$formField = new \Fieldset();
		$listOptions = $this->listOptions->getListOptions($renderApi, $unit);
		$inputName = ( strlen( $renderApi->getFormValue( $unit, 'inputName' ) ) > 0 ) ? $renderApi->getFormValue( $unit, 'inputName' )."[]" : $fieldId."[]";

		if($listOptions->hasOptions()){
            $optionCount = 0;
            $options = $listOptions->getOptions();
            $optionsLength = count($options);
			foreach($options as $option) {
				/* @var $option \Option */
				$properties = $unit->getFormValues();
				if( $properties["listType"] === \ListType::RADIO ){
					$choiceField = new \RadioButtonField();
				}elseif( $properties["listType"] === \ListType::CHECKBOX ){
					$choiceField = new \CheckboxField();
				}

                $optionId = $fieldId.'_'.$optionCount;

				$elementProperties = $choiceField->getElementProperties();
				$elementProperties->addAttribute("value", $option->getValue());
				$elementProperties->addAttribute( "name", $inputName );
				$elementProperties->addAttribute("id", $optionId);

				// set required attribute for radio options or when there is only one checkbox
				// don't set for multiple checkboxes to match server-side validation logic
				if ($required && ($properties["listType"] === \ListType::RADIO || $optionsLength === 1)) {
					$elementProperties->addAttribute("required", null);
				}

				$request = new Request();
				$request->isPostRequest();

				if((!$request->isPostRequest() && $option->isChecked()) || (!is_null($postRequestValue) && in_array($option->getValue(), $postRequestValue))) {
					$elementProperties->addAttribute( "checked", null );
				}

				$label = new \Label();
				$label->add($choiceField);
				$label->add(new \Span($option->getName()));
				$labelProperties = $label->getElementProperties();
				$labelProperties->addAttribute("for", $optionId);
				$formField->add($label);

                $optionCount++;
			}
		}
		if( $this->formSubmit->isValid( $renderApi, $unit ) && !$this->isValidValue( $unit, $postRequestValue ) ){
			$formField->add( $this->getErrorMessage( $unit, $postRequestValue ) );
			$formField->getElementProperties()->addClass('vf__error');
		}
		return $formField;
	}

	public function getSelectField( $renderApi, $unit, $fieldId, $postRequestValue = null ){
		$inputName = ( strlen( $renderApi->getFormValue( $unit, 'inputName' ) ) > 0 ) ? $renderApi->getFormValue( $unit, 'inputName' ) : $fieldId;
		$formField = new \SelectField();
		$formField->getElementProperties()->addAttribute( "name", $inputName );
		$formField->getElementProperties()->addAttribute( "id", $fieldId );
		$listOptions = $this->listOptions->getListOptions($renderApi, $unit);
		if($listOptions->hasOptions()){
			foreach($listOptions->getOptions() as $option) {
				/* @var $option \Option */
				$optionField = new \OptionField();
				$optionField->setContent($option->getName());
				$elementProperties = $optionField->getElementProperties();
				$elementProperties->addAttribute("value", $option->getValue());
				if((is_null($postRequestValue) && $option->isChecked()) || $postRequestValue === $option->getValue()) {
					$elementProperties->addAttribute( "selected", null );
				}
				$formField->add($optionField);
			}
		}
		if( $this->formSubmit->isValid( $renderApi, $unit ) && !$this->isValidValue( $unit, $postRequestValue ) ){
			$formField->getElementProperties()->addClass('vf__error');
		}
		return $formField;
	}

	/**
	 * @param $unit
	 * @return \AbstractComponent
	 */
	private function getErrorMessage( $unit ){
		$errorMsg = new \Paragraph();
		$properties = $errorMsg->getElementProperties();
		$validation = new \Validation();
		$properties->addClass( 'vf__error' );
		$errorMsg->setContent( $validation->getNotVaildValueMessage( $unit ) );
		return $errorMsg;
	}

	private function isValidValue($unit, $postRequest){
		$result = true;
		$validation = new \Validation();
		if(!$validation->isValidValue($unit, $postRequest)){
			$result = false;
		}
		return $result;
	}

	public function setListOptions(IListOptions $listOptions){
		$this->listOptions = $listOptions;
	}

}
