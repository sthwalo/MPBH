# API Reference

## Overview

The Mpumalanga Business Hub API provides a set of RESTful endpoints for interacting with the backend services. This document outlines the available endpoints, request/response formats, and authentication requirements.

## Base URL

All API endpoints are relative to the base URL:

```
https://api.mpbusinesshub.co.za/
```

For local development:

```
http://localhost/mpbh/server/
```

## Authentication

Most API endpoints require authentication using JSON Web Tokens (JWT). To authenticate requests, include the token in the Authorization header:

```
Authorization: Bearer <token>
```

### Obtaining a Token

Tokens are obtained by authenticating through the login endpoint (`/api/auth/login`). The token should be stored securely on the client and included in subsequent requests.

### Rate Limiting

API endpoints implement rate limiting to prevent abuse. The limits vary by endpoint type:

- Auth endpoints: 10 requests per minute
- Public endpoints: 30 requests per minute
- Business endpoints: 100 requests per minute
- Default authenticated endpoints: 60 requests per minute

When rate limits are exceeded, a 429 Too Many Requests response will be returned.

## Security

### CSRF Protection

All POST, PUT, DELETE, and PATCH requests require a valid CSRF token to be included either as a request header or in the request body. The token is generated automatically for GET requests and stored in the session.

**Header method:**
```
X-CSRF-Token: abc123token
```

**Form field method:**
```
csrf_token: abc123token
```

Failure to include a valid CSRF token will result in a 403 Forbidden response.

### Rate Limiting

API endpoints are subject to rate limiting to prevent abuse. The default limit is 60 requests per minute per IP address. Rate limit information is included in the response headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1617278093
```

Exceeding the rate limit will result in a 429 Too Many Requests response.

## API Endpoints

### Authentication

#### Register

```
POST /api/auth/register
```

Register a new user account.

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securePassword123",
  "password_confirmation": "securePassword123",
  "phone": "0791234567"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
  }
}
```

#### Login

```
POST /api/auth/login
```

Authenticate a user and get an access token.

**Request Body:**

```json
{
  "email": "john@example.com",
  "password": "securePassword123"
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
  }
}
```

#### Logout

```
POST /api/auth/logout
```

Invalidate the current user's token.

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "message": "Successfully logged out"
}
```

#### Forgot Password

```
POST /api/auth/forgot-password
```

Request a password reset link.

**Request Body:**

```json
{
  "email": "john@example.com"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Password reset link sent to your email"
}
```

#### Reset Password

```
POST /api/auth/reset-password
```

Reset password using token from email.

**Request Body:**

```json
{
  "token": "reset-token-from-email",
  "password": "newSecurePassword123",
  "password_confirmation": "newSecurePassword123"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Password has been reset successfully"
}
```

### Business Management

#### Get All Businesses

```
GET /api/businesses
```

Get a list of all verified businesses with optional filtering.

**Query Parameters:**

- `category` (optional): Filter by business category
- `district` (optional): Filter by district
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 10)

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Kruger Safari Adventures",
      "category": "Tourism",
      "district": "Ehlanzeni",
      "description": "Guided safari tours in Kruger National Park",
      "logo": "https://api.mpbusinesshub.co.za/uploads/businesses/1/logo.jpg",
      "package_type": "Gold"
    },
    // More businesses...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 45,
    "total_pages": 5
  }
}
```

#### Get Business by ID

```
GET /api/businesses/{id}
```

Get detailed information about a specific business.

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Kruger Safari Adventures",
    "category": "Tourism",
    "district": "Ehlanzeni",
    "description": "Guided safari tours in Kruger National Park",
    "address": "123 Park Street, Hazyview",
    "phone": "0131234567",
    "email": "info@krugersafari.co.za",
    "website": "https://www.krugersafari.co.za",
    "social_media": {
      "facebook": "krugersafari",
      "instagram": "krugersafari"
    },
    "operating_hours": "Mon-Sun: 06:00-18:00",
    "logo": "https://api.mpbusinesshub.co.za/uploads/businesses/1/logo.jpg",
    "package_type": "Gold",
    "products": [
      {
        "id": 1,
        "name": "Full Day Safari",
        "description": "Full day guided safari in an open vehicle",
        "price": 1200,
        "image": "https://api.mpbusinesshub.co.za/uploads/products/1/image.jpg"
      },
      // More products...
    ],
    "reviews": [
      {
        "id": 1,
        "user_name": "Sarah Johnson",
        "rating": 5,
        "comment": "Amazing experience! Our guide was very knowledgeable.",
        "created_at": "2023-03-15T14:30:45Z"
      },
      // More reviews...
    ]
  }
}
```

#### Create Business

```
POST /api/businesses
```

Create a new business listing (requires authentication).

**Headers:**

```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Form Data:**

