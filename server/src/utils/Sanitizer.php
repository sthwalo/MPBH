<?php

namespace App\Utils;

/**
 * Sanitizer class for cleaning user input
 * Provides methods to sanitize and validate various types of input
 */
class Sanitizer {
    /**
     * Clean general input text
     * 
     * @param string $data Input data to clean
     * @return string Sanitized data
     */
    public static function cleanInput($data) {
        if (is_string($data)) {
            // Remove whitespace from beginning and end
            $data = trim($data);
            // Remove HTML tags
            $data = strip_tags($data);
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return $data;
        }
        
        return $data;
    }
    
    /**
     * Sanitize an array of inputs recursively
     * 
     * @param array $data Array of input data
     * @return array Sanitized array
     */
    public static function cleanArray(array $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::cleanArray($value);
            } else {
                $data[$key] = self::cleanInput($value);
            }
        }
        
        return $data;
    }
    
    /**
     * Sanitize email address
     * 
     * @param string $email Email address to sanitize
     * @return string|false Sanitized email or false if invalid
     */
    public static function cleanEmail($email) {
        $email = self::cleanInput($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return false;
    }
    
    /**
     * Sanitize numeric input
     * 
     * @param mixed $number Number to sanitize
     * @return int|float|false Sanitized number or false if invalid
     */
    public static function cleanNumber($number) {
        $number = filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (filter_var($number, FILTER_VALIDATE_FLOAT)) {
            return $number;
        }
        
        return false;
    }
    
    /**
     * Sanitize integer input
     * 
     * @param mixed $int Integer to sanitize
     * @return int|false Sanitized integer or false if invalid
     */
    public static function cleanInt($int) {
        $int = filter_var($int, FILTER_SANITIZE_NUMBER_INT);
        if (filter_var($int, FILTER_VALIDATE_INT)) {
            return (int)$int;
        }
        
        return false;
    }
    
    /**
     * Sanitize a URL
     * 
     * @param string $url URL to sanitize
     * @return string|false Sanitized URL or false if invalid
     */
    public static function cleanUrl($url) {
        $url = self::cleanInput($url);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return false;
    }
    
    /**
     * Sanitize date string to ensure it's a valid date format
     * 
     * @param string $date Date string to sanitize
     * @param string $format Expected date format
     * @return string|false Sanitized date or false if invalid
     */
    public static function cleanDate($date, $format = 'Y-m-d') {
        $date = self::cleanInput($date);
        $d = \DateTime::createFromFormat($format, $date);
        
        if ($d && $d->format($format) === $date) {
            return $date;
        }
        
        return false;
    }
    
    /**
     * Sanitize a phone number
     * 
     * @param string $phone Phone number to sanitize
     * @return string Sanitized phone number
     */
    public static function cleanPhone($phone) {
        // Remove all characters except digits, +, (, and )
        return preg_replace('/[^\d+()]/', '', self::cleanInput($phone));
    }
    
    /**
     * Sanitize HTML content (when HTML is allowed)
     * 
     * @param string $html HTML content to sanitize
     * @return string Sanitized HTML
     */
    public static function cleanHtml($html) {
        // Define allowed tags
        $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img>';
        
        // Clean the HTML
        $html = strip_tags($html, $allowedTags);
        
        // Remove any potential XSS attacks in attributes
        $html = preg_replace('/\bon\w+\s*=\s*["\']{1}.*?["\']{1}/i', '', $html);
        
        return $html;
    }
}
