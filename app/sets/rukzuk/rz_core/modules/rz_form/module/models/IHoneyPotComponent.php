<?php


interface IHoneyPotComponent {

	/**
	 * Get honeypot component
	 *
	 * @return \AbstractComponent
	 */
	public function getHoneyPot();

	/**
	 * Get Form Unit Identifier component
	 *
	 * @param string $unitId
	 * @return \AbstractComponent
	 */
	public function getFormUnitIdentifier( $unitId );

	/**
	 * Validate honeypot
	 *
	 * @param array $postValues
	 * @return boolean
	 */
	public function isValidHoneyPot( array $postValues );
} 