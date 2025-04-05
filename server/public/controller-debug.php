<?php
/**
 * Controller Debug Tool
 * Tests the BusinessController to identify PostgreSQL compatibility issues
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Create PDO connection to PostgreSQL
try {
    // Create connection string
    $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";
    
    // Create new PDO instance
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create logger
    $logger = new Monolog\Logger('app');
    $logger->pushHandler(new Monolog\Handler\StreamHandler(dirname(__DIR__) . '/logs/debug.log', Monolog\Logger::DEBUG));
    
    // Get the request
    $serverParams = $_SERVER;
    $queryParams = $_GET;
    
    // Create a mock request
    $request = new \GuzzleHttp\Psr7\ServerRequest(
        'GET',
        'http://localhost:8080/api/businesses',
        [],
        null,
        '1.1',
        $serverParams
    );
    
    // Add query parameters
    $request = $request->withQueryParams($queryParams);
    
    // Create a response
    $response = new \GuzzleHttp\Psr7\Response();
    
    // Create controller
    $controllerClassName = '\\App\\Controllers\\BusinessController';
    
    if (class_exists($controllerClassName)) {
        $controller = new $controllerClassName($conn, $logger);
        
        // Check if the method exists
        if (method_exists($controller, 'getAllBusinesses')) {
            try {
                // Call the method
                $newResponse = $controller->getAllBusinesses($request, $response);
                
                // Get the response body
                $body = $newResponse->getBody();
                $body->rewind();
                $contents = $body->getContents();
                
                // Output response
                http_response_code($newResponse->getStatusCode());
                foreach ($newResponse->getHeaders() as $name => $values) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                }
                echo $contents;
            } catch (Throwable $e) {
                // Output error
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Controller method error: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ], JSON_PRETTY_PRINT);
            }
        } else {
            // Method doesn't exist
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Method getAllBusinesses does not exist in BusinessController'
            ], JSON_PRETTY_PRINT);
        }
    } else {
        // Class doesn't exist
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Controller class not found: ' . $controllerClassName,
            'available_classes' => get_declared_classes()
        ], JSON_PRETTY_PRINT);
    }
} catch (Throwable $e) {
    // Output error
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
