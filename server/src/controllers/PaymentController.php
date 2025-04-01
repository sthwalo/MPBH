<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Business;
use App\Models\Payment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use PDO;

class PaymentController
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
     * Get packages and pricing information
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getPackages(Request $request, Response $response): Response
    {
        // Define packages with features and pricing
        $packages = [
            [
                'id' => 'basic',
                'name' => 'Basic',
                'price' => 0,
                'billing_cycle' => 'once',
                'features' => [
                    'Business listing',
                    'Contact information',
                    'Basic analytics',
                    'Community reviews'
                ],
                'limits' => [
                    'adverts' => 0,
                    'products' => 0
                ]
            ],
            [
                'id' => 'silver',
                'name' => 'Silver',
                'price' => 500,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Basic',
                    'Product catalog (up to 20 products)',
                    'Featured in category searches',
                    'Advanced analytics',
                    '1 advert slot per month'
                ],
                'limits' => [
                    'adverts' => 1,
                    'products' => 20
                ]
            ],
            [
                'id' => 'gold',
                'name' => 'Gold',
                'price' => 1000,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Silver',
                    'Unlimited products',
                    'Priority listing in search results',
                    'Featured on homepage',
                    'Social media promotion',
                    '3 advert slots per month'
                ],
                'limits' => [
                    'adverts' => 3,
                    'products' => 0 // unlimited
                ]
            ]
        ];
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $packages
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get payment history for authenticated business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getPaymentHistory(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get pagination parameters
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? (int) $params['limit'] : 10;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;
        
        // Get payments
        $payment = new Payment($this->db);
        $payments = $payment->getBusinessPayments($businessId, $limit, $offset);
        
        // Get payment statistics
        $stats = $payment->getPaymentStatistics($businessId);
        
        // Get business subscription details
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => [
                'payments' => $payments,
                'statistics' => $stats,
                'current_package' => $business->package_type,
                'subscription_id' => $business->subscription_id
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Initiate payment for package upgrade
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function initiatePayment(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['package_type', 'payment_type'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate package type
        if (isset($data['package_type']) && !in_array($data['package_type'], ['Silver', 'Gold'])) {
            $errors['package_type'] = 'Invalid package type';
        }
        
        // Validate payment type
        if (isset($data['payment_type']) && !in_array($data['payment_type'], ['upgrade', 'advert'])) {
            $errors['payment_type'] = 'Invalid payment type';
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Determine amount based on package type and payment type
        $amount = 0;
        if ($data['payment_type'] === 'upgrade') {
            $amount = $data['package_type'] === 'Gold' ? 1000 : 500;
        } else { // advert
            $amount = 100; // Fixed price for additional advert slot
        }
        
        // Create payment record
        $payment = new Payment($this->db);
        $payment->business_id = $businessId;
        $payment->reference = Payment::generateReference();
        $payment->amount = $amount;
        $payment->payment_type = $data['payment_type'];
        $payment->package_type = $data['package_type'];
        $payment->status = 'pending';
        
        if (!$payment->create()) {
            throw new \Exception('Failed to create payment record');
        }
        
        // Generate PayFast payment URL (in a real app, this would integrate with PayFast API)
        $payfastUrl = $this->generatePayfastUrl($payment, $business);
        
        $this->logger->info('Payment initiated', [
            'payment_id' => $payment->id,
            'business_id' => $businessId,
            'amount' => $amount,
            'type' => $data['payment_type'],
            'package' => $data['package_type']
        ]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Payment initiated successfully',
            'data' => [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => $payment->amount,
                'payment_url' => $payfastUrl
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Process payment webhook from payment processor
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function processWebhook(Request $request, Response $response): Response
    {
        // Get webhook data
        $data = $request->getParsedBody();
        
        // Validate webhook data
        if (empty($data['payment_reference'])) {
            throw new BadRequestException('Invalid webhook data');
        }
        
        // Get payment by reference
        $payment = new Payment($this->db);
        if (!$payment->findByReference($data['payment_reference'])) {
            throw new NotFoundException('Payment not found');
        }
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($payment->business_id)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if this is a successful payment notification
        $isSuccessful = isset($data['payment_status']) && $data['payment_status'] === 'COMPLETE';
        
        try {
            $this->db->beginTransaction();
            
            // Update payment status
            $status = $isSuccessful ? 'completed' : 'failed';
            $transactionId = $data['transaction_id'] ?? null;
            $payment->updatePaymentStatus($status, $transactionId, $data);
            
            // If payment successful, update business based on payment type
            if ($isSuccessful) {
                if ($payment->payment_type === 'upgrade') {
                    // Determine adverts slots based on package
                    $advertsRemaining = $payment->package_type === 'Gold' ? 3 : 1;
                    
                    // Update business package
                    $business->updatePackage(
                        $payment->package_type,
                        $data['subscription_id'] ?? null,
                        $advertsRemaining
                    );
                } else { // advert
                    // Increment adverts remaining
                    $business->updateAdvertsRemaining($business->adverts_remaining + 1);
                }
            }
            
            $this->db->commit();
            
            $this->logger->info('Payment webhook processed', [
                'payment_id' => $payment->id,
                'status' => $status,
                'transaction_id' => $transactionId
            ]);
            
            // Prepare response
            $responseData = [
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Payment webhook processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate PayFast payment URL
     * 
     * @param Payment $payment Payment object
     * @param Business $business Business object
     * @return string PayFast URL
     */
    private function generatePayfastUrl(Payment $payment, Business $business): string
    {
        // PayFast merchant details from .env
        $merchantId = $_ENV['PAYFAST_MERCHANT_ID'] ?? '10000100';
        $merchantKey = $_ENV['PAYFAST_MERCHANT_KEY'] ?? 'abcdefgh';
        $returnUrl = $_ENV['FRONTEND_URL'] . '/dashboard/payment-return?reference=' . $payment->reference;
        $cancelUrl = $_ENV['FRONTEND_URL'] . '/dashboard/payment-cancel?reference=' . $payment->reference;
        $notifyUrl = $_ENV['API_URL'] . '/api/payments/webhook';
        
        // Payment data
        $data = [
            'merchant_id' => $merchantId,
            'merchant_key' => $merchantKey,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
            'name_first' => 'Business',
            'name_last' => 'Owner',
            'email_address' => $business->email,
            'm_payment_id' => $payment->reference,
            'amount' => $payment->amount,
            'item_name' => $payment->payment_type === 'upgrade' 
                ? "Upgrade to {$payment->package_type} Package" 
                : "Additional Advert Slot",
            'item_description' => $payment->payment_type === 'upgrade'
                ? "Membership upgrade for business ID: {$business->id}"
                : "Additional advert slot for business ID: {$business->id}"
        ];
        
        // In a real implementation, we would:
        // 1. Sort the data alphabetically by key
        // 2. URL encode the values
        // 3. Generate a signature using hash_hmac
        // 4. Append the signature to the data
        
        // For now, let's just simulate this
        $baseUrl = 'https://sandbox.payfast.co.za/eng/process';
        $queryString = http_build_query($data);
        
        return $baseUrl . '?' . $queryString;
    }
}
