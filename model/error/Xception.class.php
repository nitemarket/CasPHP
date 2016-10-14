<?php
class Xception extends Exception{

	public function __construct($message, $code = 0, Exception $previous = null){
		parent::__construct($message, (int)$code, $previous);
		$this->code = $code;
	}
}
?>