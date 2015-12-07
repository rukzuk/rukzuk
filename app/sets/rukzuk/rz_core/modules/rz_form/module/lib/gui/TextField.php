<?php
require_once(dirname(__FILE__)."/AbstractComponent.php");
require_once(dirname(__FILE__)."/ElementProperties.php");

/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class TextField extends AbstractComponent{

	const INPUT_TYPE  = "text";
	const ELEMENT_TAG = "input";

	/**
	 * @var IElementProperties
	 */
	private $elementProperties = null;

	public function __construct(){
		$this->elementProperties = new ElementProperties();
		$this->elementProperties->addAttribute("type", self::INPUT_TYPE);
	}

	public function getElementProperties() {
		return $this->elementProperties;
	}

	protected function getElementTag() {
		return self::ELEMENT_TAG;
	}

}
