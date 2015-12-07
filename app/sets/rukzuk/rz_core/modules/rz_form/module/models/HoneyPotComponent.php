<?php

require_once( dirname( __FILE__ ) . "/../lib/gui/TextField.php" );
require_once( dirname( __FILE__ ) . "/IHoneyPotComponent.php" );



class HoneyPotComponent implements IHoneyPotComponent {

	const HONEY_POT_NAME = 'email_x';

	public function getHoneyPot(){
		$formField = new \TextField();
		$elementProperties = $formField->getElementProperties();
		$elementProperties->addAttribute( 'name', self::HONEY_POT_NAME );
		$elementProperties->addAttribute('value', '');
		$elementProperties->addAttribute('class', 'rz_form_field_hide');
		return $formField;
	}

	public function isValidHoneyPot( array $postValues ){
		$result = false;
		foreach( $postValues as $value ){
			if( $value->getKey() === self::HONEY_POT_NAME && strlen( $value->getValue() ) === 0 ){
				$result = true;
				break;
			}
		}
		return $result;
	}

	public function getFormUnitIdentifier( $unitId ){
		$formField = new \TextField();
		$elementProperties = $formField->getElementProperties();
		$elementProperties->addAttribute( 'name', 'formUnitIdentifier' );
		$elementProperties->addAttribute( 'value', $unitId );
		$elementProperties->addAttribute( 'class', 'rz_form_field_hide' );
		return $formField;
	}
} 