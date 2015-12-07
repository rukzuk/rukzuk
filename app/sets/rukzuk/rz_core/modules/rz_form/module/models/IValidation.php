<?php
/**
 */
interface IValidation {

	/**
	 * Validate given value.
	 *
	 * @param $unit
	 * @param $value
	 * @return boolean
	 */
	public function isValidValue($unit, $value);

	/**
	 * Get corresponding error message for not valid value.
	 *
	 * @param $unit
	 * @return string
	 */
	public function getNotVaildValueMessage($unit);
} 