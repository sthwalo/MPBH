# API Reference

## Authentication Endpoints

### Register User and Business
- **URL**: `/debug-api.php?action=register` or `/auth/register`
- **Method**: POST
- **Description**: Registers a new user and associated business
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "securePassword",
    "businessName": "Business Name",
    "description": "Business description",
    "category": "Business Category",
    "district": "District Name",
    "address": "Physical Address",
    "phone": "+27XXXXXXXXX",
    "website": "https://example.com"
  }
  ```
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Registration successful",
    "data": {
      "user_id": 1,
      "business_id": 1,
      "token": "JWT_TOKEN"
    }
  }
  ```

### User Login
- **URL**: `/debug-api.php?action=login` or `/auth/login`
- **Method**: POST
- **Description**: Authenticates a user and returns user data, business data, and JWT token
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "securePassword"
  }
  ```
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Login successful",
    "data": {
      "user": {
        "user_id": 1,
        "name": "User Name",
        "email": "user@example.com",
        "phone_number": "+27XXXXXXXXX"
      },
      "business": {
        "business_id": 1,
        "name": "Business Name",
        "description": "Business description",
        "category": "Business Category",
        "district": "District Name",
        "address": "Physical Address",
        "phone": "+27XXXXXXXXX",
        "email": "user@example.com",
        "website": "https://example.com",
        "package_type": "Basic"
      },
      "token": "JWT_TOKEN"
    }
  }
  ```

## Business Endpoints

### Get Business Details
- **URL**: `/debug-api.php?endpoint=business/details` or `/business/details`
- **Method**: GET
- **Authorization**: Bearer Token
- **Description**: Retrieves details of the authenticated user's business
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "data": {
      "business_id": 1,
      "name": "Business Name",
      "description": "Business description",
      "category": "Business Category",
      "district": "District Name",
      "address": "Physical Address",
      "phone": "+27XXXXXXXXX",
      "email": "user@example.com",
      "website": "https://example.com",
      "logo": null,
      "cover_image": null,
      "package_type": "Basic",
      "created_at": "2025-04-05 15:16:59.977969",
      "updated_at": "2025-04-05 15:16:59.977969",
      "verification_status": "pending",
      "social_media": {},
      "business_hours": {}
    }
  }
  ```

### Get All Businesses
- **URL**: `/debug-api.php?endpoint=businesses` or `/businesses`
- **Method**: GET
- **Description**: Retrieves a list of all businesses with optional filtering and pagination
- **Query Parameters**:
  - `category`: Filter by business category
  - `district`: Filter by district
  - `search`: Search term for business name or description
  - `page`: Page number for pagination (default: 1)
  - `limit`: Number of items per page (default: 20)
  - `sort_by`: Field to sort by (default: name)
  - `order`: Sort order 'asc' or 'desc' (default: asc)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "data": {
      "businesses": [
        {
          "business_id": 1,
          "name": "Business Name",
          "description": "Business description",
          "category": "Business Category",
          "district": "District Name",
          "address": "Physical Address",
          "phone": "+27XXXXXXXXX",
          "email": "user@example.com",
          "website": "https://example.com",
          "package_type": "Basic",
          "verified": false,
          "active": true,
          "rating": "0.00",
          "views": 0
        }
      ],
      "pagination": {
        "total": 10,
        "page": 1,
        "limit": 20,
        "pages": 1
      }
    }
  }
  ```

## User Endpoints

### Get User Profile
- **URL**: `/debug-api.php?endpoint=user/profile` or `/user/profile`
- **Method**: GET
- **Authorization**: Bearer Token
- **Description**: Retrieves the authenticated user's profile information
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "data": {
      "user_id": 1,
      "name": "User Name",
      "email": "user@example.com",
      "phone_number": "+27XXXXXXXXX",
      "area_of_operation": null,
      "language_preference": null
    }
  }
  ```
# First, let's start the server in the background
cd /Users/sthwalonyoni/MPBH/server
php -S localhost:8000 -t public &