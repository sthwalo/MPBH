<?php

namespace App\Services;

use Psr\Http\Message\UploadedFileInterface;
use App\Exceptions\UploadException;

class ImageUploadService
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_FILE_SIZE = 5242880; // 5MB

    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(UploadedFileInterface $file, string $type): string
    {
        $this->validateFile($file);
        
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s_%s.%s', $type, $basename, $extension);
        
        $directory = $this->uploadDir . '/' . $type;
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $file->moveTo($directory . '/' . $filename);
        
        return $filename;
    }

    private function validateFile(UploadedFileInterface $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new UploadException('File is too large. Maximum size is 5MB');
        }

        if (!in_array($file->getClientMediaType(), self::ALLOWED_TYPES)) {
            throw new UploadException('Invalid file type. Only JPEG, PNG, and GIF are allowed');
        }
    }
}
