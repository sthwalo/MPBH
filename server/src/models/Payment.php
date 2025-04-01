<?php

namespace App\Models;

use PDO;

class Payment
{
    private PDO $db;
    
    // Database table name
    private string $table = 'payments';
    
    // Payment properties
    public ?int $id = null;
    public int $business_id;
    public string $reference;
    public float $amount;
    public string $payment_type; // upgrade, advert, etc.
    public string $package_type = 'Basic'; // Basic, Silver, Gold (for upgrades)
    public string $status = 'pending'; // pending, completed, failed
    public ?string $transaction_id = null;
    public ?string $processor_response = null; // JSON
    public string $created_at;
    public ?string $updated_at = null;
    
    /**
     * Constructor with database dependency
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create new payment record
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (business_id, reference, amount, payment_type, package_type, status)
                 VALUES (:business_id, :reference, :amount, :payment_type, :package_type, :status)";
        
        $stmt = $this->db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->bindParam(':reference', $this->reference);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_type', $this->payment_type);
        $stmt->bindParam(':package_type', $this->package_type);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get payment by ID
     * 
     * @param int $id Payment ID
     * @return bool Success status
     */
    public function readOne(int $id): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->id = $row['id'];
        $this->business_id = $row['business_id'];
        $this->reference = $row['reference'];
        $this->amount = $row['amount'];
        $this->payment_type = $row['payment_type'];
        $this->package_type = $row['package_type'];
        $this->status = $row['status'];
        $this->transaction_id = $row['transaction_id'];
        $this->processor_response = $row['processor_response'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get payment by reference
     * 
     * @param string $reference Payment reference
     * @return bool Success status
     */
    public function findByReference(string $reference): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE reference = :reference LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reference', $reference);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties same as in readOne
        $this->id = $row['id'];
        $this->business_id = $row['business_id'];
        $this->reference = $row['reference'];
        $this->amount = $row['amount'];
        $this->payment_type = $row['payment_type'];
        $this->package_type = $row['package_type'];
        $this->status = $row['status'];
        $this->transaction_id = $row['transaction_id'];
        $this->processor_response = $row['processor_response'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get payment history for a business
     * 
     * @param int $businessId Business ID
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Payments array
     */
    public function getBusinessPayments(int $businessId, int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT * FROM " . $this->table . "
                 WHERE business_id = :business_id
                 ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $payments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse JSON data
            $row['processor_response'] = json_decode($row['processor_response'] ?? null);
            $payments[] = $row;
        }
        
        return $payments;
    }
    
    /**
     * Update payment status and transaction details
     * 
     * @param string $status New status
     * @param string|null $transactionId Transaction ID
     * @param array|null $processorResponse Processor response data
     * @return bool Success status
     */
    public function updatePaymentStatus(string $status, ?string $transactionId = null, ?array $processorResponse = null): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET status = :status, 
                     transaction_id = :transaction_id,
                     processor_response = :processor_response
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Prepare processor response JSON
        $processorResponseJson = $processorResponse ? json_encode($processorResponse) : null;
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':transaction_id', $transactionId);
        $stmt->bindParam(':processor_response', $processorResponseJson);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->status = $status;
            $this->transaction_id = $transactionId;
            $this->processor_response = $processorResponseJson;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get payment statistics summary
     * 
     * @param int $businessId Business ID
     * @return array Payment statistics
     */
    public function getPaymentStatistics(int $businessId): array
    {
        $query = "SELECT 
                     SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_spent,
                     COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
                     COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                     MAX(CASE WHEN status = 'completed' THEN created_at END) as last_payment_date
                 FROM " . $this->table . "
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_spent' => (float) $stats['total_spent'] ?? 0,
            'successful_payments' => (int) $stats['successful_payments'] ?? 0,
            'failed_payments' => (int) $stats['failed_payments'] ?? 0,
            'last_payment_date' => $stats['last_payment_date'] ?? null
        ];
    }
    
    /**
     * Get payment data as array
     * 
     * @return array Payment data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'payment_type' => $this->payment_type,
            'package_type' => $this->package_type,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'processor_response' => json_decode($this->processor_response ?? '{}'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Generate unique payment reference
     * 
     * @param string $prefix Reference prefix
     * @return string Payment reference
     */
    public static function generateReference(string $prefix = 'MPBH'): string
    {
        $timestamp = time();
        $random = rand(1000, 9999);
        return $prefix . '_' . $timestamp . '_' . $random;
    }
}
