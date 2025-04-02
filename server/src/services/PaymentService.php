<?php

namespace App\Services;

use PDO;
use App\Models\Payment;
use App\Models\Business;

class PaymentService {
    private $db;
    private $payment;
    private $business;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->payment = new Payment($db);
        $this->business = new Business($db);
    }
    
    /**
     * Initiate a payment for a business subscription upgrade
     * 
     * @param int $businessId
     * @param string $packageType
     * @param string $paymentType
     * @return array Payment details including payment URL
     */
    public function initiatePayment(int $businessId, string $packageType, string $paymentType): array {
        // Validate package type
        if (!in_array($packageType, ['Basic', 'Silver', 'Gold'])) {
            throw new \InvalidArgumentException('Invalid package type');
        }
        
        // Get business details
        $business = $this->business->getBusinessById($businessId);
        if (!$business) {
            throw new \Exception('Business not found');
        }
        
        // Generate payment reference
        $reference = 'MPBH_' . time() . '_' . $businessId;
        
        // Determine payment amount based on package
        $amount = $this->getPackageAmount($packageType);
        
        // Create payment record
        $paymentData = [
            'business_id' => $businessId,
            'reference' => $reference,
            'amount' => $amount,
            'payment_type' => $paymentType,
            'package_type' => $packageType,
            'status' => 'pending'
        ];
        
        $paymentId = $this->payment->createPayment($paymentData);
        
        // Generate PayFast payment URL
        $paymentUrl = $this->generatePayFastUrl([
            'merchant_id' => $_ENV['PAYFAST_MERCHANT_ID'],
            'merchant_key' => $_ENV['PAYFAST_MERCHANT_KEY'],
            'return_url' => $_ENV['FRONTEND_URL'] . '/dashboard/payment-success',
            'cancel_url' => $_ENV['FRONTEND_URL'] . '/dashboard/payment-cancel',
            'notify_url' => $_ENV['API_URL'] . '/api/payments/webhook',
            'name_first' => $business['name'],
            'email_address' => $business['email'],
            'm_payment_id' => $reference,
            'amount' => $amount,
            'item_name' => $packageType . ' Package Subscription'
        ]);
        
        return [
            'payment_id' => $paymentId,
            'reference' => $reference,
            'amount' => $amount,
            'payment_url' => $paymentUrl
        ];
    }
    
    /**
     * Process payment webhook from PayFast
     * 
     * @param array $webhookData
     * @return bool Success status
     */
    public function processPaymentWebhook(array $webhookData): bool {
        // Validate webhook data
        if (!isset($webhookData['payment_reference']) || !isset($webhookData['payment_status'])) {
            return false;
        }
        
        // Find the payment by reference
        $payment = $this->payment->getPaymentByReference($webhookData['payment_reference']);
        if (!$payment) {
            return false;
        }
        
        // Update payment status
        $status = 'pending';
        if ($webhookData['payment_status'] === 'COMPLETE') {
            $status = 'completed';
        } elseif ($webhookData['payment_status'] === 'FAILED') {
            $status = 'failed';
        }
        
        $updateData = [
            'status' => $status,
            'transaction_id' => $webhookData['transaction_id'] ?? null,
            'processor_response' => json_encode($webhookData)
        ];
        
        $this->payment->updatePayment($payment['id'], $updateData);
        
        // If payment is successful and it's a package upgrade, update business package
        if ($status === 'completed' && $payment['payment_type'] === 'upgrade') {
            $businessData = [
                'package_type' => $payment['package_type'],
                'subscription_id' => $webhookData['subscription_id'] ?? null
            ];
            
            // Set adverts remaining based on package type
            if ($payment['package_type'] === 'Silver') {
                $businessData['adverts_remaining'] = 1;
            } elseif ($payment['package_type'] === 'Gold') {
                $businessData['adverts_remaining'] = 3;
            }
            
            $this->business->updateBusiness($payment['business_id'], $businessData);
        }
        
        return true;
    }
    
    /**
     * Get payment history for a business
     * 
     * @param int $businessId
     * @return array Payment history and statistics
     */
    public function getPaymentHistory(int $businessId): array {
        $payments = $this->payment->getPaymentsByBusinessId($businessId);
        $business = $this->business->getBusinessById($businessId);
        
        // Calculate payment statistics
        $totalSpent = 0;
        $successfulPayments = 0;
        $failedPayments = 0;
        $lastPaymentDate = null;
        
        foreach ($payments as $payment) {
            if ($payment['status'] === 'completed') {
                $totalSpent += $payment['amount'];
                $successfulPayments++;
                
                if (!$lastPaymentDate || strtotime($payment['created_at']) > strtotime($lastPaymentDate)) {
                    $lastPaymentDate = $payment['created_at'];
                }
            } elseif ($payment['status'] === 'failed') {
                $failedPayments++;
            }
        }
        
        return [
            'payments' => $payments,
            'statistics' => [
                'total_spent' => $totalSpent,
                'successful_payments' => $successfulPayments,
                'failed_payments' => $failedPayments,
                'last_payment_date' => $lastPaymentDate
            ],
            'current_package' => $business['package_type'],
            'subscription_id' => $business['subscription_id']
        ];
    }
    
    /**
     * Get payment packages information
     * 
     * @return array Available packages
     */
    public function getPackages(): array {
        return [
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
                    'products' => 0
                ]
            ]
        ];
    }
    
    /**
     * Get package amount based on package type
     * 
     * @param string $packageType
     * @return float Package price
     */
    private function getPackageAmount(string $packageType): float {
        switch ($packageType) {
            case 'Basic':
                return 0;
            case 'Silver':
                return 500;
            case 'Gold':
                return 1000;
            default:
                return 0;
        }
    }
    
    /**
     * Generate PayFast payment URL with parameters
     * 
     * @param array $params
     * @return string PayFast URL
     */
    private function generatePayFastUrl(array $params): string {
        // For sandbox/testing environment
        $baseUrl = 'https://sandbox.payfast.co.za/eng/process';
        
        // For production environment
        // $baseUrl = 'https://www.payfast.co.za/eng/process';
        
        // Generate signature
        $pfOutput = '';
        foreach ($params as $key => $val) {
            $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
        }
        
        // Remove last ampersand
        $getString = substr($pfOutput, 0, -1);
        
        // Generate signature
        $signature = md5($getString);
        $params['signature'] = $signature;
        
        // Construct URL with query string
        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }
}
