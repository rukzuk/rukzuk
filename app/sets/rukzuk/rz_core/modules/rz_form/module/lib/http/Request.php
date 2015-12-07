<?php
require_once(dirname(__FILE__) . '/IRequest.php');
/**
 */
class Request implements IRequest{

	/**
	 * @var null
	 */
	private $postValues = null;

	public function __construct(){
		if($this->isPostRequest()) {
			$this->setPostValues($_POST);
		}
	}

	public function isPostRequest(){
		return (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')?true:false;
	}

	public function getPostValues(){
		$data = array();
		foreach($this->postValues as $key => $value){
			$data[preg_replace( '/field/', '', $key )] = $this->sanatizerValues( $value );
		}
		return $data;
	}

	private function sanatizerValues( $postValues ){
		if( is_array( $postValues ) ) {
			$data = array();
			foreach( $postValues as $value ) {
				if( is_array( $value ) ) {
					$data[] = $this->sanatizerValues( $value );
				} else {
					$data[] = filter_var( $value, FILTER_SANITIZE_STRING );
				}
			}
		}else{
			$data = filter_var( $postValues, FILTER_SANITIZE_STRING );
		}
		return $data;
	}

	/**
	 * @param array $postValues
	 */
	public function setPostValues( array $postValues ) {
		$this->postValues = $postValues;
	}

} 