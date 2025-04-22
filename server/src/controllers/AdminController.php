<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\Business\BusinessService;
use App\Services\EmailService;
use App\Utils\Sanitizer;

class AdminController {
    private $businessService;
    private $emailService;
    
    public function __construct($container) {
        $this->businessService = new BusinessService($container->get('db'));
        $this->emailService = new EmailService();
    }
    
    /**
     * Get all pending business listings for admin approval
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getPendingBusinesses(Request $request, Response $response): Response {
        try {
            // Get pending businesses from database
            $db = $request->getAttribute('db');
            $stmt = $db->prepare("SELECT * FROM businesses WHERE verification_status = 'pending' ORDER BY created_at ASC");
            $stmt->execute();
            $businesses = $stmt->fetchAll();
            
            return $response->withJson([
                'status' => 'success',
                'data' => $businesses
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Failed to get pending businesses',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Approve or reject a business listing
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     */
    public function updateBusinessStatus(Request $request, Response $response, array $args): Response {
        try {
            $businessId = Sanitizer::cleanInt($args['id']);
            $data = $request->getParsedBody();
            
            // Validate required fields
            if (!isset($data['status']) || !in_array($data['status'], ['verified', 'rejected'])) {
                return $response->withStatus(400)->withJson([
                    'status' => 'error',
                    'message' => 'Invalid status. Must be "verified" or "rejected"'
                ]);
            }
            
            // Get business details for email notification
            $db = $request->getAttribute('db');
            $stmt = $db->prepare("SELECT b.*, u.email FROM businesses b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
            $stmt->execute([$businessId]);
            $business = $stmt->fetch();
            
            if (!$business) {
                return $response->withStatus(404)->withJson([
                    'status' => 'error',
                    'message' => 'Business not found'
                ]);
            }
            
            // Update business verification status
            $success = $this->businessService->verifyBusiness($businessId, $data['status']);
            
            if (!$success) {
                return $response->withStatus(500)->withJson([
                    'status' => 'error',
                    'message' => 'Failed to update business status'
                ]);
            }
            
            // Send email notification to business owner
            if ($data['status'] === 'verified') {
                $this->emailService->sendBusinessVerificationEmail(
                    $business['email'],
                    $business['name']
                );
            }
            
            return $response->withJson([
                'status' => 'success',
                'message' => 'Business ' . ($data['status'] === 'verified' ? 'approved' : 'rejected') . ' successfully'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Failed to update business status',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get admin dashboard statistics
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getDashboardStats(Request $request, Response $response): Response {
        try {
            $db = $request->getAttribute('db');
            
            // Get counts for different business statuses
            $stmt = $db->prepare(
                "SELECT 
                    verification_status, 
                    COUNT(*) as count 
                FROM businesses 
                GROUP BY verification_status"
            );
            $stmt->execute();
            $statusCounts = $stmt->fetchAll();
            
            // Format as associative array
            $businessStatuses = [];
            foreach ($statusCounts as $status) {
                $businessStatuses[$status['verification_status']] = $status['count'];
            }
            
            // Get counts for different package types
            $stmt = $db->prepare(
                "SELECT 
                    package_type, 
                    COUNT(*) as count 
                FROM businesses 
                WHERE verification_status = 'verified' 
                GROUP BY package_type"
            );
            $stmt->execute();
            $packageCounts = $stmt->fetchAll();
            
            // Format as associative array
            $packageTypes = [];
            foreach ($packageCounts as $package) {
                $packageTypes[$package['package_type']] = $package['count'];
            }
            
            // Get recent payments
            $stmt = $db->prepare(
                "SELECT p.*, b.name as business_name 
                FROM payments p 
                JOIN businesses b ON p.business_id = b.id 
                ORDER BY p.created_at DESC 
                LIMIT 5"
            );
            $stmt->execute();
            $recentPayments = $stmt->fetchAll();
            
            // Get total revenue
            $stmt = $db->prepare(
                "SELECT 
                    SUM(amount) as total_revenue 
                FROM payments 
                WHERE status = 'completed'"
            );
            $stmt->execute();
            $revenue = $stmt->fetch();
            
            return $response->withJson([
                'status' => 'success',
                'data' => [
                    'businesses' => [
                        'total' => array_sum($businessStatuses),
                        'verified' => $businessStatuses['verified'] ?? 0,
                        'pending' => $businessStatuses['pending'] ?? 0,
                        'rejected' => $businessStatuses['rejected'] ?? 0
                    ],
                    'packages' => [
                        'basic' => $packageTypes['Basic'] ?? 0,
                        'silver' => $packageTypes['Silver'] ?? 0,
                        'gold' => $packageTypes['Gold'] ?? 0
                    ],
                    'payments' => [
                        'recent' => $recentPayments,
                        'total_revenue' => $revenue['total_revenue'] ?? 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Failed to get admin dashboard statistics',
                'error' => $e->getMessage()
            ]);
        }
    }
}
