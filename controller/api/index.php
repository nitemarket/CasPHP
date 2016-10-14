<?php

// GET /api or /api/index
$app->get('/', function () use ($app) {
    echo '<pre>';
    echo json_encode($app->getHeaders(), JSON_PRETTY_PRINT);
});

// POST to /api/postData
$app->post('/postData', function () use ($app) {
    $post = $app->postRequest();
    $get = $app->getRequest();
    
    $feedback = array_merge($post, $get);
    
    $app->contentType('application/json');
    echo json_encode($feedback);
});

// Better write at the bottom to avoid collision
// GET to /api/:param1/:param2 or /api/index/:param1/:param2
$app->get('/:param1/:param2', function ($param1, $param2) use ($app) {
    $feedback = array(
        'param1' => $param1,
        'param2' => $param2,
    );
    
    $app->contentType('application/json');
    echo json_encode($feedback);
});

$app->post('/img', function () use ($app) {
    $data = $app->postRequest();
    $feedback = array();
    
    try{
        $s3 = new AwsS3Storage(System::$vars['aws']['s3']['bucket_name']);
        $objectURL = $s3->save_base64_media($data['image']);
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