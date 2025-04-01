-- Mpumalanga Business Hub - Database Schema
-- Version 1.0.0

-- Create database
CREATE DATABASE IF NOT EXISTS mpbh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mpbh;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  reset_token VARCHAR(255) NULL,
  reset_token_expires TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Packages table
CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  monthly_price DECIMAL(10,2) NOT NULL,
  annual_price DECIMAL(10,2) NOT NULL,
  features TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  package_type VARCHAR(50) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
  payment_method VARCHAR(50) NOT NULL DEFAULT 'PayFast',
  payment_reference VARCHAR(100) NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'active',
  next_billing_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (package_type),
  INDEX (status)
);

-- Businesses table
CREATE TABLE IF NOT EXISTS businesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  category VARCHAR(100) NOT NULL,
  district VARCHAR(100) NOT NULL,
  address VARCHAR(255) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(255) NULL,
  website VARCHAR(255) NULL,
  logo VARCHAR(255) NULL,
  cover_image VARCHAR(255) NULL,
  package_type VARCHAR(50) NOT NULL DEFAULT 'Basic',
  subscription_id INT NULL,
  verification_status VARCHAR(50) NOT NULL DEFAULT 'pending',
  social_media JSON NULL,
  business_hours JSON NULL,
  longitude DECIMAL(10,7) NULL,
  latitude DECIMAL(10,7) NULL,
  adverts_remaining INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (user_id),
  INDEX (category),
  INDEX (district),
  INDEX (package_type),
  INDEX (subscription_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add foreign key constraints after tables are created
ALTER TABLE subscriptions 
  ADD CONSTRAINT fk_subscription_business 
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE;

ALTER TABLE businesses
  ADD CONSTRAINT fk_business_subscription 
  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL;

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL,
  discount_price DECIMAL(10,2) NULL,
  image VARCHAR(255) NULL,
  is_featured BOOLEAN NOT NULL DEFAULT FALSE,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (name),
  INDEX (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  user_name VARCHAR(100) NOT NULL,
  user_email VARCHAR(255) NULL,
  rating TINYINT NOT NULL,
  comment TEXT NULL,
  reply TEXT NULL,
  reply_date TIMESTAMP NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'approved',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (rating),
  INDEX (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CHECK (rating BETWEEN 1 AND 5)
);

-- Adverts table
CREATE TABLE IF NOT EXISTS adverts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  image VARCHAR(255) NULL,
  click_count INT NOT NULL DEFAULT 0,
  impression_count INT NOT NULL DEFAULT 0,
  status VARCHAR(50) NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (status),
  INDEX idx_advert_dates (start_date, end_date),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CHECK (end_date >= start_date)
);

-- Analytics table for business page views
CREATE TABLE IF NOT EXISTS analytics_page_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  ip_address VARCHAR(50) NULL,
  user_agent TEXT NULL,
  referrer VARCHAR(255) NULL,
  viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (business_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Analytics table for business inquiries
CREATE TABLE IF NOT EXISTS analytics_inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  message TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Payments history table
CREATE TABLE IF NOT EXISTS payment_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  subscription_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'ZAR',
  payment_method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(100) NULL,
  invoice_number VARCHAR(50) NOT NULL,
  billing_period_start DATE NOT NULL,
  billing_period_end DATE NOT NULL,
  package_type VARCHAR(50) NOT NULL,
  status VARCHAR(50) NOT NULL,
  payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (business_id),
  INDEX (subscription_id),
  INDEX (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
);

-- Insert default package data
INSERT INTO packages (name, monthly_price, annual_price, features) VALUES
('Basic', 0, 0, '{"listing_visibility":true,"contact_links":false,"products":false,"monthly_adverts":0,"featured_placement":false}'),
('Bronze', 200, 2000, '{"listing_visibility":true,"contact_links":true,"products":false,"monthly_adverts":0,"featured_placement":false}'),
('Silver', 500, 5000, '{"listing_visibility":true,"contact_links":true,"products":true,"monthly_adverts":1,"featured_placement":false}'),
('Gold', 1000, 10000, '{"listing_visibility":true,"contact_links":true,"products":true,"monthly_adverts":4,"featured_placement":true}')
ON DUPLICATE KEY UPDATE
  monthly_price = VALUES(monthly_price),
  annual_price = VALUES(annual_price),
  features = VALUES(features);
