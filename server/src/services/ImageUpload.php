<?php

namespace App\Services;

use App\Exceptions\UploadException;
use App\Models\Image;
use App\Repositories\ImageRepository;

class ImageUpload
{
    public function __construct(
        private ImageRepository $repository,
        private string $uploadDir
    ) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    }
    
    public function upload(string $type, \Psr\Http\Message\UploadedFileInterface $file): string
    {
        try {
            $filename = uniqid() . '_' . $file->getClientFilename();
            $path = $this->uploadDir . '/' . $filename;
            
            $file->moveTo($path);
            
            $image = new Image();
            $image->type = $type;
            $image->filename = $filename;
            $image->path = $path;
            
            $this->repository->create($image);
            
            return $filename;
        } catch (\Exception $e) {
            throw new UploadException('Failed to upload image', $e);
        }
    }
    
    public function delete(string $filename): bool
    {
        $image = $this->repository->findByFilename($filename);
        if (!$image) {
            return false;
        }
        
        unlink($image->path);
        return $this->repository->delete($image->id);
    }
}
