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

## Error Handling

The API uses standard HTTP response codes to indicate success or failure:

- `2xx` - Success
- `4xx` - Client error (invalid request)
- `5xx` - Server error

Error responses include a JSON payload with error details:

```json
{
  "status": "error",
  "code": 400,
  "message": "Validation failed",
  "errors": {
    "email": "Email is required"
  }
}
```

## Rate Limiting

API requests are subject to rate limiting to prevent abuse. The current limits are:

- 100 requests per minute for authenticated users
- 30 requests per minute for unauthenticated users

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1617567600
```

## API Endpoints

### Authentication

#### Register a new business

```
POST /api/auth/register
```

**Request Body:**

```json
{
  "email": "business@example.com",
  "password": "securepassword",
  "businessName": "Example Business",
  "category": "Tourism",
  "description": "A sample business description",
  "address": "123 Main St, Mbombela",
  "phone": "+27 13 123 4567",
  "packageType": "Basic"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Registration successful",
  "data": {
    "user_id": 123,
    "business_id": 456,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

#### Login

```
POST /api/auth/login
```

**Request Body:**

```json
{
  "email": "business@example.com",
  "password": "securepassword"
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 123,
      "email": "business@example.com",
      "business_id": 456
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

#### Logout

```
POST /api/auth/logout
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

### Businesses

#### Get all businesses

```
GET /api/businesses
```

**Query Parameters:**

| Parameter  | Type   | Description                             |
|------------|--------|-----------------------------------------|
| category   | string | Filter by business category             |
| district   | string | Filter by district                      |
| search     | string | Search term for name or description     |
| page       | number | Page number for pagination              |
| limit      | number | Number of records per page (default 20) |
| sort       | string | Sort field (name, created_at, rating)   |
| order      | string | Sort order (asc, desc)                  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "businesses": [
      {
        "id": 1,
        "name": "Kruger Safari Adventures",
        "description": "Luxury safari tours in the Kruger National Park",
        "category": "Tourism",
        "district": "Mbombela",
        "address": "123 Elephant Road, Hazyview",
        "phone": "+27 13 123 4567",
        "email": "info@krugersafari.co.za",
        "website": "https://www.krugersafari.co.za",
        "logo": "https://example.com/logos/kruger.jpg",
        "package_type": "Gold",
        "rating": 4.8,
        "created_at": "2025-01-15T08:30:00Z"
      },
      // More businesses...
    ],
    "pagination": {
      "total": 150,
      "page": 1,
      "limit": 20,
      "pages": 8
    }
  }
}
```

#### Get a specific business

```
GET /api/businesses/{id}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Kruger Safari Adventures",
    "description": "Luxury safari tours in the Kruger National Park",
    "category": "Tourism",
    "district": "Mbombela",
    "address": "123 Elephant Road, Hazyview",
    "phone": "+27 13 123 4567",
    "email": "info@krugersafari.co.za",
    "website": "https://www.krugersafari.co.za",
    "logo": "https://example.com/logos/kruger.jpg",
    "cover_image": "https://example.com/covers/kruger.jpg",
    "package_type": "Gold",
    "rating": 4.8,
    "reviews": [
      {
        "id": 101,
        "user_name": "John Smith",
        "rating": 5,
        "comment": "Excellent safari experience!",
        "created_at": "2025-02-10T14:30:00Z"
      },
      // More reviews...
    ],
    "products": [
      {
        "id": 201,
        "name": "Full Day Safari",
        "description": "A full day guided safari in the Kruger Park",
        "price": 1500,
        "image": "https://example.com/products/safari.jpg"
      },
      // More products...
    ],
    "created_at": "2025-01-15T08:30:00Z",
    "updated_at": "2025-03-20T10:15:00Z"
  }
}
```

#### Get my business (authenticated)

```
GET /api/businesses/my-business
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

Similar to the "Get a specific business" endpoint, but includes additional fields related to the business owner's account.

#### Update business details (authenticated)

```
PUT /api/businesses/my-business
```

**Headers:**

```
Authorization: Bearer <token>
```

**Request Body:**

```json
{
  "name": "Updated Business Name",
  "description": "Updated business description",
  "address": "456 New Street, Mbombela",
  "phone": "+27 13 987 6543",
  "email": "newemail@business.com",
  "website": "https://www.updatedbusiness.co.za"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Business updated successfully",
  "data": {
    "id": 456,
    "name": "Updated Business Name",
    // Other updated fields...
    "updated_at": "2025-04-01T09:45:00Z"
  }
}
```

### Products

#### Get products for a business

