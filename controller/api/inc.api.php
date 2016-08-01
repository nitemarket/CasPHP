<?php

try{
    //config file that loads before main file
}
catch(exception $e){
    switch($e->getCode()){
        //bad request
        default:
            $app->setStatus('400');
            echo $app->getMessageForCode('400');
            break;
    }
    
    exit;
}

?>