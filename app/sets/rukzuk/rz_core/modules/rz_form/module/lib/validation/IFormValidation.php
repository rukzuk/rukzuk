<?php
/**
 */
interface IFormValidation {

	/**
	 * Check if given email address is valid
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isValidEmailAddress($value);

	/**
	 * Check whether the given value contains at
	 * least one character.
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isFilled($value);

	/**
	 * Check whether the given value is numeric.
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isNumeric($value);
} 
