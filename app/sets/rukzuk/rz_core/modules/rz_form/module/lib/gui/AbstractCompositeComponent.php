<?php
require_once(dirname(__FILE__)."/AbstractComponent.php");
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
abstract class AbstractCompositeComponent extends AbstractComponent{

	/**
	 * @var AbstractComponent[]
	 */
	private $children = array();

	public function add( AbstractComponent $child ){
		$this->children[] = $child;
	}

	public function renderElement( $children = null ){
		$output = '';
		foreach($this->children as $child){
			$output .= $child->renderElement();
		}
		return parent::renderElement( $output );
	}

	protected function setStaticContent( $content ){
		parent::setContent( $content );
	}

	public function renderElementProgressive( $renderApi, $unit ){
		$output = '';
		foreach($this->children as $child){
			$output .= $child->renderElement();
		}
		parent::setContent($output);
		return parent::renderElementProgressive( $renderApi, $unit );
	}

}
