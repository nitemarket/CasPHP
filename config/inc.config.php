<?php
basename($_SERVER["PHP_SELF"]) == 'inc.config.php' && exit('Invalid Access!');

define('ENV', 'local');
define('CONFIG_DIR_NAME', 'config');
define('M_ROOT', substr(dirname(__FILE__), 0, strlen(dirname(__FILE__)) -(strlen(CONFIG_DIR_NAME) + 1)));
define('WORK_DIR', '');// working directory for the site, precede with '/'
define('CURRENT_SSL', (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')? true : false);
define('URL_PROTOCOL', ENV == 'live' && CURRENT_SSL? 'https' : 'http');
define('M_URL', URL_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].WORK_DIR);
define('DOMAIN', 'example.com');
define('HC_URL', URL_PROTOCOL.'://www.'.DOMAIN.WORK_DIR);
define('CONFIG_PATH', M_ROOT.'/'.CONFIG_DIR_NAME);
define('ROUTER_ROOT', M_ROOT.'/controller');
define('MODEL_ROOT', M_ROOT.'/model');
define('TEMPLATE_DIR_NAME', '/templates');
define('TEMPLATE_TYPE', '/default');
define('TEMPLATE_ASSET', '/_include');
define('TEMPLATE_PATH', M_ROOT.TEMPLATE_DIR_NAME.TEMPLATE_TYPE);
define('TEMPLATE_URL', M_URL.TEMPLATE_DIR_NAME.TEMPLATE_TYPE);
define('FILE_ROOT', M_ROOT.'/public/files'); //cpanel
define('FILE_URL', M_URL.'/public/files'); //cpanel

@date_default_timezone_set('GMT');

//#####GENERAL
$vars['timezone_offset'] = 8;
$vars['language'] = "en";
$vars["debug"] = true;

#####DATABASE
$vars["dbi"]["host"]="localhost";
$vars["dbi"]["port"]="";
$vars["dbi"]["name"]="database";
$vars["dbi"]["user"]="root";
$vars["dbi"]["pass"]="";
$vars["dbi"]["prefix"]="ex_";