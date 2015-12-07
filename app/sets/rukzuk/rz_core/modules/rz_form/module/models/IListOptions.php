<?php

/**
 */
interface IListOptions {

	/**
	 * @param $renderApi
	 * @param $unit
	 * @return IOptionProvider
	 */
	public function getListOptions( $renderApi, $unit );

} 