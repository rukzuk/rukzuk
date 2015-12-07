<?php
require( dirname( __FILE__ ) . "/IFormValidation.php" );
/**
 */
class FormValidation implements IFormValidation{

	public function isValidEmailAddress( $value ) {
		$result = true;
		if( filter_var( $value, FILTER_VALIDATE_EMAIL ) === false ) {
			$result = false;
		}

		return $result;
	}

	public function isFilled($value){
		return (strlen($value)>0)?true:false;
	}

	public function isNumeric($value){
		return (is_numeric($value))?true:false;
	}

} 
