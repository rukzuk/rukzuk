<?php
require(dirname(__FILE__)."/IElementProperties.php");
/**
 * @package      Rukzuk\Modules\rz_form_field
 */

class ElementProperties implements IElementProperties{

	/**
	 * @var string
	 */
	private $id = null;

	/**
	 * @var array
	 */
	private $class = array();
	
	/**
	 * @var array
	 */
	private $attributes = array();

	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		$this->id = $id;
		$this->addAttribute( "id", $this->id );
	}

	public function getClass() {
		return $this->class;
	}

	public function addClass( $class ) {
		\array_push( $this->class, $class );
		$this->addAttribute( "class", \implode( " ", $this->class ) );
	}

	public function addAttribute( $key, $value ) {
		$this->attributes[$key] = $value;
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function render(){
		$attributes = $this->getAttributes();
		$attributes = implode( ' ', array_map( function ($v, $k) {
          $return = $k;
          if (!is_null($v)) {
            $return .= '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
          }
          return $return;
		}, $attributes, array_keys( $attributes ) ) );
		return ( strlen( $attributes ) > 1 ) ? ' ' . $attributes : '';
	}

} 
