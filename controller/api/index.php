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