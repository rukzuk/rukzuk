<?php
require_once( dirname( __FILE__ ) . "/IOption.php" );
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class Option implements IOption{

	/**
	 * @var null
	 */
	private $name = null;
	/**
	 * @var null
	 */
	private $value = null;
	/**
	 * @var null
	 */
	private $checked = false;

	/**
	 * @return null
	 */
	public function isChecked() {
		return $this->checked;
	}

	/**
	 * @param null $checked
	 */
	public function setChecked( $checked ) {
		$this->checked = $checked;
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



}
