<?php

use DI\ContainerBuilder;
use App\Config\Config;
use App\Services\AuthService;
use App\Services\BusinessService;
use App\Services\PaymentService;
use App\Services\SearchService;
use App\Services\AnalyticsService;
use App\Services\EmailService;
use App\Services\ImageService;

return function (ContainerBuilder $containerBuilder) {
    // Load configuration
    $containerBuilder->addDefinitions([
        Config::class => function () {
            return require __DIR__ . '/config.php';
        },
        
        // Services
        AuthService::class => DI\autowire(),
        BusinessService::class => DI\autowire(),
        PaymentService::class => DI\autowire(),
        SearchService::class => DI\autowire(),
        AnalyticsService::class => DI\autowire(),
        EmailService::class => DI\autowire(),
        ImageService::class => DI\autowire(),
    ]);
};
