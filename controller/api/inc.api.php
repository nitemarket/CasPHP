<?php

try{
    //config file that loads before main file
}
catch(exception $e){
    switch($e->getCode()){
        //bad request
        default:
            $error = 400;
            $errormsg = $app->getMessageForCode(400);
            break;
    }
    
    $app->terminate($error, $errormsg);
    exit;
}

?>