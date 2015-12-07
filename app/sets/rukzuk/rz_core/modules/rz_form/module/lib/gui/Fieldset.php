<?php
require_once(dirname(__FILE__)."/AbstractCompositeComponent.php");
require_once(dirname(__FILE__)."/ElementProperties.php");

/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class Fieldset extends AbstractCompositeComponent{

	const ELEMENT_TAG = "fieldset";

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

	protected function getElementTag() {
		return self::ELEMENT_TAG;
	}
}
