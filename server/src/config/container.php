<?php

use Dotenv\Dotenv;
use App\Services\PaymentService;
use App\Services\SearchService;
use App\Services\AnalyticsService;
use App\Services\EmailService;
use App\Services\ImageService;
use App\Services\ErrorService;
use App\Repositories\Interfaces\BusinessRepositoryInterface;
use App\Services\Interfaces\BusinessServiceInterface;
use App\Services\Business\BusinessValidator;
use App\Services\Business\BusinessService;
use App\Repositories\Business\BusinessRepository;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\TagProcessor;
use PDO;
use PDOException;
use Psr\Container\ContainerInterface;
use App\Services\Business\BusinessRegistrationService;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

return [
    Config::class => function () {
        return require __DIR__ . '/config.php';
    },
    
    PDO::class => function () {
        $config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'dbname' => $_ENV['DB_NAME'] ?? 'mpbusis6k1d8_sthwalo',
            'user' => $_ENV['DB_USER'] ?? 'mpbusis6k1d8_sthwalo',
            'password' => $_ENV['DB_PASSWORD'] ?? 'Password123'
        ];

        error_log("Database config: " . json_encode($config));

        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};user={$config['user']};password={$config['password']}";
        error_log("DSN: " . $dsn);

        try {
            return new PDO(
                $dsn,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    },

    // Business Services
    App\Repositories\Interfaces\BusinessRepositoryInterface::class => function (ContainerInterface $container) {
        return new App\Repositories\Business\BusinessRepository($container->get(PDO::class));
    },
    
    App\Services\Interfaces\BusinessServiceInterface::class => function (ContainerInterface $container) {
        return new App\Services\Business\BusinessService(
            $container->get(App\Repositories\Interfaces\BusinessRepositoryInterface::class),
            new App\Services\Business\BusinessValidator(),
            $container->get(App\Services\AnalyticsService::class)
        );
    },
    // Business Registration
    App\Services\Business\BusinessRegistrationService::class => function (ContainerInterface $container) {
        return new App\Services\Business\BusinessRegistrationService(
            $container->get(App\Services\Business\BusinessService::class),
            $container->get(App\Services\ImageService::class),
            $container->get(App\Services\AnalyticsService::class)
        );
    },
    // Image Service
    App\Services\ImageService::class => function () {
        return new App\Services\ImageService();
    },
    
    // Error Service
    App\Services\ErrorService::class => function (ContainerInterface $container) {
        return new App\Services\ErrorService(
            $container->get(PDO::class),
            $container->get(Logger::class)
        );
    },
    
    // Logger
    Logger::class => function () {
        $logger = new Logger('app');
        
        // Add processors
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new WebProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new MemoryPeakUsageProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(new TagProcessor(['environment' => 'development']));
        
        // Add handlers
        $logger->pushHandler(new StreamHandler(
            __DIR__ . '/../../logs/app.log',
            Logger::DEBUG
        ));
        
        return $logger;
    },
    
    // Services
    AuthService::class => DI\autowire(),
    PaymentService::class => DI\autowire(),
    SearchService::class => DI\autowire(),
    AnalyticsService::class => function (ContainerInterface $container) {
        return new AnalyticsService(
            $container->get(PDO::class),
            $container->get(ImageService::class),
            null
        );
    },
    EmailService::class => DI\autowire(),
    ImageService::class => function (PDO $db) {
        return new ImageService($db);
    },
    Business::class => function (PDO $db, ImageService $imageService) {
        return new Business($db, $imageService);
    },
];