<?php
/**
 * CasPHP - a PHP 5 framework
 *
 * @author      Cas Chan <casper_ccb@hotmail.com>
 * @version     1.0.0
 *
 * MIT LICENSE
 */

basename($_SERVER["PHP_SELF"]) == 'inc.include.php' && exit('Invalid Access!');

session_start();

require_once("config/inc.config.php");
require_once(MODEL_ROOT . '/aws/aws-autoloader.php');

$_error_report_code = $vars['debug'] ? E_ALL & ~E_NOTICE & ~E_DEPRECATED : 0;
error_reporting($_error_report_code);

spl_autoload_register(function ($class) {
	$classname = strtolower($class);
    if(strstr($classname, 'core') !== false){
		$path = MODEL_ROOT . '/core';
		include($path . '/' . $class . '.class.php');
	}
    elseif(strstr($classname, 'util') !== false){
		$path = MODEL_ROOT . '/util';
		include($path . '/' . $class . '.class.php');
	}
    elseif(strstr($classname, 'error') !== false || $class == 'Xception'){
		$path = MODEL_ROOT . '/error';
		include($path . '/' . $class . '.class.php');
	}
    elseif(strstr($classname, 'system') !== false){
		$path = MODEL_ROOT . '/system';
		include($path . '/' . $class . '.class.php');
	}
    elseif(strstr($classname, 'storage') !== false){
		$path = MODEL_ROOT . '/storage';
        $sub_ext = 'class';
		if(substr($classname, -5) == 'inter') $sub_ext = 'inter';
		include($path . '/' . $class . '.' . $sub_ext . '.php');
	}
});

?>