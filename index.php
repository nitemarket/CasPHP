<?php
require_once("inc.include.php");

$app = new Core();

$app->notFound(function () use ($app) {
//    echo '404 Not Found';
    $app->render('/404.html');
});

$resourceUri = $app->env['PATH_INFO'];
$directories = explode('/', ltrim($resourceUri, '/'));

//setup default folder
$defaultFilepath = '/public';

//look for correct controller file
$filepath = '';
foreach($directories as $index => $directory){
    if($directory){
        $filepath .= '/' . $directory;
        if(!is_dir(ROUTER_ROOT . $filepath)){
            if($index === 0){
                $filepath = $defaultFilepath;
            }
            else{
                $filepath = dirname($filepath);
            }
            if(file_exists(ROUTER_ROOT . $filepath . '/' . $directory . '.php')){
                $filename = $directory;
            }
            break;
        }
    }
}

//set prefix to the pattern in order to match routes
$prePattern = str_replace($defaultFilepath, '', $filepath) . ($filename ? '/' . $filename : '');
$app->settings('prePattern', $prePattern);

if(!$filepath){
    $filepath = $defaultFilepath;
}

if(!$filename){
    //default filename
	$filename = 'index';
}

//inc
$inc_file = ROUTER_ROOT . $filepath . '/inc.' . trim($filepath, '/') . '.php';
if(file_exists($inc_file)){
    require_once($inc_file);
}

//controller
$php_file = ROUTER_ROOT . $filepath . '/' . $filename . '.php';
if(file_exists($php_file)){
    require_once($php_file);
}

$app->run();

?>