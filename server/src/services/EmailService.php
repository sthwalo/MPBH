<?php

namespace App\Services;

class EmailService {
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mpbusinesshub.co.za';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Mpumalanga Business Hub';
    }
    
    /**
     * Send welcome email to a new user
     * 
     * @param string $email Recipient email
     * @param string $name Recipient name
     * @return bool Success status
     */
    public function sendWelcomeEmail(string $email, string $name): bool {
        $subject = 'Welcome to Mpumalanga Business Hub';
        
        $message = $this->getEmailTemplate('welcome', [
            'name' => $name,
            'loginUrl' => $_ENV['FRONTEND_URL'] . '/login',
            'year' => date('Y')
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send password reset email
     * 
     * @param string $email Recipient email
     * @param string $resetToken Reset token
     * @param string $name Recipient name
     * @return bool Success status
     */
    public function sendPasswordResetEmail(string $email, string $resetToken, string $name): bool {
        $subject = 'Reset Your Password - Mpumalanga Business Hub';
        
        $resetUrl = $_ENV['FRONTEND_URL'] . '/reset-password?token=' . $resetToken . '&email=' . urlencode($email);
        
        $message = $this->getEmailTemplate('password-reset', [
            'name' => $name,
            'resetUrl' => $resetUrl,
            'year' => date('Y')
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send verification email for a newly registered business
     * 
     * @param string $email Recipient email
     * @param string $businessName Business name
     * @return bool Success status
     */
    public function sendBusinessVerificationEmail(string $email, string $businessName): bool {
        $subject = 'Business Registration Confirmation - Mpumalanga Business Hub';
        
        $message = $this->getEmailTemplate('business-verification', [
            'businessName' => $businessName,
            'dashboardUrl' => $_ENV['FRONTEND_URL'] . '/dashboard',
            'year' => date('Y')
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send payment confirmation email
     * 
     * @param string $email Recipient email
     * @param string $businessName Business name
     * @param string $packageType Package type
     * @param float $amount Payment amount
     * @param string $reference Payment reference
     * @return bool Success status
     */
    public function sendPaymentConfirmationEmail(string $email, string $businessName, string $packageType, float $amount, string $reference): bool {
        $subject = 'Payment Confirmation - Mpumalanga Business Hub';
        
        $message = $this->getEmailTemplate('payment-confirmation', [
            'businessName' => $businessName,
            'packageType' => $packageType,
            'amount' => number_format($amount, 2),
            'reference' => $reference,
            'date' => date('Y-m-d H:i:s'),
            'dashboardUrl' => $_ENV['FRONTEND_URL'] . '/dashboard/payments',
            'year' => date('Y')
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send new review notification
     * 
     * @param string $email Recipient email
     * @param string $businessName Business name
     * @param string $reviewerName Reviewer name
     * @param float $rating Review rating
     * @return bool Success status
     */
    public function sendNewReviewNotification(string $email, string $businessName, string $reviewerName, float $rating): bool {
        $subject = 'New Review Received - Mpumalanga Business Hub';
        
        $message = $this->getEmailTemplate('new-review', [
            'businessName' => $businessName,
            'reviewerName' => $reviewerName,
            'rating' => $rating,
            'dashboardUrl' => $_ENV['FRONTEND_URL'] . '/dashboard/reviews',
            'year' => date('Y')
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email content (HTML)
     * @return bool Success status
     */
    private function sendEmail(string $to, string $subject, string $message): bool {
        // Headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Get email template with placeholders replaced
     * 
     * @param string $template Template name
     * @param array $data Template variables
     * @return string Processed template
     */
    private function getEmailTemplate(string $template, array $data): string {
        // Path to email templates
        $templatePath = dirname(dirname(dirname(__DIR__))) . '/templates/emails/' . $template . '.html';
        
        // Default template if file doesn't exist
        if (!file_exists($templatePath)) {
            return $this->getDefaultTemplate($data);
        }
        
        // Get template content
        $content = file_get_contents($templatePath);
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Get a default template if the requested template file doesn't exist
     * 
     * @param array $data Template variables
     * @return string Default template
     */
    private function getDefaultTemplate(array $data): string {
        $content = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Mpumalanga Business Hub</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4CAF50;
                    padding: 20px;
                    color: white;
                    text-align: center;
                }
                .footer {
                    background-color: #f1f1f1;
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .content {
                    padding: 20px;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Mpumalanga Business Hub</h1>
                </div>
                <div class="content">
                    <p>Dear {{name}},</p>
                    <p>Thank you for using Mpumalanga Business Hub.</p>
                    <p>If you have any questions, please contact our support team.</p>
                    
                    <p>Best regards,<br>The Mpumalanga Business Hub Team</p>
                </div>
                <div class="footer">
                    <p>&copy; {{year}} Mpumalanga Business Hub. All rights reserved.</p>
                    <p>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }
}
