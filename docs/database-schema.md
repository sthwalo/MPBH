# Database Schema

## Overview

The Mpumalanga Business Hub application uses MySQL 8.0 as its database management system. This document outlines the database schema, including tables, relationships, and field descriptions.

## Entity Relationship Diagram

```
+----------------+       +-------------------+       +------------------+
| users          |       | businesses        |<----->| products         |
+----------------+       +-------------------+       +------------------+
| PK id          |<----->| PK id             |       | PK id            |
| name           |       | FK user_id        |       | FK business_id   |
| email          |       | name              |       | name             |
| password       |       | description       |       | description      |
| phone          |       | category          |       | price            |
| reset_token    |       | district          |       | image            |
| reset_expires  |       | address           |       | status           |
| role           |       | phone             |       | created_at       |
| created_at     |       | email             |       | updated_at       |
| updated_at     |       | website           |       +------------------+
+----------------+       | social_media      |                ^
        |                | operating_hours   |                |
        |                | logo              |                |
        |                | package_type      |                |
        |                | verification_status|                |
        |                | created_at        |       +------------------+
        |                | updated_at        |<----->| reviews          |
        |                +-------------------+       +------------------+
        |                        |                   | PK id            |
        |                        |                   | FK business_id   |
        |                        |                   | FK user_id       |
        |                        |                   | rating           |
        |                        |                   | comment          |
        |                        |                   | status           |
        |                        |                   | created_at       |
        |                        |                   +------------------+
        |                        |
        |                        v
        |                +-------------------+       +------------------+
        +--------------->| payments         |<----->| statistics       |
                         +-------------------+       +------------------+
                         | PK id             |       | PK id            |
                         | FK business_id    |       | FK business_id   |
                         | payment_id        |       | view_count       |
                         | amount            |       | inquiry_count    |
                         | package_type      |       | date             |
                         | payment_type      |       | district         |
                         | status            |       | source           |
                         | reference         |       | created_at       |
                         | created_at        |       +------------------+
                         +-------------------+              ^
                                  |                         |
                                  v                         |
                         +-------------------+              |
                         | adverts          |              |
                         +-------------------+              |
                         | PK id             |              |
                         | FK business_id    |              |
                         | title             |              |
                         | description       |              |
                         | image             |              |
                         | placement         |              |
                         | start_date        |              |
                         | end_date          |              |
                         | status            |              |
                         | created_at        |              |
                         +-------------------+              |
                                                           |
        +----------------+                                 |
        | search_logs    |                                 |
        +----------------+                                 |
        | PK id          |                                 |
        | query          |                                 |
        | filters        |                                 |
        | FK user_id     |                                 |
        | ip_address     |                                 |
        | result_count   |                                 |
        | created_at     |                                 |
        +----------------+                      +------------------+
                                                | interactions    |
                                                +------------------+
                                                | PK id            |
                                                | FK business_id   |
                                                | FK user_id       |
                                                | type             |
                                                | source           |
                                                | FK product_id    |
                                                | FK advert_id     |
                                                | ip_address       |
                                                | created_at       |
                                                +------------------+
```

## Table Definitions

### users

Stores user account information for business owners and administrators.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| name         | VARCHAR(255) | NOT NULL         | User's full name                     |
| email        | VARCHAR(255) | UNIQUE, NOT NULL | User's email address                 |
| password     | VARCHAR(255) | NOT NULL         | Hashed password                      |
| phone        | VARCHAR(20)  | NULL             | User's phone number                  |
| reset_token  | VARCHAR(100) | NULL             | Password reset token                 |
| reset_expires| TIMESTAMP    | NULL             | Password reset token expiration time |
| role         | ENUM         | DEFAULT 'user'   | User role (user, admin)              |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Record update timestamp |

### businesses

Stores business information including profile details, contact information, and subscription status.

| Column        | Type         | Constraints      | Description                          |
|---------------|--------------|------------------|--------------------------------------|
| id            | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| user_id       | INT          | FK, NOT NULL     | Reference to users table             |
| name          | VARCHAR(255) | NOT NULL         | Business name                        |
| description   | TEXT         | NOT NULL         | Business description                 |
| category      | VARCHAR(100) | NOT NULL         | Business category                    |
| district      | VARCHAR(100) | NOT NULL         | Geographic district                  |
| address       | TEXT         | NOT NULL         | Physical address                     |
| phone         | VARCHAR(20)  | NOT NULL         | Contact phone number                 |
| email         | VARCHAR(255) | NOT NULL         | Contact email address                |
| website       | VARCHAR(255) | NULL             | Business website URL                 |
| social_media  | JSON         | NULL             | Social media links                   |
| operating_hours| TEXT         | NULL             | Business operating hours             |
| logo          | VARCHAR(255) | NULL             | Path to logo image                   |
| package_type  | ENUM         | DEFAULT 'Basic'  | Subscription package type            |
| verification_status | ENUM   | DEFAULT 'pending'| Status (pending, verified, rejected) |
| created_at    | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at    | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Record update timestamp |

### products

