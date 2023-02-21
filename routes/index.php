<?php

use Pragma\Router\Router;
use Pragma\Historic\HistoricController;

$app = Router::getInstance();

$app->group('historic:', function () use ($app) {
    $app->cli('clean', function () {
        HistoricController::clean();
    });
});
