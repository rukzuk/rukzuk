<?php
/**
 * @package      Rukzuk\Modules\rz_form_field
 */

interface IElementProperties {

	/**
	 * Get the identifier of this element object.
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Set the identifier for this element object.
	 *
	 * @param string $id
	 */
	public function setId( $id );

	/**
	 * Get a collection of style classes.
	 *
	 * @return array<string>
	 */
	public function getClass();

	/**
	 * Add a style class to the style class collection.
	 *
	 * @param string $class
	 */
	public function addClass( $class );

	/**
	 * Add further attribute to this element object.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addAttribute( $key, $value );

	/**
	 * Get a collection of all attributes of this element object.
	 *
	 * @return array<attributeName, attributeValue>
	 */
	public function getAttributes();

	/**
	 * Render the
	 *
	 * @return string
	 */
	public function render();
} 