```
GET /api/businesses/{id}/products
```

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 201,
      "name": "Full Day Safari",
      "description": "A full day guided safari in the Kruger Park",
      "price": 1500,
      "image": "https://example.com/products/safari.jpg",
      "created_at": "2025-01-20T11:30:00Z"
    },
    // More products...
  ]
}
```

#### Create a product (authenticated)

```
POST /api/products
```

**Headers:**

```
Authorization: Bearer <token>
```

**Request Body:**

```json
{
  "name": "New Product",
  "description": "Product description",
  "price": 750,
  "image": "base64-encoded-image-data" // Optional
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 204,
    "name": "New Product",
    "description": "Product description",
    "price": 750,
    "image": "https://example.com/products/new-product.jpg",
    "created_at": "2025-04-01T10:15:00Z"
  }
}
```

#### Update a product (authenticated)

```
PUT /api/products/{id}
```

**Headers:**

```
Authorization: Bearer <token>
```

**Request Body:**

```json
{
  "name": "Updated Product Name",
  "description": "Updated product description",
  "price": 800
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Product updated successfully",
  "data": {
    "id": 204,
    "name": "Updated Product Name",
    "description": "Updated product description",
    "price": 800,
    "image": "https://example.com/products/new-product.jpg",
    "updated_at": "2025-04-01T11:30:00Z"
  }
}
```

#### Delete a product (authenticated)

```
DELETE /api/products/{id}
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "message": "Product deleted successfully"
}
```

### Payments

#### Get package information

```
GET /api/payments/packages
```

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
      "price": 0,
      "billing_cycle": "once",
      "features": [
        "Business listing",
        "Contact information",
        "Basic analytics",
        "Community reviews"
      ],
      "limits": {
        "adverts": 0,
        "products": 0
      }
    },
    {
      "id": "silver",
      "name": "Silver",
      "price": 500,
      "billing_cycle": "monthly",
      "features": [
        "Everything in Basic",
        "Product catalog (up to 20 products)",
        "Featured in category searches",
        "Advanced analytics",
        "1 advert slot per month"
      ],
      "limits": {
        "adverts": 1,
        "products": 20
      }
    },
    {
      "id": "gold",
      "name": "Gold",
      "price": 1000,
      "billing_cycle": "monthly",
      "features": [
        "Everything in Silver",
        "Unlimited products",
        "Priority listing in search results",
        "Featured on homepage",
        "Social media promotion",
        "3 advert slots per month"
      ],
      "limits": {
        "adverts": 3,
        "products": 0
      }
    }
  ]
}
```

#### Get payment history (authenticated)

```
GET /api/payments/history
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "payments": [
      {
        "id": 1,
        "reference": "MPBH_1617283940_1234",
        "amount": 1000,
        "payment_type": "upgrade",
        "package_type": "Gold",
        "status": "completed",
        "transaction_id": "PF12345678",
        "created_at": "2025-04-01T10:30:00Z"
      },
      // More payment records...
    ],
    "statistics": {
      "total_spent": 3500,
      "successful_payments": 4,
      "failed_payments": 1,
      "last_payment_date": "2025-04-01T10:30:00Z"
    },
    "current_package": "Gold",
    "subscription_id": "SUB12345"
  }
}
```

#### Initiate payment (authenticated)

```
POST /api/payments/initiate
```

**Headers:**

```
Authorization: Bearer <token>
```

**Request Body:**

```json
{
  "package_type": "Gold",
  "payment_type": "upgrade"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Payment initiated successfully",
  "data": {
    "payment_id": 25,
    "reference": "MPBH_1617283940_5678",
    "amount": 1000,
    "payment_url": "https://sandbox.payfast.co.za/eng/process?merchant_id=10000100&merchant_key=abcdefgh&..."
  }
}
```

#### Payment webhook (for payment processor callbacks)

```
POST /api/payments/webhook
```

**Request Body:**
```json
{
  "payment_reference": "MPBH_1617283940_5678",
  "payment_status": "COMPLETE",
  "transaction_id": "PF87654321",
  "subscription_id": "SUB54321"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Webhook processed successfully"
}
```

### Adverts

#### Get all adverts for my business (authenticated)

