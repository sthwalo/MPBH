<?php

namespace App\Services;

class ImageService {
    private $uploadDir;
    
    public function __construct() {
        // Set base upload directory relative to the public folder
        $this->uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/';
        
        // Ensure the upload directory exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload an image file
     * 
     * @param array $file The uploaded file ($_FILES array element)
     * @param string $subDir Subdirectory to store the image (e.g., 'businesses', 'products')
     * @param string $prefix Optional filename prefix
     * @return string|false The path to the uploaded file or false on failure
     */
    public function uploadImage(array $file, string $subDir, string $prefix = ''): string|false {
        // Validate file
        if (!$this->validateImage($file)) {
            return false;
        }
        
        // Create target directory if it doesn't exist
        $targetDir = $this->uploadDir . $subDir . '/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $prefix . uniqid() . '_' . time() . '.' . $extension;
        $targetFile = $targetDir . $fileName;
        
        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Optimize the image
            $this->optimizeImage($targetFile, $extension);
            
            // Return the relative path for database storage
            return 'uploads/' . $subDir . '/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Delete an image file
     * 
     * @param string $filePath Relative path to the file
     * @return bool True if deleted successfully, false otherwise
     */
    public function deleteImage(string $filePath): bool {
        $fullPath = dirname(dirname(dirname(__DIR__))) . '/public/' . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Validate an uploaded image file
     * 
     * @param array $file The uploaded file ($_FILES array element)
     * @return bool True if valid, false otherwise
     */
    private function validateImage(array $file): bool {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size (limit to 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }
        
        // Verify file is an image
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return false;
        }
        
        // Allow only specific image types
        $allowedTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF
        ];
        
        if (!in_array($imageInfo[2], $allowedTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Optimize image for web
     * 
     * @param string $filePath Path to the image file
     * @param string $extension File extension
     * @return bool Success status
     */
    private function optimizeImage(string $filePath, string $extension): bool {
        // Maximum width and height
        $maxWidth = 1200;
        $maxHeight = 1200;
        
        // Get current dimensions
        list($width, $height) = getimagesize($filePath);
        
        // Only resize if the image is larger than max dimensions
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Calculate new dimensions while maintaining aspect ratio
        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * $maxWidth / $width);
        } else {
            $newHeight = $maxHeight;
            $newWidth = intval($width * $maxHeight / $height);
        }
        
        // Create new image resource
        $sourceImage = null;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load source image based on file type
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($filePath);
                // Preserve transparency
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }
        
        // Resize the image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save the optimized image
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($newImage, $filePath, 85); // 85% quality
                break;
            case 'png':
                imagepng($newImage, $filePath, 8); // Compression level 8
                break;
            case 'gif':
                imagegif($newImage, $filePath);
                break;
            default:
                return false;
        }
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return true;
    }
}
