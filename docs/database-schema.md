# Database Schema

The Mpumalanga Business Hub uses a PostgreSQL database with the following structure:

## Core Tables

### users
| Column              | Type          | Constraints     | Description                           |
|---------------------|---------------|----------------|---------------------------------------|
| user_id             | SERIAL        | PRIMARY KEY    | Unique identifier for the user        |
| name                | VARCHAR(255)  | NOT NULL       | User's full name                      |
| email               | VARCHAR(255)  | NOT NULL, UNIQUE| User's email address                  |
| password_hash       | VARCHAR(255)  | NOT NULL       | Bcrypt hashed password                |
| phone_number        | VARCHAR(20)   |                | User's contact phone number           |
| area_of_operation   | VARCHAR(255)  |                | User's primary region of operation    |
| language_preference | VARCHAR(50)   |                | User's preferred language             |
| is_admin            | BOOLEAN       | DEFAULT FALSE  | Whether user has admin privileges     |
| created_at          | TIMESTAMP     | DEFAULT NOW()  | When the record was created           |
| updated_at          | TIMESTAMP     |                | When the record was last updated      |

### businesses
| Column              | Type          | Constraints     | Description                           |
|---------------------|---------------|----------------|---------------------------------------|
| business_id         | SERIAL        | PRIMARY KEY    | Unique identifier for the business    |
| user_id             | INTEGER       | FOREIGN KEY    | References users table                |
| name                | VARCHAR(255)  | NOT NULL       | Business name                         |
| description         | TEXT          |                | Business description                  |
| category            | VARCHAR(100)  | NOT NULL       | Business category                     |
| district            | VARCHAR(100)  | NOT NULL       | District/region                       |
| address             | TEXT          |                | Physical address                      |
| phone               | VARCHAR(20)   |                | Contact phone                         |
| email               | VARCHAR(255)  | NOT NULL       | Contact email                         |
| website             | VARCHAR(255)  |                | Website URL                           |
| logo                | VARCHAR(255)  |                | Logo image path                       |
| cover_image         | VARCHAR(255)  |                | Cover image path                      |
| package_type        | VARCHAR(50)   | DEFAULT 'Basic'| Subscription package (Basic/Silver/Gold) |
| subscription_id     | INTEGER       |                | References subscriptions table        |
| verification_status | VARCHAR(50)   | DEFAULT 'pending'| Verification status                  |
| social_media        | JSONB         |                | Social media links                    |
| business_hours      | JSONB         |                | Operating hours                       |
| longitude           | DECIMAL(10,7) |                | Business location longitude           |
| latitude            | DECIMAL(10,7) |                | Business location latitude            |
| adverts_remaining   | INTEGER       | DEFAULT 0      | Number of adverts user can post       |
| created_at          | TIMESTAMP     | DEFAULT NOW()  | When the record was created           |
| updated_at          | TIMESTAMP     |                | When the record was last updated      |

## Additional Tables

### products
| Column              | Type          | Constraints     | Description                           |
|---------------------|---------------|----------------|---------------------------------------|
| product_id          | SERIAL        | PRIMARY KEY    | Unique identifier for the product     |
| business_id         | INTEGER       | FOREIGN KEY    | References businesses table           |
| name                | VARCHAR(255)  | NOT NULL       | Product name                          |
| description         | TEXT          |                | Product description                   |
| price               | DECIMAL(10,2) |                | Product price                         |
| image               | VARCHAR(255)  |                | Product image path                    |
| created_at          | TIMESTAMP     | DEFAULT NOW()  | When the record was created           |
| updated_at          | TIMESTAMP     |                | When the record was last updated      |

### adverts
| Column              | Type          | Constraints     | Description                           |
|---------------------|---------------|----------------|---------------------------------------|
| advert_id           | SERIAL        | PRIMARY KEY    | Unique identifier for the advert      |
| business_id         | INTEGER       | FOREIGN KEY    | References businesses table           |
| title               | VARCHAR(255)  | NOT NULL       | Advert title                          |
| description         | TEXT          | NOT NULL       | Advert description                    |
| image               | VARCHAR(255)  |                | Advert image path                     |
| start_date          | DATE          | NOT NULL       | When advert starts displaying         |
| end_date            | DATE          | NOT NULL       | When advert stops displaying          |
| status              | VARCHAR(50)   | DEFAULT 'pending'| Advert status                        |
| created_at          | TIMESTAMP     | DEFAULT NOW()  | When the record was created           |
| updated_at          | TIMESTAMP     |                | When the record was last updated      |

### reviews
| Column              | Type          | Constraints     | Description                           |
|---------------------|---------------|----------------|---------------------------------------|
| review_id           | SERIAL        | PRIMARY KEY    | Unique identifier for the review      |
| business_id         | INTEGER       | FOREIGN KEY    | References businesses table           |
| user_id             | INTEGER       | FOREIGN KEY    | References users table                |
| rating              | INTEGER       | NOT NULL       | Rating (1-5)                          |
| comment             | TEXT          |                | Review comment                        |
| status              | VARCHAR(50)   | DEFAULT 'pending'| Review status                        |
| created_at          | TIMESTAMP     | DEFAULT NOW()  | When the record was created           |
| updated_at          | TIMESTAMP     |                | When the record was last updated      |
