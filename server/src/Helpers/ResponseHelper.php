<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Response Helper for Slim 4
 * A utility class to standardize JSON responses across the application
 */
class ResponseHelper {
    /**
     * Return a JSON response with proper headers
     *
     * @param Response $response The PSR-7 response object
     * @param mixed $data The data to encode as JSON
     * @param int $status The HTTP status code
     * @param array $headers Additional headers to add
     * @return Response
     */
    public static function withJson(Response $response, $data, int $status = 200, array $headers = []): Response {
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/json');
        
        // Add any additional headers
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        // Write the JSON-encoded data to the response body
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        
        return $response;
    }
    
    /**
     * Return a success response with standard format
     *
     * @param Response $response The PSR-7 response object
     * @param mixed $data The data to include in the response
     * @param int $status The HTTP status code
     * @param array $headers Additional headers to add
     * @return Response
     */
    public static function success(Response $response, $data = null, int $status = 200, array $headers = []): Response {
        $payload = [
            'status' => 'success',
            'data' => $data
        ];
        
        return self::withJson($response, $payload, $status, $headers);
    }
    
    /**
     * Return an error response with standard format
     *
     * @param Response $response The PSR-7 response object
     * @param string $message The error message
     * @param int $status The HTTP status code
     * @param array $headers Additional headers to add
     * @return Response
     */
    public static function error(Response $response, string $message, int $status = 400, array $headers = []): Response {
        $payload = [
            'status' => 'error',
            'message' => $message
        ];
        
        return self::withJson($response, $payload, $status, $headers);
    }
}