```
GET /api/adverts
```

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
      "title": "Special Safari Discount",
      "description": "20% off all safari packages booked in April!",
      "start_date": "2025-04-01T00:00:00Z",
      "end_date": "2025-04-30T23:59:59Z",
      "status": "active",
      "created_at": "2025-03-15T14:30:00Z"
    },
    // More adverts...
  ]
}
```

#### Create a new advert (authenticated)

```
POST /api/adverts
```

**Headers:**

```
Authorization: Bearer <token>
```

**Request Body:**

```json
{
  "title": "Summer Holiday Package",
  "description": "Exclusive summer holiday deals for families",
  "start_date": "2025-06-01T00:00:00Z",
  "end_date": "2025-06-30T23:59:59Z"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Advert created successfully",
  "data": {
    "id": 3,
    "title": "Summer Holiday Package",
    "description": "Exclusive summer holiday deals for families",
    "start_date": "2025-06-01T00:00:00Z",
    "end_date": "2025-06-30T23:59:59Z",
    "status": "scheduled",
    "created_at": "2025-04-01T09:30:00Z"
  }
}
```

#### Delete an advert (authenticated)

```
DELETE /api/adverts/{id}
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "message": "Advert deleted successfully"
}
```

### Statistics and Analytics

#### Get dashboard statistics (authenticated)

```
GET /api/statistics/dashboard
```

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
      "name": "Kruger Safari Adventures",
      "package_type": "Gold",
      "verification_status": "verified",
      "adverts_remaining": 2
    },
    "statistics": {
      "visitors": {
        "total_views": 3450,
        "unique_visitors": 2103,
        "views_last_7_days": 245,
        "views_last_30_days": 876,
        "daily_views": [
          { "date": "2025-03-03", "views": 26 },
          { "date": "2025-03-04", "views": 31 },
          // Additional days...
        ]
      },
      "reviews": {
        "total_reviews": 47,
        "average_rating": 4.7,
        "approved_reviews": 42,
        "pending_reviews": 5,
        "new_reviews": 8,
        "rating_breakdown": [
          { "rating": 1, "count": 1 },
          { "rating": 2, "count": 2 },
          { "rating": 3, "count": 3 },
          { "rating": 4, "count": 12 },
          { "rating": 5, "count": 29 }
        ]
      },
      "inquiries": {
        "total_inquiries": 156,
        "inquiries_last_7_days": 12,
        "inquiries_last_30_days": 43,
        "weekly_inquiries": [
          { "week": 202513, "count": 8 },
          { "week": 202514, "count": 12 },
          // Additional weeks...
        ]
      },
      "products": {
        "total_products": 24,
        "active_products": 20,
        "new_products": 3,
        "top_products": [
          { "product_id": 45, "name": "Safari Day Pass", "views": 312 },
          { "product_id": 23, "name": "Wildlife Photography Tour", "views": 245 },
          // Additional products...
        ]
      },
      "adverts": {
        "total_adverts": 12,
        "active_adverts": 3,
        "pending_adverts": 1,
        "expired_adverts": 8,
        "adverts_remaining": 2,
        "advert_clicks": [
          { "advert_id": 8, "title": "Summer Safari Special", "clicks": 125 },
          { "advert_id": 10, "title": "Family Weekend Package", "clicks": 87 },
          // Additional adverts...
        ]
      }
    },
    "payments": {
      "total_spent": 5000,
      "successful_payments": 5,
      "failed_payments": 0,
      "last_payment_date": "2025-03-01T10:15:30Z"
    }
  }
}
```

#### Get traffic by location statistics (authenticated)

```
GET /api/statistics/location
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": [
    { "location": "Nelspruit", "visits": 150 },
    { "location": "White River", "visits": 85 },
    { "location": "Sabie", "visits": 63 },
    { "location": "Barberton", "visits": 47 },
    { "location": "Hazyview", "visits": 35 },
    { "location": "Other Mpumalanga", "visits": 120 },
    { "location": "Gauteng", "visits": 180 },
    { "location": "Other Provinces", "visits": 95 },
    { "location": "International", "visits": 25 }
  ]
}
```

#### Get traffic by referral source (authenticated)

```
GET /api/statistics/referral
```

**Headers:**

```
Authorization: Bearer <token>
```

**Response:**

```json
{
  "status": "success",
  "data": [
    { "source": "Direct", "visits": 245 },
    { "source": "Directory Search", "visits": 185 },
    { "source": "Google", "visits": 165 },
    { "source": "Facebook", "visits": 110 },
    { "source": "Twitter", "visits": 35 },
    { "source": "Other Social Media", "visits": 45 },
    { "source": "Adverts", "visits": 55 },
    { "source": "Other", "visits": 60 }
  ]
}
```

#### Log user interaction with a business (public)

```
POST /api/statistics/log/{id}
```

**Path Parameters:**

- `id` - Business ID

**Request Body:**

```json
{
  "type": "page_view", // page_view, product_view, advert_click, inquiry
  "product_id": 45, // Optional, required for product_view
  "advert_id": 8, // Optional, required for advert_click
  "inquiry_type": "contact" // Optional, required for inquiry
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Interaction logged successfully"
}
```

## Versioning

The API may evolve over time. To ensure backward compatibility, versioning is implemented in the URL:

```
https://api.mpbusinesshub.co.za/v1/businesses
```

The current version is v1. When breaking changes are introduced, a new version (v2, v3, etc.) will be created.

## Rate Limits

To prevent abuse and ensure fair usage, the API implements rate limiting:

| Endpoint                   | Unauthenticated | Authenticated |
|----------------------------|-----------------|---------------|
| `/api/auth/*`              | 10/min          | N/A           |
| `/api/businesses` (GET)    | 30/min          | 100/min       |
| `/api/businesses/*` (GET)  | 20/min          | 100/min       |
| Authenticated endpoints    | N/A             | 60/min        |

## Cross-Origin Resource Sharing (CORS)

The API supports CORS for browser-based applications. The following origins are allowed:

- `https://mpbusinesshub.co.za`
- `https://www.mpbusinesshub.co.za`

During development, `localhost` origins are also allowed.