- `name`: Business name
- `category`: Business category
- `district`: District location
- `description`: Business description
- `address`: Physical address
- `phone`: Contact phone number
- `email`: Contact email
- `website` (optional): Business website
- `social_media` (optional): JSON string of social media links
- `operating_hours` (optional): Operating hours description
- `package_type` (optional): Membership tier (default: Basic)
- `logo` (optional): Business logo image file

**Response:**

```json
{
  "status": "success",
  "message": "Business created successfully and pending approval",
  "data": {
    "id": 123,
    "name": "My New Business",
    "verification_status": "pending",
    // Other business fields...
  }
}
```

#### Update My Business

```
PUT /api/businesses/my-business
```

Update the authenticated user's business details.

**Headers:**

```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Form Data:**

- All fields from the Create Business endpoint are optional here

**Response:**

```json
{
  "status": "success",
  "message": "Business updated successfully",
  "data": {
    "id": 123,
    "name": "Updated Business Name",
    // Updated business fields...
  }
}
```

### Product Management

#### Get Business Products

```
GET /api/businesses/{id}/products
```

Get all products for a specific business.

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Full Day Safari",
      "description": "Full day guided safari in an open vehicle",
      "price": 1200,
      "image": "https://api.mpbusinesshub.co.za/uploads/products/1/image.jpg"
    },
    // More products...
  ]
}
```

#### Create Product

```
POST /api/products
```

Add a new product to the authenticated user's business (requires Silver or Gold tier).

**Headers:**

```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Form Data:**

- `name`: Product name
- `description`: Product description
- `price`: Product price
- `image` (optional): Product image file

**Response:**

```json
{
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 123,
    "name": "New Product",
    "description": "Product description",
    "price": 299.99,
    "image": "https://api.mpbusinesshub.co.za/uploads/products/123/image.jpg"
  }
}
```

### Payment Processing

#### Get Packages

```
GET /api/payments/packages
```

Get available membership packages and pricing.

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": "basic",
      "name": "Basic",
      "description": "Basic business listing",
      "features": ["Business listing", "Contact information"],
      "price": {
        "monthly": 0,
        "annual": 0
      }
    },
    {
      "id": "silver",
      "name": "Silver",
      "description": "Enhanced business profile with products",
      "features": ["Business listing", "Contact information", "Product listings (up to 15)", "1 monthly advert", "Website link", "Analytics dashboard"],
      "price": {
        "monthly": 349.99,
        "annual": 3499.99
      }
    },
    {
      "id": "gold",
      "name": "Gold",
      "description": "Premium business profile with maximum visibility",
      "features": ["Business listing", "Contact information", "Product listings (up to 30)", "3 monthly adverts", "Featured placement", "Website link", "Analytics dashboard"],
      "price": {
        "monthly": 599.99,
        "annual": 5999.99
      }
    }
  ]
}
```

#### Initiate Payment

```
POST /api/payments/initiate
```

Initiate a new payment for a package subscription.

**Headers:**

```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "business_id": 123,
  "package_type": "Gold",
  "payment_type": "monthly" // or "annual"
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "payment_id": "MPBH12345678",
    "amount": 599.99,
    "payment_url": "https://sandbox.payfast.co.za/eng/process?merchant_id=123456&merchant_key=abcdef..."  
  }
}
```

#### Get Payment History

```
GET /api/payments/history
```

Get payment history for the authenticated user's business.

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "payment_id": "MPBH12345678",
      "amount": 599.99,
      "package_type": "Gold",
      "payment_type": "monthly",
      "status": "completed",
      "created_at": "2023-03-15T14:30:45Z"
    },
    // More payment records...
  ]
}
```

### Search

#### Search Businesses

```
GET /api/search
```

Search for businesses using various criteria.

**Query Parameters:**

- `q`: Search query term
- `category` (optional): Filter by category
- `district` (optional): Filter by district
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 10, max: 50)

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Kruger Safari Adventures",
      "category": "Tourism",
      "district": "Ehlanzeni",
      "description": "Guided safari tours in Kruger National Park",
      "logo": "https://api.mpbusinesshub.co.za/uploads/businesses/1/logo.jpg",
      "package_type": "Gold"
    },
    // More search results...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "total_pages": 3
  }
}
```

#### Get Categories

```
GET /api/search/categories
```

Get all available business categories.

**Response:**

```json
{
  "status": "success",
  "data": [
    "Agriculture",
    "Construction",
    "Education",
    "Healthcare",
    "Hospitality",
    "Manufacturing",
    "Retail",
    "Technology",
    "Tourism",
    "Transport"
  ]
}
```

#### Get Districts

```
GET /api/search/districts
```

Get all available districts in Mpumalanga.

**Response:**

```json
{
  "status": "success",
  "data": [
    "Ehlanzeni",
    "Gert Sibande",
    "Nkangala"
  ]
}
```

### Statistics

#### Get Dashboard Statistics

```
GET /api/statistics/dashboard
```

