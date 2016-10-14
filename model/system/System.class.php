<?php
class System {
	protected static $_system_instance;
	
	public static $vars;
	public static $data;
	public static $language_codes = array("EN");
	public static $charset = 'utf-8';
	
	public function __construct(){
		$method_code = '0001';
		
		global $vars;
		static::$vars = $vars;
	}
	
	protected static function _initialize(){
		$method_code = '0002';
		if(!static::$_system_instance){
			static::$_system_instance = new self();
		}
    }
	
	public static function is_env($type){
		static::_initialize();
        $method_code = '0003';
		
		return $type === ENV ? true : false;
	}
}
?>