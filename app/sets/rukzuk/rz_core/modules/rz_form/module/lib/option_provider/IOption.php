<?php
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
interface IOption {

	/**
	 * Check whether this option is default checked.
	 *
	 * @return boolean
	 */
	public function isChecked();

	/**
	 * Set the state checked for this option
	 *
	 * @param boolean $checked
	 */
	public function setChecked( $checked );

	/**
	 * Get the name of this option
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Set name for this option
	 *
	 * @param string $name
	 */
	public function setName( $name );

	/**
	 * Get value of this option
	 *
	 * @return string
	 */
	public function getValue();

	/**
	 * Set the value for this option
	 *
	 * @param string $value
	 */
	public function setValue( $value );
}
