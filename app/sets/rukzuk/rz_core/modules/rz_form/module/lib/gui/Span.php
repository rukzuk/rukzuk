<?php
require_once(dirname(__FILE__)."/AbstractComponent.php");
require_once(dirname(__FILE__)."/ElementProperties.php");

/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class Span extends AbstractComponent{

	const ELEMENT_TAG = "span";

	/**
	 * @var IElementProperties
	 */
	private $elementProperties = null;

	public function __construct( $content = null ){
		$this->elementProperties = new ElementProperties();
		if( !is_null($content)){
			parent::setContent($content);
		}
	}

	public function getElementProperties() {
		return $this->elementProperties;
	}

	protected function getElementTag() {
		return self::ELEMENT_TAG;
	}

	/**
	 * @param null $content
	 */
	public function setContent( $content ) {
		parent::setContent($content);
	}

}
