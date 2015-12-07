<?php
require_once( dirname( __FILE__ ) . "/IOptionProvider.php" );
require_once( dirname( __FILE__ ) . "/IOption.php" );
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
class OptionProvider implements IOptionProvider{

	/**
	 * @var IOption[]
	 */
	private $options = null;

	public function __construct() {
		$this->options = array();
	}

	public function addOption( IOption $option ){
		$this->options[] = $option;
	}

	public function getOptions(){
		return $this->options;
	}

	public function hasOptions(){
		$result = false;
		if(count($this->options)>0){
			$result = true;
		}
		return $result;
	}
}
