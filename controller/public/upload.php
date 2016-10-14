<?php

$app->get('/', function () use ($app) {
    $app->render('/public/upload.html');
    
});

$app->post('/', function () use ($app) {
    $feedback = array();
    
    try{
        $s3 = new AwsS3Storage(System::$vars['aws']['s3']['bucket_name']);
        $objectURL = $s3->save_standard_media($_FILES['image'], $_FILES['image']['name']);
        $feedback = Array(
            "status" => 'success',
            "url" => $objectURL,
        );
    }
    catch(Exception $e){
        $feedback = array(
            'error' => $e->getCode(),
            'errormsg' => $e->getMessage()
        );
    }
   
    $app->contentType('application/json');
    echo json_encode($feedback, JSON_PRETTY_PRINT);
});