Stores products and services offered by businesses.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| name         | VARCHAR(255) | NOT NULL         | Product name                         |
| description  | TEXT         | NOT NULL         | Product description                  |
| price        | DECIMAL(10,2)| NOT NULL         | Product price                        |
| image        | VARCHAR(255) | NULL             | Path to product image                |
| status       | ENUM         | DEFAULT 'active' | Status (active, inactive)            |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Record update timestamp |

### reviews

Stores customer reviews for businesses.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| user_id      | INT          | FK, NOT NULL     | Reference to users table             |
| rating       | DECIMAL(2,1) | NOT NULL         | Rating value (0.0-5.0)               |
| comment      | TEXT         | NOT NULL         | Review comment                       |
| status       | ENUM         | DEFAULT 'pending'| Status (pending, approved, rejected) |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

### payments

Stores payment transactions for business subscriptions.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| payment_id   | VARCHAR(100) | UNIQUE, NOT NULL | Payment reference ID                 |
| amount       | DECIMAL(10,2)| NOT NULL         | Payment amount                       |
| package_type | ENUM         | NOT NULL         | Package type (Basic, Silver, Gold)   |
| payment_type | ENUM         | NOT NULL         | Payment type (monthly, annual)       |
| status       | ENUM         | DEFAULT 'pending'| Status (pending, completed, failed)  |
| reference    | VARCHAR(255) | NULL             | Payment gateway reference            |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

### statistics

Stores business statistics for analytics.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| view_count   | INT          | DEFAULT 0        | Number of profile views              |
| inquiry_count| INT          | DEFAULT 0        | Number of inquiries                  |
| date         | DATE         | NOT NULL         | Statistics date                      |
| district     | VARCHAR(100) | NULL             | Visitor district                     |
| source       | VARCHAR(100) | NULL             | Traffic source                       |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

### adverts

Stores business advertisements.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| title        | VARCHAR(255) | NOT NULL         | Advertisement title                  |
| description  | TEXT         | NOT NULL         | Advertisement description            |
| image        | VARCHAR(255) | NOT NULL         | Path to advertisement image          |
| placement    | ENUM         | NOT NULL         | Placement location                   |
| start_date   | DATETIME     | NOT NULL         | Start date and time                  |
| end_date     | DATETIME     | NOT NULL         | End date and time                    |
| status       | ENUM         | DEFAULT 'pending'| Status (pending, active, expired)    |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

### interactions

Stores user interactions with businesses.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT          | FK, NOT NULL     | Reference to businesses table        |
| user_id      | INT          | FK, NULL         | Reference to users table             |
| type         | ENUM         | NOT NULL         | Interaction type                     |
| source       | VARCHAR(255) | NULL             | Traffic source                       |
| product_id   | INT          | FK, NULL         | Reference to products table          |
| advert_id    | INT          | FK, NULL         | Reference to adverts table           |
| ip_address   | VARCHAR(45)  | NULL             | Visitor IP address                   |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

### search_logs

Stores search queries and results for analytics.

| Column       | Type         | Constraints      | Description                          |
|--------------|--------------|------------------|--------------------------------------|
| id           | INT          | PK, AUTO_INCREMENT| Unique identifier                    |
| query        | VARCHAR(255) | NOT NULL         | Search query text                    |
| filters      | JSON         | NULL             | Search filters used                  |
| user_id      | INT          | FK, NULL         | Reference to users table (if authenticated) |
| ip_address   | VARCHAR(45)  | NULL             | Searcher IP address                  |
| result_count | INT          | DEFAULT 0        | Number of search results             |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |

## Indexes

### Primary Keys
- `users.id`
- `businesses.id`
- `products.id`
- `reviews.id`
- `payments.id`
- `statistics.id`
- `adverts.id`
- `interactions.id`
- `search_logs.id`

### Foreign Keys
- `businesses.user_id` → `users.id`
- `products.business_id` → `businesses.id`
- `reviews.business_id` → `businesses.id`
- `reviews.user_id` → `users.id`
- `payments.business_id` → `businesses.id`
- `statistics.business_id` → `businesses.id`
- `adverts.business_id` → `businesses.id`
- `interactions.business_id` → `businesses.id`
- `interactions.user_id` → `users.id`
- `interactions.product_id` → `products.id`
- `interactions.advert_id` → `adverts.id`
- `search_logs.user_id` → `users.id`

### Additional Indexes
- `users.email` (UNIQUE)
- `businesses.name` (INDEX)
- `businesses.category` (INDEX)
- `businesses.district` (INDEX)
- `businesses.verification_status` (INDEX)
- `payments.payment_id` (UNIQUE)
- `statistics.date` (INDEX)
- `adverts.status` (INDEX)
- `adverts.start_date` (INDEX)
- `adverts.end_date` (INDEX)
- `search_logs.query` (INDEX)

## Constraints

### Cascading Deletes
- When a user is deleted, all associated businesses are deleted
- When a business is deleted, all associated products, reviews, payments, adverts, and statistics are deleted

### Validation
- `businesses.package_type` must be one of: 'Basic', 'Silver', 'Gold'
- `businesses.verification_status` must be one of: 'pending', 'verified', 'rejected'
- `reviews.rating` must be between 0.0 and 5.0
- `payments.status` must be one of: 'pending', 'completed', 'failed'
- `adverts.status` must be one of: 'pending', 'active', 'expired'
- `users.role` must be one of: 'user', 'admin'