Get dashboard statistics for the authenticated user's business.

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "business": {
      "id": 123,
      "name": "My Business",
      "package_type": "Gold"
    },
    "views": {
      "total": 1250,
      "today": 25,
      "this_week": 175,
      "this_month": 450
    },
    "engagement": {
      "total_contacts": 48,
      "today": 3,
      "this_week": 15,
      "this_month": 28
    },
    "popular_products": [
      {
        "id": 1,
        "name": "Popular Product 1",
        "views": 120
      },
      // More products...
    ]
  }
}
```

### Admin Endpoints

#### Get Pending Businesses

```
GET /api/admin/businesses/pending
```

Get list of businesses pending approval (admin only).

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "name": "New Business",
      "category": "Retail",
      "district": "Ehlanzeni",
      "created_at": "2023-04-01T09:30:45Z",
      "user_id": 456
    },
    // More pending businesses...
  ]
}
```

#### Update Business Status

```
PUT /api/admin/businesses/{id}/status
```

Approve or reject a business listing (admin only).

**Headers:**

```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "status": "verified" // or "rejected"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Business approved successfully"
}
```

#### Get Admin Dashboard Statistics

```
GET /api/admin/dashboard
```

Get admin dashboard statistics (admin only).

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "businesses": {
      "total": 150,
      "verified": 120,
      "pending": 25,
      "rejected": 5
    },
    "packages": {
      "basic": 50,
      "silver": 40,
      "gold": 30
    },
    "payments": {
      "recent": [
        {
          "id": 1,
          "business_name": "Business Name",
          "amount": 599.99,
          "payment_type": "monthly",
          "status": "completed",
          "created_at": "2023-04-01T09:30:45Z"
        },
        // More payments...
      ],
      "total_revenue": 25499.85
    }
  }
}
```

### Business Feature Access

#### Check Feature Access

```
GET /businesses/{id}/features/{feature}
```

Checks if a business has access to a specific feature based on its package tier.

**URL Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| id        | integer | Yes      | Business ID |
| feature   | string  | Yes      | Feature name to check (e.g., "websiteLink", "products") |

**Response:**

```json
{
  "status": "success",
  "data": {
    "has_access": true,
    "feature": "websiteLink",
    "tier": "Silver"
  }
}
```

#### Check Tier Limits

```
GET /businesses/{id}/tier-limits/{limitType}
```

Checks if a business has reached the limit for a specific feature type based on its package tier.

**URL Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| id        | integer | Yes      | Business ID |
| limitType | string  | Yes      | Limit type to check (e.g., "products", "adverts", "images") |

**Response:**

```json
{
  "status": "success",
  "data": {
    "at_limit": false,
    "current_count": 5,
    "max_allowed": 10,
    "limit_type": "products",
    "tier": "Silver"
  }
}
```

## Sample Requests

### Get all businesses
```bash
curl -X GET "https://api.mpbusinesshub.co.za/api/businesses?category=Tourism"
```

### Authentication
```bash
curl -X POST "https://api.mpbusinesshub.co.za/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"your_password"}'
```

### Creating a business listing
```bash
curl -X POST "https://api.mpbusinesshub.co.za/api/businesses" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-CSRF-Token: YOUR_CSRF_TOKEN" \
  -d '{
    "name": "Example Business",
    "description": "A description of the business",
    "category": "Tourism",
    "district": "Ehlanzeni",
    "contact_person": "John Doe",
    "email": "contact@example.com",
    "phone": "+27123456789",
    "website": "https://example.com",
    "address": "123 Main St, Nelspruit",
    "package_type": "Silver"
  }'
```

## Cache Control

The API uses Redis caching for improved performance. Cached responses will have the following header:

```
Cache-Control: max-age=60, public
```

Cached items include:
- Business listings
- Search results
- Category and district lists
- Dashboard statistics

## Cross-Origin Resource Sharing (CORS)

CORS is enabled for all API endpoints. The following origins are allowed:

- `https://mpbusinesshub.co.za`
- `https://www.mpbusinesshub.co.za`

During development, `localhost` origins are also allowed.


### 3. Update for api-reference.md

```markdown
## PostgreSQL Migration Notes

The API now uses PostgreSQL instead of MySQL. This generally requires no changes to API usage, but note these PostgreSQL-specific details:

### Query Syntax Differences
- **JSON queries**: When filtering by JSON fields, use PostgreSQL-specific JSON operators 
  (`->` for JSON keys, `->>` for JSON values as text)
- **Case sensitivity**: PostgreSQL is case-sensitive for string comparisons by default
- **Regex searches**: Use `~` for regex matching instead of MySQL's REGEXP

### Performance Considerations
- Queries using PostgreSQL's GIN indexes (for JSONB fields) may show improved performance
- Full-text search now uses PostgreSQL's built-in text search capabilities

### Example PostgreSQL-Specific Query

```sql
-- Find businesses with specific social media links
SELECT * FROM businesses 
WHERE social_media->>'facebook' IS NOT NULL 
ORDER BY created_at DESC
LIMIT 10;