<?php

namespace App\Middleware;

use App\Exceptions\AuthenticationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Authentication middleware
     *
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     * @throws AuthenticationException
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        
        // Check if the Authorization header exists and has the Bearer prefix
        if (empty($header) || !preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            throw new AuthenticationException('Authentication token required');
        }
        
        $jwt = $matches[1];
        
        try {
            // Decode the JWT using the secret key
            $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            // Add the decoded token data to the request attributes
            $request = $request->withAttribute('user', $decoded);
            
            // Call the next middleware
            return $handler->handle($request);
            
        } catch (ExpiredException $e) {
            throw new AuthenticationException('Token has expired');
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid authentication token');
        }
    }
}
