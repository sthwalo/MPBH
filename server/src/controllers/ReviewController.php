<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Business;
use App\Models\Review;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use PDO;

class ReviewController
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
     * Create a new review
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function createReview(Request $request, Response $response, array $args): Response
    {
        $businessId = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $userId = $userData->user_id;
        
        // Verify business exists
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if user already reviewed this business
        $review = new Review($this->db);
        if ($review->hasUserReviewed($userId, $businessId)) {
            throw new BadRequestException('You have already reviewed this business');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['rating', 'comment', 'reviewer_name'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate rating
        if (isset($data['rating'])) {
            $rating = (float) $data['rating'];
            if ($rating < 1 || $rating > 5) {
                $errors['rating'] = 'Rating must be between 1 and 5';
            }
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Create review
        $review->business_id = $businessId;
        $review->user_id = $userId;
        $review->reviewer_name = $data['reviewer_name'];
        $review->rating = (float) $data['rating'];
        $review->comment = $data['comment'];
        $review->status = 'pending'; // All reviews start as pending
        
        if (!$review->create()) {
            throw new \Exception('Failed to create review');
        }
        
        $this->logger->info('Review created', [
            'review_id' => $review->id,
            'business_id' => $businessId,
            'user_id' => $userId
        ]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Review submitted successfully. It will be published after approval.',
            'data' => $review->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
    
    /**
     * Get reviews for current user
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getMyReviews(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $userId = $userData->user_id;
        
        // Get pagination parameters
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? (int) $params['limit'] : 10;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;
        
        // Get reviews
        $review = new Review($this->db);
        $reviews = $review->getUserReviews($userId, $limit, $offset);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $reviews
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Update a review
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function updateReview(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $userId = $userData->user_id;
        
        // Get review
        $review = new Review($this->db);
        if (!$review->readOne($id)) {
            throw new NotFoundException('Review not found');
        }
        
        // Verify ownership
        if ($review->user_id !== $userId) {
            throw new AuthorizationException('You do not have permission to update this review');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate data
        $errors = [];
        
        // Validate rating if provided
        if (isset($data['rating'])) {
            $rating = (float) $data['rating'];
            if ($rating < 1 || $rating > 5) {
                $errors['rating'] = 'Rating must be between 1 and 5';
            }
            $review->rating = $rating;
        }
        
        // Validate comment if provided
        if (isset($data['comment'])) {
            if (empty($data['comment'])) {
                $errors['comment'] = 'Comment is required';
            } else {
                $review->comment = $data['comment'];
            }
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Reset status to pending since it's been modified
        $review->status = 'pending';
        
        // Update review
        if (!$review->update()) {
            throw new \Exception('Failed to update review');
        }
        
        $this->logger->info('Review updated', ['review_id' => $review->id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Review updated successfully. It will be published after approval.',
            'data' => $review->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Delete a review
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function deleteReview(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $userId = $userData->user_id;
        
        // Get review
        $review = new Review($this->db);
        if (!$review->readOne($id)) {
            throw new NotFoundException('Review not found');
        }
        
        // Verify ownership
        if ($review->user_id !== $userId) {
            throw new AuthorizationException('You do not have permission to delete this review');
        }
        
        // Delete review
        if (!$review->delete()) {
            throw new \Exception('Failed to delete review');
        }
        
        $this->logger->info('Review deleted', ['review_id' => $id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Approve or reject a review (admin only)
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function moderateReview(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        
        // Check if user is an admin (in a real app, we'd have a proper role system)
        if (!isset($userData->is_admin) || !$userData->is_admin) {
            throw new AuthorizationException('You do not have permission to moderate reviews');
        }
        
        // Get review
        $review = new Review($this->db);
        if (!$review->readOne($id)) {
            throw new NotFoundException('Review not found');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate action
        if (empty($data['action']) || !in_array($data['action'], ['approve', 'reject'])) {
            throw new ValidationException('Validation failed', [
                'action' => 'Action must be either "approve" or "reject"'
            ]);
        }
        
        // Set new status based on action
        $status = $data['action'] === 'approve' ? 'approved' : 'rejected';
        
        // Update status
        if (!$review->updateStatus($status)) {
            throw new \Exception('Failed to update review status');
        }
        
        $this->logger->info('Review moderated', [
            'review_id' => $review->id,
            'status' => $status,
            'moderator_id' => $userData->user_id
        ]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Review ' . $status . ' successfully',
            'data' => $review->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
