<?php

use App\Config\Database;
use App\Controllers\BusinessController;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;

return [
    // Database connection - shared instance
    PDO::class => function() {
        try {
            $database = new Database();
            $connection = $database->getConnection();
            
            if (!$connection) {
                throw new \Exception('Failed to establish database connection');
            }
            
            return $connection;
        } catch (\Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw $e;
        }
    },
    
    // Controller registrations with explicit dependencies
    BusinessController::class => function(ContainerInterface $container) {
        return new BusinessController(
            $container->get(PDO::class),
            $container->get(Logger::class)
        );
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
