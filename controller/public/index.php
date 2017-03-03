<?php

$app->get('/', function () use ($app) {
    $app->render('/public/index.html');
});

$app->get('/:name', function ($name) use ($app) {
    $app->render('/public/hi.html', array(
        'name' => $name,
    ));
    
});