<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

$app = AppFactory::create();

$view = new PhpRenderer(__DIR__ . '/../templates');

$app->get('/', function ($request, $response, $args) use ($view) {
    return $view->render($response, "home.php");
});

$app->run();
