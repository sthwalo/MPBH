-- First, create a function for updating timestamps
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create ENUM replacements as custom types
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'package_type_enum') THEN
        CREATE TYPE package_type_enum AS ENUM ('Basic', 'Silver', 'Gold');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'verification_status_enum') THEN
        CREATE TYPE verification_status_enum AS ENUM ('pending', 'verified', 'rejected');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_status_enum') THEN
        CREATE TYPE product_status_enum AS ENUM ('active', 'inactive');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'review_status_enum') THEN
        CREATE TYPE review_status_enum AS ENUM ('pending', 'approved', 'rejected');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'advert_status_enum') THEN
        CREATE TYPE advert_status_enum AS ENUM ('pending', 'active', 'rejected', 'expired');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'advert_placement_enum') THEN
        CREATE TYPE advert_placement_enum AS ENUM ('sidebar', 'banner', 'featured');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_type_enum') THEN
        CREATE TYPE payment_type_enum AS ENUM ('upgrade', 'advert');
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_status_enum') THEN
        CREATE TYPE payment_status_enum AS ENUM ('pending', 'completed', 'failed');
    END IF;
END $$;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add trigger for users
CREATE OR REPLACE TRIGGER update_users_timestamp
BEFORE UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Businesses table
CREATE TABLE IF NOT EXISTS businesses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo VARCHAR(255),
    cover_image VARCHAR(255),
    package_type package_type_enum DEFAULT 'Basic',
    subscription_id VARCHAR(100),
    verification_status verification_status_enum DEFAULT 'pending',
    social_media JSONB,
    business_hours JSONB,
    longitude DECIMAL(10, 8),
    latitude DECIMAL(11, 8),
    adverts_remaining INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add trigger for businesses
CREATE OR REPLACE TRIGGER update_businesses_timestamp
BEFORE UPDATE ON businesses
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2),
    image VARCHAR(255),
    status product_status_enum DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Add trigger for products
CREATE OR REPLACE TRIGGER update_products_timestamp
BEFORE UPDATE ON products
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    reviewer_name VARCHAR(255) NOT NULL,
    rating DECIMAL(2, 1) NOT NULL,
    comment TEXT NOT NULL,
    status review_status_enum DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add trigger for reviews
CREATE OR REPLACE TRIGGER update_reviews_timestamp
BEFORE UPDATE ON reviews
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Adverts table
CREATE TABLE IF NOT EXISTS adverts (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    url VARCHAR(255),
    status advert_status_enum DEFAULT 'pending',
    placement advert_placement_enum DEFAULT 'sidebar',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Add trigger for adverts
CREATE OR REPLACE TRIGGER update_adverts_timestamp
BEFORE UPDATE ON adverts
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type payment_type_enum NOT NULL,
    package_type package_type_enum DEFAULT 'Basic',
    status payment_status_enum DEFAULT 'pending',
    transaction_id VARCHAR(100),
    processor_response JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Add trigger for payments
CREATE OR REPLACE TRIGGER update_payments_timestamp
BEFORE UPDATE ON payments
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Analytics - Page Views
CREATE TABLE IF NOT EXISTS analytics_page_views (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Analytics - Product Views
CREATE TABLE IF NOT EXISTS analytics_product_views (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    ip_address VARCHAR(45),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Analytics - Advert Clicks
CREATE TABLE IF NOT EXISTS analytics_advert_clicks (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    advert_id INTEGER NOT NULL,
    ip_address VARCHAR(45),
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (advert_id) REFERENCES adverts(id) ON DELETE CASCADE
);

-- Analytics - Inquiries
CREATE TABLE IF NOT EXISTS analytics_inquiries (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    inquiry_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_businesses_category ON businesses(category);
CREATE INDEX IF NOT EXISTS idx_businesses_district ON businesses(district);
CREATE INDEX IF NOT EXISTS idx_businesses_package_type ON businesses(package_type);
CREATE INDEX IF NOT EXISTS idx_products_business_id ON products(business_id);
CREATE INDEX IF NOT EXISTS idx_reviews_business_id ON reviews(business_id);
CREATE INDEX IF NOT EXISTS idx_adverts_business_id ON adverts(business_id);
CREATE INDEX IF NOT EXISTS idx_adverts_placement ON adverts(placement);
CREATE INDEX IF NOT EXISTS idx_payments_business_id ON payments(business_id);
CREATE INDEX IF NOT EXISTS idx_payments_reference ON payments(reference);
