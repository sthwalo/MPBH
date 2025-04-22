<?php

namespace App\Services;

use PDO;
use Exception;
use App\Exceptions\UploadException;
use App\Models\Image;
use App\Repositories\ImageRepository;
use Psr\Http\Message\UploadedFileInterface;

class ImageService {
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_FILE_SIZE = 5242880; // 5MB
    private const MAX_DIMENSIONS = [
        'width' => 1200,
        'height' => 1200
    ];
    
    private ?ImageRepository $repository = null;
    
    public function __construct(
        private PDO $db,
        ?ImageRepository $repository = null,
        private ?string $uploadDir = null
    ) {
        // Set repository if provided
        if ($repository !== null) {
            $this->repository = $repository;
        }
        
        // Set default upload directory if not provided
        if ($this->uploadDir === null) {
            $this->uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/';
        }
        
        // Ensure the upload directory exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload an image file from $_FILES array
     * 
     * @param array $file The uploaded file ($_FILES array element)
     * @param string $subDir Subdirectory to store the image (e.g., 'businesses', 'products')
     * @param string $prefix Optional filename prefix
     * @return string|false The path to the uploaded file or false on failure
     */
    public function uploadImage(array $file, string $subDir, string $prefix = ''): string|false {
        try {
            // Validate file
            if (!$this->validateImage($file)) {
                throw new Exception('Invalid image file');
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
            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Optimize the image
            $this->optimizeImage($targetFile, $extension);
            
            // Save to repository if available
            $this->saveToRepository($subDir, $fileName, $targetFile);
            
            // Return the relative path for database storage
            return 'uploads/' . $subDir . '/' . $fileName;
        } catch (Exception $e) {
            error_log('Image upload failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload an image file from PSR-7 UploadedFileInterface
     * 
     * @param UploadedFileInterface $file The uploaded file
     * @param string $type Image type/category
     * @return string Filename of the uploaded image
     * @throws UploadException If upload fails
     */
    public function uploadFromPsr7(UploadedFileInterface $file, string $type): string {
        try {
            // Validate file
            $this->validatePsr7File($file);
            
            // Generate unique filename
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8));
            $filename = sprintf('%s_%s.%s', $type, $basename, $extension);
            
            // Create type-specific directory
            $directory = $this->uploadDir . '/' . $type;
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move uploaded file
            $path = $directory . '/' . $filename;
            $file->moveTo($path);
            
            // Optimize the image
            $this->optimizeImage($path, $extension);
            
            // Save to repository if available
            $this->saveToRepository($type, $filename, $path);
            
            return $filename;
        } catch (Exception $e) {
            throw new UploadException('Failed to upload image: ' . $e->getMessage(), $e);
        }
    }
    
    /**
     * Delete an image file
     * 
     * @param string $filePath Relative path to the file or filename
     * @param string|null $type Type/category directory if filename is provided
     * @return bool True if deleted successfully, false otherwise
     */
    public function deleteImage(string $filePath, ?string $type = null): bool {
        try {
            // Handle full path vs. filename-only scenarios
            if ($type !== null) {
                // We've been given a filename and type
                $fullPath = $this->uploadDir . '/' . $type . '/' . $filePath;
                
                // Remove from repository if available
                if ($this->repository !== null) {
                    $image = $this->repository->findByFilename($filePath);
                    if ($image) {
                        $this->repository->delete($image->id);
                    }
                }
            } else {
                // We've been given a relative path
                $fullPath = dirname(dirname(dirname(__DIR__))) . '/public/' . $filePath;
                
                // Extract filename for repository deletion
                $filename = basename($fullPath);
                if ($this->repository !== null) {
                    $image = $this->repository->findByFilename($filename);
                    if ($image) {
                        $this->repository->delete($image->id);
                    }
                }
            }
            
            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Image deletion failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate an uploaded image file from $_FILES
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
        if ($file['size'] > self::MAX_FILE_SIZE) {
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
     * Validate a PSR-7 uploaded file
     * 
     * @param UploadedFileInterface $file The file to validate
     * @throws UploadException If file is invalid
     */
    private function validatePsr7File(UploadedFileInterface $file): void {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new UploadException('File is too large. Maximum size is 5MB');
        }

        if (!in_array($file->getClientMediaType(), self::ALLOWED_TYPES)) {
            throw new UploadException('Invalid file type. Only JPEG, PNG, and GIF are allowed');
        }
    }
    
    /**
     * Optimize image for web
     * 
     * @param string $filePath Path to the image file
     * @param string $extension File extension
     * @return bool Success status
     */
    private function optimizeImage(string $filePath, string $extension): bool {
        try {
            // Maximum width and height
            $maxWidth = self::MAX_DIMENSIONS['width'];
            $maxHeight = self::MAX_DIMENSIONS['height'];
            
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
            switch (strtolower($extension)) {
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
            switch (strtolower($extension)) {
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
        } catch (Exception $e) {
            error_log('Image optimization failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save image information to repository if available
     * 
     * @param string $type Image type/category
     * @param string $filename Image filename
     * @param string $path Full path to the image
     * @return bool Success status
     */
    private function saveToRepository(string $type, string $filename, string $path): bool {
        if ($this->repository === null) {
            return false;
        }
        
        try {
            $image = new Image();
            $image->type = $type;
            $image->filename = $filename;
            $image->path = $path;
            
            $this->repository->create($image);
            return true;
        } catch (Exception $e) {
            error_log('Failed to save image to repository: ' . $e->getMessage());
            return false;
        }
    }
}