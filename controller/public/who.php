<?php

$app->get('/', function () use ($app) {
    $app->render('/public/who.html');
    
});