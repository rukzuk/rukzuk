<?php
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
interface IOptionProvider {

	/**
	 * Add Option object to the option provider.
	 *
	 * @param IOption $option
	 */
	public function addOption( IOption $option );

	/**
	 * Get a collection of all options.
	 *
	 * @return Option[]
	 */
	public function getOptions();

	/**
	 * Check whether the option provider has options.
	 *
	 * @return boolean
	 */
	public function hasOptions();

}
