<?php
require_once( dirname( __FILE__ ) . "/../lib/validation/FormValidation.php" );
require_once( dirname( __FILE__ ) . "/../lib/enum/InputType.php" );
require_once( dirname( __FILE__ ) . "/IValidation.php" );

/**
 */
class Validation implements IValidation{

	/**
	 * @var IFormValidation
	 */
	private $formValidation = null;

	public function __construct(){
		$this->formValidation = new \FormValidation();
	}

	public function isValidValue($unit, $value){
		$result = true;
		$formValues = $unit->getFormValues();
		if(isset($formValues['type'])) {
			if( $formValues['enableRequired'] && !$this->formValidation->isFilled( $value ) ) {
				$result = false;
			} elseif( $formValues['type'] === InputType::EMAIL && ( !$this->formValidation->isValidEmailAddress( $value ) || !$this->formValidation->isFilled( $value ) ) ) {
				$result = false;
			} elseif( $formValues['type'] === InputType::NUMERIC && !$this->formValidation->isNumeric( $value ) ) {
				$result = false;
			}
		}else if(isset($formValues['listType'])) {
			if( $formValues['enableRequired'] && is_null( $value ) ) {
				$result = false;
			}
		}
		return $result;
	}

	public function getNotVaildValueMessage($unit){
		$result = '';
		$formValues = $unit->getFormValues();
		if(isset($formValues['type']) && $formValues['type'] === InputType::EMAIL) {
			$result = $formValues['inputTypeErrorEmail'];
		}elseif(isset($formValues['type']) && $formValues['type'] === InputType::NUMERIC) {
			$result = $formValues['inputTypeErrorNumeric'];
		}elseif($formValues['enableRequired']) {
			$result = $formValues['requiredErrorMessage'];
		}
		return $result;
	}

} 