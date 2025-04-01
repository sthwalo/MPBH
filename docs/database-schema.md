# Database Schema

## Overview

The Mpumalanga Business Hub application uses MySQL 8.0 as its database management system. This document outlines the database schema, including tables, relationships, and field descriptions.

## Entity Relationship Diagram

```
+----------------+       +-------------------+       +------------------+
| users          |       | businesses        |<----->| products         |
+----------------+       +-------------------+       +------------------+
| PK id          |<----->| PK id             |       | PK id            |
| email          |       | FK user_id        |       | FK business_id   |
| password       |       | name              |       | name             |
| reset_token    |       | description       |       | description      |
| reset_expires  |       | category          |       | price            |
| created_at     |       | district          |       | image            |
| updated_at     |       | address           |       | status           |
+----------------+       | phone             |       | created_at       |
        |                | email             |       | updated_at       |
        |                | website           |       +------------------+
        |                | logo              |                ^
        |                | cover_image       |                |
        |                | package_type      |                |
        |                | subscription_id   |                |
        |                | verification_status|                |
        |                | adverts_remaining |                |
        |                | created_at        |                |
        |                | updated_at        |                |
        |                +-------------------+                |
        |                      ^   ^   ^                     |
        |                      |   |   |                     |
        v                      |   |   |                     |
+----------------+             |   |   |                     |
| reviews        |<------------+   |   |                     |
+----------------+                 |   |                     |
| PK id          |                 |   |                     |
| FK business_id |                 |   |                     |
| FK user_id     |                 |   |                     |
| reviewer_name  |                 |   |                     |
| rating         |                 |   |                     |
| comment        |                 |   |                     |
| status         |                 |   |                     |
| created_at     |                 |   |                     |
| updated_at     |                 |   |                     |
+----------------+                 |   |                     |
                                  |   |                     |
+----------------+                 |   |     +----------------+
| adverts        |<---------------+   |     | analytics_product_views |
+----------------+                     |     +----------------+
| PK id          |                     |     | PK id          |
| FK business_id |                     |     | FK business_id |
| title          |                     |     | FK product_id  |
| description    |                     |     | ip_address     |
| image          |                     |     | viewed_at      |
| url            |                     |     +----------------+
| status         |                     |
| placement      |                     |
| start_date     |                     |
| end_date       |                     |
| created_at     |                     |
| updated_at     |                     |
+----------------+                     |
        |                              |
        v                              |
+------------------+                   |
| analytics_advert_clicks |            |
+------------------+                   |
| PK id            |                   |
| FK business_id   |                   |
| FK advert_id     |                   |
| ip_address       |                   |
| clicked_at       |                   |
+------------------+                   |
                                      |
+------------------+                   |
| payments         |<------------------+
+------------------+
| PK id            |
| FK business_id   |
| reference        |
| amount           |
| payment_type     |
| package_type     |
| status           |
| transaction_id   |
| processor_response |
| created_at       |
| updated_at       |
+------------------+
        ^
        |
+------------------+
| analytics_page_views |
+------------------+
| PK id            |
| FK business_id   |
| ip_address       |
| user_agent       |
| referrer         |
| viewed_at        |
+------------------+

+------------------+
| analytics_inquiries |
+------------------+
| PK id            |
| FK business_id   |
| inquiry_type     |
| ip_address       |
| created_at       |
+------------------+
```

## Tables

### users

Stores user account information.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| email        | VARCHAR(255)  | NOT NULL, UNIQUE  | User email address                   |
| password     | VARCHAR(255)  | NOT NULL          | Hashed password                      |
| reset_token  | VARCHAR(255)  | NULL              | Password reset token                 |
| reset_token_expires | DATETIME | NULL              | Password reset token expiration      |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp      |
| updated_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### businesses

Stores business profile information.

| Column        | Data Type     | Constraints       | Description                           |
|---------------|---------------|-------------------|---------------------------------------|
| id            | INT           | PK, AUTO_INCREMENT| Unique identifier                     |
| user_id       | INT           | FK, NOT NULL      | Reference to users table              |
| name          | VARCHAR(255)  | NOT NULL          | Business name                         |
| description   | TEXT          | NULL              | Business description                  |
| category      | VARCHAR(100)  | NOT NULL          | Business category                     |
| district      | VARCHAR(100)  | NOT NULL          | Business district                     |
| address       | VARCHAR(255)  | NULL              | Business address                      |
| phone         | VARCHAR(20)   | NULL              | Business phone number                 |
| email         | VARCHAR(255)  | NULL              | Business contact email                |
| website       | VARCHAR(255)  | NULL              | Business website URL                  |
| logo          | VARCHAR(255)  | NULL              | Path to logo image                    |
| cover_image   | VARCHAR(255)  | NULL              | Path to cover image                   |
| package_type  | ENUM          | DEFAULT 'Basic'   | Subscription tier (Basic, Silver, Gold)|
| subscription_id | VARCHAR(100) | NULL              | External subscription identifier      |
| verification_status | ENUM     | DEFAULT 'pending' | Status (pending, verified, rejected)  |
| social_media  | JSON          | NULL              | Social media links                    |
| business_hours | JSON         | NULL              | Business operating hours              |
| longitude     | DECIMAL(10,8) | NULL              | Geographic longitude                  |
| latitude      | DECIMAL(11,8) | NULL              | Geographic latitude                   |
| adverts_remaining | INT       | DEFAULT 0         | Number of advert slots available      |
| created_at    | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp       |
| updated_at    | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### products

