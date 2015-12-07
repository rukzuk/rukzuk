<?php
/**
 */
Interface IRequest {

	/**
	 * Return a collection of post values in (key,value) pair.
	 *
	 * @return array<key,value>
	 */
	public function getPostValues();

	/**
	 * Check for post request
	 *
	 * @return boolean
	 */
	public function isPostRequest();
} 