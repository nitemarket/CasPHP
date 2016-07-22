<?php

$app->get('/', function () use ($app) {
    echo '<pre>';
    echo json_encode($app->getHeaders('Authorization'), JSON_PRETTY_PRINT);
});