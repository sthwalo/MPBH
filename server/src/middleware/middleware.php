<?php

use Slim\App;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Middleware\SecurityHeadersMiddleware;

return function (App $app) {
    // Global middleware
    $app->add(new RateLimitMiddleware(100)); // 100 requests/minute
    $app->add(new SecurityHeadersMiddleware());
    $app->add(new JsonBodyParserMiddleware());
    $app->add(new CsrfMiddleware());

    // Error handling middleware
    $app->addErrorMiddleware(true, true, true);
};
