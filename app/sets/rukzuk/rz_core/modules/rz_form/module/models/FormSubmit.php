<?php

require_once( dirname( __FILE__ ) . "/../lib/http/Request.php" );
require_once( dirname( __FILE__ ) . "/../lib/mailer/FormValueSet.php" );

/**
 */
class FormSubmit {

	private $http = null;

	private $postValues = null;

	private $formUnitIdentifier = null;

	public function __construct(){
		$this->postValues = array();
		$this->http = new \Request();
		$this->setPostRequestFormValues();
	}

	public function isValid( $renderApi, $unit ){
		$parentlookup = new \Rukzuk\Modules\ChildModuleDependency();
		return ($parentlookup->isInsideUnit( $renderApi, $unit, $this->formUnitIdentifier ))?true:false;
	}

	public function setFieldLabelsToFormValueSet( $renderApi ){
		foreach( $this->postValues as $postValue ){
			if( $unit = $renderApi->getUnitById( $postValue->getKey() ) ){
				$postValue->setName( $renderApi->getFormValue( $unit, 'fieldLabel') );
			}
		}
	}

	/**
	 * @return FormValueSet[]
	 */
	public function getPostValues(){
		return $this->postValues;
	}

	private function setPostRequestFormValues(){
		$postRequest = array();
		if( $this->http->isPostRequest() ){
			$postValues = $this->http->getPostValues();
			foreach( $postValues as $key => $value ){
				if( $key === "formUnitIdentifier" ){
					$this->formUnitIdentifier = $value;
				}
				$formValue = new \FormValueSet();
				$formValue->setKey( $key );
				$formValue->setValue( $value );
				$postRequest[] = $formValue;
			}
		}
		$this->postValues = $postRequest;
	}
} 
