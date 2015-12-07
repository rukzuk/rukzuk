<?php
require_once(dirname(__FILE__)."/AbstractComponent.php");
require_once(dirname(__FILE__)."/ElementProperties.php");

/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class TextareaField extends AbstractComponent{

	const ELEMENT_TAG = "textarea";

	/**
	 * @var IElementProperties
	 */
	private $elementProperties = null;

	public function __construct(){
		$this->elementProperties = new ElementProperties();
	}

	public function setContent( $content ){
		parent::setContent( $content );
	}

	public function getElementProperties() {
		return $this->elementProperties;
	}

	protected function getElementTag() {
		return self::ELEMENT_TAG;
	}

} 
