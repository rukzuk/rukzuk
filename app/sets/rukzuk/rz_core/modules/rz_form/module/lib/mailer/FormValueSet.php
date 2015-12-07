<?php
require( dirname( __FILE__ ) . "/IFormValueSet.php" );
/**
 */
class FormValueSet implements IFormValueSet {

	private $key = null;
	private $name = null;
	private $value = null;

	/**
	 * @return null
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @param null $key
	 */
	public function setKey( $key ) {
		$this->key = $key;
	}

	/**
	 * @return null
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param null $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @return null
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param null $value
	 */
	public function setValue( $value ) {
		$this->value = $value;
	}

	public function __toString(){
		$contentBuffer = '';
		if(is_array($this->getValue())){
			$contentBuffer .= "\r\n<br>". $this->getName() . ": " . implode(', ', $this->getValue());
		}else{
			$contentBuffer .= "\r\n<br>". $this->getName() . ": " . $this->getValue();
		}
		return $contentBuffer;
	}

} 