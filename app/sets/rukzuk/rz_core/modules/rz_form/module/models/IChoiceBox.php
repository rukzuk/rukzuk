<?php
require_once( dirname( __FILE__ ) . "/IListOptions.php" );

/**
 */
interface IChoiceBox {

	/**
	 * Get either radio buttons or checkboxes corresponding to the give values.
	 *
	 * @param $renderApi
	 * @param $unit
	 * @param $fieldId
	 * @param $postRequestValue
	 * @return \AbstractCompositeComponent
	 */
	public function getRadioCheckbox( $renderApi, $unit, $fieldId, $postRequestValue = null );

	/**
	 * Get drop-down box corresponding to the give values
	 *
	 * @param $renderApi
	 * @param $unit
	 * @param $fieldId
	 * @param $postRequestValue
	 * @return \AbstractCompositeComponent
	 */
	public function getSelectField( $renderApi, $unit, $fieldId, $postRequestValue = null );

	/**
	 * Set list option
	 *
	 * @param IListOptions $listOptions
	 */
	public function setListOptions( IListOptions $listOptions );

} 