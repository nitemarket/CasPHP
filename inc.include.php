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
});

?>