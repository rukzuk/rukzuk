<?php
require_once( dirname( __FILE__ ) . "/../lib/option_provider/OptionProvider.php" );
require_once( dirname( __FILE__ ) . "/../lib/option_provider/Option.php" );
require_once( dirname( __FILE__ ) . "/IListOptions.php" );
/**
 */
class ListOptions implements IListOptions{

	/**
	 * @var IOptionProvider
	 */
	private $optionProvider = null;

	function __construct() {
		$this->optionProvider = new \OptionProvider();
	}

	/**
	 * @param $renderApi
	 * @param $unit
	 * @return IOptionProvider
	 */
	public function getListOptions( $renderApi, $unit ){
		$options = preg_split('/\n/', $renderApi->getFormValue($unit, 'listFieldOptions'));
		foreach ($options as $option) {
			$checked = false;
			if (preg_match('/\*$/', $option)) {
				$checked = true;
				$option = preg_replace('/\*$/', '', $option);
			}
			$optionObj = new \Option();
			$optionObj->setName($option);
			$optionObj->setValue($option);
			$optionObj->setChecked($checked);
			$this->optionProvider->addOption($optionObj);
		}
		return $this->optionProvider;
	}
} 
