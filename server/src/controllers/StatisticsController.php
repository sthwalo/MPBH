<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Models\Business;
use App\Models\Statistic;
use App\Models\Payment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use PDO;

class StatisticsController
{
    private PDO $db;
    private Logger $logger;
    
    /**
     * Constructor with dependencies
     * 
     * @param PDO $db Database connection
     * @param Logger $logger Logger instance
     */
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * Get dashboard statistics for authenticated business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getDashboardStats(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get statistics
        $statistic = new Statistic($this->db);
        $statistics = $statistic->getDashboardStatistics($businessId);
        
        // Get payment summary
        $payment = new Payment($this->db);
        $paymentStats = $payment->getPaymentStatistics($businessId);
        
        // Get business info
        $businessData = [
            'id' => $business->id,
            'name' => $business->name,
            'package_type' => $business->package_type,
            'verification_status' => $business->verification_status,
            'adverts_remaining' => $business->adverts_remaining,
        ];
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => [
                'business' => $businessData,
                'statistics' => $statistics,
                'payments' => $paymentStats
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Log user interactions with the business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function logInteraction(Request $request, Response $response, array $args): Response
    {
        $businessId = (int) $args['id'];
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate interaction type
        if (empty($data['type']) || !in_array($data['type'], ['page_view', 'product_view', 'advert_click', 'inquiry'])) {
            throw new BadRequestException('Invalid interaction type');
        }
        
        // Get client info
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        
        // Log the interaction
        $statistic = new Statistic($this->db);
        $success = false;
        
        switch ($data['type']) {
            case 'page_view':
                $success = $statistic->logPageView($businessId, $ipAddress, $userAgent, $referrer);
                break;
                
            case 'product_view':
                if (empty($data['product_id'])) {
                    throw new BadRequestException('Product ID is required for product view');
                }
                $success = $statistic->logProductView($businessId, (int) $data['product_id'], $ipAddress);
                break;
                
            case 'advert_click':
                if (empty($data['advert_id'])) {
                    throw new BadRequestException('Advert ID is required for advert click');
                }
                $success = $statistic->logAdvertClick($businessId, (int) $data['advert_id'], $ipAddress);
                break;
                
            case 'inquiry':
                if (empty($data['inquiry_type'])) {
                    throw new BadRequestException('Inquiry type is required');
                }
                $success = $statistic->logInquiry($businessId, $data['inquiry_type'], $ipAddress);
                break;
        }
        
        if (!$success) {
            throw new \Exception('Failed to log interaction');
        }
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Interaction logged successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get business traffic statistics by location
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getTrafficByLocation(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // In a real implementation, we would analyze IP addresses to determine location
        // For now, let's return simulated data
        $locationData = [
            ['location' => 'Nelspruit', 'visits' => 150],
            ['location' => 'White River', 'visits' => 85],
            ['location' => 'Sabie', 'visits' => 63],
            ['location' => 'Barberton', 'visits' => 47],
            ['location' => 'Hazyview', 'visits' => 35],
            ['location' => 'Other Mpumalanga', 'visits' => 120],
            ['location' => 'Gauteng', 'visits' => 180],
            ['location' => 'Other Provinces', 'visits' => 95],
            ['location' => 'International', 'visits' => 25]
        ];
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $locationData
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get business traffic statistics by referral source
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getTrafficByReferral(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // In a real implementation, we would analyze referrer data
        // For now, let's return simulated data
        $referralData = [
            ['source' => 'Direct', 'visits' => 245],
            ['source' => 'Directory Search', 'visits' => 185],
            ['source' => 'Google', 'visits' => 165],
            ['source' => 'Facebook', 'visits' => 110],
            ['source' => 'Twitter', 'visits' => 35],
            ['source' => 'Other Social Media', 'visits' => 45],
            ['source' => 'Adverts', 'visits' => 55],
            ['source' => 'Other', 'visits' => 60]
        ];
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $referralData
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
