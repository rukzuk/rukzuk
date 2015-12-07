<?php
require_once(dirname(__FILE__)."/AbstractComponent.php");
require_once(dirname(__FILE__)."/ElementProperties.php");

/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class OptionField extends AbstractComponent{

	const ELEMENT_TAG = "option";

	/**
	 * @var IElementProperties
	 */
	private $elementProperties = null;

	public function __construct(){
		$this->elementProperties = new ElementProperties();
	}

	public function getElementProperties() {
		return $this->elementProperties;
	}

	public function setContent($content){
		parent::setContent($content);
	}

	protected function getElementTag() {
		return self::ELEMENT_TAG;
	}
}
