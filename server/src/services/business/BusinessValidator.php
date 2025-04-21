<?php

namespace App\Services\Business;

use App\DTOs\BusinessDataTransfer;
use App\Exceptions\ValidationException;

class BusinessValidator
{
    public function validate(BusinessDataTransfer $dto): void
    {
        $errors = [];

        // Required fields
        if (empty($dto->name)) {
            $errors['name'] = 'Business name is required';
        }
        if (empty($dto->category)) {
            $errors['category'] = 'Category is required';
        }
        if (empty($dto->district)) {
            $errors['district'] = 'District is required';
        }

        // Email validation
        if (!empty($dto->email) && !filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Phone validation
        if (!empty($dto->phone) && !preg_match('/^[0-9\-\+\s]+$/i', $dto->phone)) {
            $errors['phone'] = 'Invalid phone number format';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    public function validateUpdate(BusinessDataTransfer $dto): void
    {
        $errors = [];

        // Validate optional fields
        if (!empty($dto->email) && !filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($dto->phone) && !preg_match('/^[0-9\-\+\s]+$/i', $dto->phone)) {
            $errors['phone'] = 'Invalid phone number format';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