Stores product information for businesses.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| name         | VARCHAR(255)  | NOT NULL          | Product name                         |
| description  | TEXT          | NULL              | Product description                  |
| price        | DECIMAL(10,2) | NULL              | Product price                        |
| image        | VARCHAR(255)  | NULL              | Path to product image                |
| status       | ENUM          | DEFAULT 'active'  | Status (active, inactive)            |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### reviews

Stores reviews for businesses.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| user_id      | INT           | FK, NOT NULL      | Reference to users table             |
| reviewer_name| VARCHAR(255)  | NOT NULL          | Name of reviewer                     |
| rating       | DECIMAL(2,1)  | NOT NULL          | Rating value (0.0-5.0)               |
| comment      | TEXT          | NOT NULL          | Review comment                       |
| status       | ENUM          | DEFAULT 'pending' | Status (pending, approved, rejected) |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### adverts

Stores promotional advertisements for businesses.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| title        | VARCHAR(255)  | NOT NULL          | Advert title                         |
| description  | TEXT          | NULL              | Advert description                   |
| image        | VARCHAR(255)  | NULL              | Path to advert image                 |
| url          | VARCHAR(255)  | NULL              | URL for advert link                  |
| status       | ENUM          | DEFAULT 'pending' | Status (pending, active, rejected, expired) |
| placement    | ENUM          | DEFAULT 'sidebar' | Placement (sidebar, banner, featured) |
| start_date   | DATE          | NULL              | Start date for advert                |
| end_date     | DATE          | NULL              | End date for advert                  |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### payments

Stores payment records for businesses.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| reference    | VARCHAR(100)  | NOT NULL, UNIQUE  | Payment reference number             |
| amount       | DECIMAL(10,2) | NOT NULL          | Payment amount                       |
| payment_type | ENUM          | NOT NULL          | Type (upgrade, advert)               |
| package_type | ENUM          | DEFAULT 'Basic'   | Package type (Basic, Silver, Gold)   |
| status       | ENUM          | DEFAULT 'pending' | Status (pending, completed, failed)  |
| transaction_id | VARCHAR(100) | NULL             | External transaction ID              |
| processor_response | JSON     | NULL             | Response from payment processor      |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp     |
| updated_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp|

### analytics_page_views

Stores page view statistics for businesses.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| ip_address   | VARCHAR(45)   | NULL              | Visitor IP address                   |
| user_agent   | TEXT          | NULL              | Browser user agent                   |
| referrer     | VARCHAR(255)  | NULL              | Referral source                      |
| viewed_at    | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | View timestamp               |

### analytics_product_views

Stores product view statistics.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| product_id   | INT           | FK, NOT NULL      | Reference to products table          |
| ip_address   | VARCHAR(45)   | NULL              | Visitor IP address                   |
| viewed_at    | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | View timestamp               |

### analytics_advert_clicks

Stores advert click statistics.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| advert_id    | INT           | FK, NOT NULL      | Reference to adverts table           |
| ip_address   | VARCHAR(45)   | NULL              | Visitor IP address                   |
| clicked_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Click timestamp              |

### analytics_inquiries

Stores business inquiry statistics.

| Column       | Data Type     | Constraints       | Description                          |
|--------------|---------------|-------------------|--------------------------------------|
| id           | INT           | PK, AUTO_INCREMENT| Unique identifier                    |
| business_id  | INT           | FK, NOT NULL      | Reference to businesses table        |
| inquiry_type | VARCHAR(50)   | NOT NULL          | Type of inquiry                      |
| ip_address   | VARCHAR(45)   | NULL              | Visitor IP address                   |
| created_at   | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP | Inquiry timestamp             |

## Indexes

The following indexes are created to optimize query performance:

| Table          | Index Name              | Columns              | Type     |
|----------------|-------------------------|----------------------|----------|
| businesses     | idx_businesses_category | category              | Non-unique |
| businesses     | idx_businesses_district | district              | Non-unique |
| businesses     | idx_businesses_package_type | package_type       | Non-unique |
| products       | idx_products_business_id | business_id          | Non-unique |
| reviews        | idx_reviews_business_id | business_id           | Non-unique |
| adverts        | idx_adverts_business_id | business_id           | Non-unique |
| adverts        | idx_adverts_placement   | placement             | Non-unique |
| payments       | idx_payments_business_id | business_id          | Non-unique |
| payments       | idx_payments_reference  | reference              | Unique    |
