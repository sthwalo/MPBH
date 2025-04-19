<?php

namespace App\Services;

use App\Exceptions\BadRequestException;

class PaymentGatewayService {
    private $merchantId;
    private $merchantKey;
    private $passphrase;
    private $testMode;
    private $returnUrl;
    private $cancelUrl;
    private $notifyUrl;
    
    /**
     * Constructor initializes PayFast integration settings
     */
    public function __construct() {
        // Load PayFast configuration from environment
        $this->merchantId = $_ENV['PAYFAST_MERCHANT_ID'] ?? '';
        $this->merchantKey = $_ENV['PAYFAST_MERCHANT_KEY'] ?? '';
        $this->passphrase = $_ENV['PAYFAST_PASSPHRASE'] ?? null;
        $this->testMode = $_ENV['PAYFAST_TEST_MODE'] ?? 'true';
        
        // Set URLs
        $apiUrl = $_ENV['API_URL'] ?? 'http://localhost:8080';
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
        
        $this->returnUrl = $frontendUrl . '/payment/success';
        $this->cancelUrl = $frontendUrl . '/payment/cancel';
        $this->notifyUrl = $apiUrl . '/api/payments/notify';
    }
    
    /**
     * Generate PayFast payment URL for redirecting user
     * 
     * @param int $businessId The business ID
     * @param string $packageType The package type (Basic, Silver, Gold)
     * @param string $paymentType The payment type (monthly, annual)
     * @param string $name The business name
     * @param string $email The user's email
     * @return array The payment URL and data
     */
    public function getPaymentUrl($businessId, $packageType, $paymentType, $name, $email) {
        // Set amount based on package type and payment frequency
        $amount = $this->getPackagePrice($packageType, $paymentType);
        
        if (!$amount) {
            throw new BadRequestException('Invalid package type or payment type');
        }
        
        // Generate unique payment ID
        $paymentId = 'MPBH' . time() . rand(100, 999);
        
        // Create payment data array for PayFast
        $data = [
            // Merchant details
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'return_url' => $this->returnUrl,
            'cancel_url' => $this->cancelUrl,
            'notify_url' => $this->notifyUrl,
            
            // Transaction details
            'name_first' => explode(' ', $name)[0],
            'name_last' => count(explode(' ', $name)) > 1 ? explode(' ', $name)[1] : '',
            'email_address' => $email,
            'm_payment_id' => $paymentId,
            'amount' => number_format($amount, 2, '.', ''),
            'item_name' => "MPBH $packageType Package - $paymentType",
            'item_description' => "Mpumalanga Business Hub $packageType Package ($paymentType subscription)",
            
            // Custom variables
            'custom_str1' => $businessId,
            'custom_str2' => $packageType,
            'custom_str3' => $paymentType,
        ];
        
        // Add test mode if enabled
        if ($this->testMode === 'true') {
            $data['custom_str4'] = 'true';
        }
        
        // Generate signature
        $signature = $this->generateSignature($data);
        $data['signature'] = $signature;
        
        // Build the query string
        $queryString = http_build_query($data);
        
        // Determine the PayFast URL based on test mode
        $payfastUrl = ($this->testMode === 'true') 
            ? 'https://sandbox.payfast.co.za/eng/process' 
            : 'https://www.payfast.co.za/eng/process';
        
        return [
            'url' => $payfastUrl . '?' . $queryString,
            'payment_id' => $paymentId,
            'amount' => $amount,
            'data' => $data
        ];
    }
    
    /**
     * Verify that the ITN (Instant Transaction Notification) is valid
     * 
     * @param array $data The POST data from PayFast
     * @param string $serverVars Server variables including HTTP headers
     * @return bool Whether the ITN is valid
     */
    public function validateItn($data, $serverVars) {
        $pfHost = ($this->testMode === 'true') 
            ? 'sandbox.payfast.co.za' 
            : 'www.payfast.co.za';
        
        // Step 1: Verify source IP
        $validHosts = [
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        ];
        
        $validIps = [];
        foreach ($validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);
            if ($ips !== false) {
                $validIps = array_merge($validIps, $ips);
            }
        }
        
        $validIps = array_unique($validIps);
        
        if (!in_array($_SERVER['REMOTE_ADDR'], $validIps)) {
            return false;
        }
        
        // Step 2: Verify data received
        if (empty($data)) {
            return false;
        }
        
        // Step 3: Verify signature
        $calculatedSignature = $this->generateSignature($data);
        if ($calculatedSignature !== $data['signature']) {
            return false;
        }
        
        // Step 4: Verify data with PayFast
        $verifyUrl = 'https://' . $pfHost . '/eng/query/validate';
        $response = $this->curlPost($verifyUrl, $data);
        
        if (strcmp($response, 'VALID') !== 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the price for a package and payment type
     * 
     * @param string $packageType The package type (Basic, Silver, Gold)
     * @param string $paymentType The payment type (monthly, annual)
     * @return float|null The price or null if invalid
     */
    private function getPackagePrice($packageType, $paymentType) {
        $prices = [
            'Basic' => [
                'monthly' => 199.99,
                'annual' => 1999.99,
            ],
            'Silver' => [
                'monthly' => 349.99,
                'annual' => 3499.99,
            ],
            'Gold' => [
                'monthly' => 599.99,
                'annual' => 5999.99,
            ],
        ];
        
        return $prices[$packageType][$paymentType] ?? null;
    }
    
    /**
     * Generate signature for PayFast API
     * 
     * @param array $data The data to sign
     * @return string The generated signature
     */
    private function generateSignature($data) {
        // Remove signature field if it exists
        unset($data['signature']);
        
        // Sort data by key
        ksort($data);
        
        // Create URL encoded parameter string
        $paramString = http_build_query($data);
        
        // Calculate signature based on whether passphrase is set
        if (!empty($this->passphrase)) {
            $signature = md5($paramString . '&passphrase=' . urlencode($this->passphrase));
        } else {
            $signature = md5($paramString);
        }
        
        return $signature;
    }
    
    /**
     * Perform a cURL POST request
     * 
     * @param string $url The URL to send the request to
     * @param array $data The data to send
     * @return string The response
     */
    private function curlPost($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
