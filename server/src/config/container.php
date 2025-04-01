<?php

use App\Config\Database;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

return [
    // Database connection
    PDO::class => function() {
        $database = new Database();
        return $database->getConnection();
    },
    
    // Logger
    Logger::class => function() {
        $logLevel = isset($_ENV['LOG_LEVEL']) ? $_ENV['LOG_LEVEL'] : 'info';
        $loggerLevel = Logger::toMonologLevel(strtoupper($logLevel));
        
        $logger = new Logger('app');
        $processor = new UidProcessor();
        $logger->pushProcessor($processor);
        
        $handler = new StreamHandler(__DIR__ . '/../../logs/app.log', $loggerLevel);
        $logger->pushHandler($handler);
        
        return $logger;
    },
];
