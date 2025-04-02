# Project Overview

## Introduction

The Mpumalanga Business Hub is a comprehensive digital platform designed to connect local businesses in Mpumalanga Province across various sectors including Tourism, Agriculture, Construction, and Events. The platform serves as a directory and marketplace that enables businesses to showcase their products and services to potential customers, while providing enhanced discovery features for users.

## Project Vision

The vision for Mpumalanga Business Hub is to create a digital ecosystem that fosters economic growth in the Mpumalanga region by:

1. Increasing the visibility of local businesses
2. Facilitating connections between businesses and potential customers
3. Providing businesses with tools to manage their online presence
4. Creating a tiered membership system that offers increasing value and features

## Key Features

### For Businesses

- **Business Listings**: Register and create detailed profiles with contact information, business hours, and service descriptions
- **Tiered Memberships**: Access different levels of features based on subscription level (Basic, Silver, Gold)
- **Dashboard**: Manage business information, track statistics, and handle subscriptions
- **Product Management**: Add and manage products/services (Silver and Gold tiers)
- **Advertising**: Create promotional advertisements to reach more potential customers (Silver and Gold tiers)
- **Analytics**: Track profile views, inquiries, and customer engagement
- **Payment Processing**: Secure payment processing for membership subscriptions via PayFast
- **Admin Approval**: Business listings go through an approval process before being published

### For Users

- **Business Directory**: Browse and search for businesses by category, district, or keyword
- **Advanced Search**: Fuzzy search capabilities for finding businesses even with inexact terms
- **Business Profiles**: View detailed information about businesses, including products and services
- **Reviews and Ratings**: Read and submit reviews for businesses
- **Contact Forms**: Contact businesses directly through the platform
- **Location-Based Search**: Find businesses near a specific location
- **Interactive Map**: View businesses on a map interface

## Technical Architecture

### Frontend

- **Framework**: React.js with Vite build tool
- **State Management**: React Context API
- **Routing**: React Router v6
- **UI Components**: Custom components with responsive design
- **Styling**: CSS Modules with SCSS
- **HTTP Client**: Axios for API communication
- **Search**: Fuse.js for client-side fuzzy search

### Backend

- **Framework**: Custom PHP MVC framework
- **API**: RESTful JSON API
- **Authentication**: JWT-based authentication with PHP sessions
- **Database**: MySQL with optimized schema
- **File Storage**: Local file system with image optimization
- **Service Layer**: Business logic encapsulated in dedicated service classes
- **Rate Limiting**: Token bucket algorithm for API rate limiting
- **Input Validation**: Robust input sanitization and validation

### External Integrations

- **Payment Gateway**: PayFast for handling subscription payments
- **Email Service**: SMTP for transactional emails
- **Analytics**: Custom analytics tracking system

## Project Architecture

The project follows a client-server architecture with clean separation of concerns:

### Client-Side Structure

```
client/
├── public/          # Static assets
├── src/
│   ├── components/  # Reusable UI components
│   ├── pages/       # Page components
│   ├── utils/       # Utility functions and helpers
│   ├── errors/      # Error handling components
│   ├── App.jsx      # Main application component
│   └── main.jsx     # Application entry point
```

### Server-Side Structure

```
server/
├── public/          # Public-facing directory 
│   ├── index.php    # Entry point
│   └── uploads/     # Uploaded files (images)
├── src/
│   ├── config/      # Configuration files
│   ├── controllers/ # Request handlers
│   ├── middleware/  # Request middleware
│   ├── models/      # Data models
│   ├── services/    # Business logic services
│   ├── utils/       # Utility functions
│   └── exceptions/  # Custom exceptions
└── database/        # Database scripts
```

## Current Status

The project has been fully implemented with the following components completed:

- Complete frontend UI implementation
- User authentication flows with JWT and session handling
- Business registration process with admin approval workflow
- Business listing and discovery features
- Advanced search with Fuse.js integration
- Business owner dashboard with profile management
- Product management for premium tiers with tier-based access control
- Advertising management system
- Complete payment processing system with PayFast integration
- Comprehensive statistics tracking and analytics dashboard
- Business performance visualization with metrics for views, engagement, and revenue
- API endpoints for all business operations
- Database schema with optimized tables and relationships
- Service layer architecture implementation for better code organization
- Error handling and input validation throughout the application
- Rate limiting to prevent API abuse
- Admin dashboard for business approval and system monitoring
