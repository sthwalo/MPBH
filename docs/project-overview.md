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
- **Tiered Memberships**: Access different levels of features based on subscription level (Basic, Bronze, Silver, Gold)
- **Dashboard**: Manage business information, track statistics, and handle subscriptions
- **Product Management**: Add and manage products/services (Silver and Gold tiers)
- **Advertising**: Create promotional advertisements to reach more potential customers (Silver and Gold tiers)
- **Analytics**: Track profile views, inquiries, and customer engagement

### For Users/Customers

- **Business Discovery**: Search and browse businesses by category, location, or specific criteria
- **Advanced Filtering**: Filter businesses based on various parameters
- **Business Details**: View comprehensive information about businesses including location, contact details, and offerings
- **Reviews and Ratings**: Read and submit reviews for businesses

## Technology Stack

| **Layer**       | **Technology**          | **Version** | **Purpose**  |
|-----------------|-------------------------|-------------|-------------|
| **Frontend**    | React + Vite            | 18.x        | Dynamic UI  |  
| **State**       | Context API             | -           | Client state |  
| **Backend**     | PHP Slim Framework      | 8.1+        | REST API    |  
| **Database**    | MySQL                   | 8.0         | Data storage |  
| **Search**      | Fuse.js                 | 6.6         | Client-side search |  
| **Payments**    | PayFast                 | -           | Subscription billing |  
| **Maps**        | Google Maps API         | -           | Location services |  

## Project Structure

The project follows a client-server architecture with a clear separation between the frontend and backend components:

```
mpbusinesshub/
├── client/               # React Frontend
│   ├── src/
│   │   ├── components/   # Reusable UI components
│   │   ├── pages/        # Page components
│   │   ├── context/      # State management
│   │   ├── hooks/        # Custom React hooks
│   │   ├── utils/        # Utility functions
│   │   └── assets/       # Static assets
├── server/               # PHP Backend
│   ├── api/              # API endpoints
│   ├── config/           # Configuration files
│   ├── models/           # Data models
│   ├── middleware/       # Request middleware
│   └── utils/            # Utility functions
├── docs/                 # Documentation
└── scripts/              # Deployment and utility scripts
```

## Membership Tiers

The platform offers a tiered membership system with increasing features and capabilities:

| Feature                   | Basic | Bronze | Silver | Gold |
|---------------------------|-------|--------|--------|------|
| **Listing Visibility**    | ✅    | ✅     | ✅     | ✅   |
| **Contact Links**         | ❌    | ✅     | ✅     | ✅   |
| **Product Listings**      | ❌    | ❌     | ✅     | ✅   |
| **Monthly Adverts**       | 0     | 0      | 1      | 4    |
| **Featured Placement**    | ❌    | ❌     | ❌     | ✅   |
| **Analytics Dashboard**   | ❌    | ✅     | ✅     | ✅   |
| **Price (Monthly)**       | Free  | R200   | R500   | R1000|
| **Price (Annual)**        | Free  | R2000  | R5000  | R10000|

## Implementation Roadmap

### Phase 1: Core Functionality (Completed)

- Frontend components and pages
- Basic business listing features
- User authentication and registration
- Business owner dashboard

### Phase 2: Enhanced Features (Current)

- Complete business management features
- Subscription payment processing
- Advanced search and filtering
- Review and rating system

### Phase 3: Future Enhancements (Planned)

- Mobile application
- Business-to-business networking
- Events calendar
- Local marketplace
- Integration with additional payment gateways

## Current Status

The project is currently in Phase 2 of development with the following components completed:

- Complete frontend UI implementation
- User authentication flows
- Business registration process
- Business listing and discovery features
- Business owner dashboard with profile management
- Product management for premium tiers
- Advertising management system
- **Complete payment processing system with PayFast integration**
- **Comprehensive statistics tracking and analytics dashboard**
- **Business performance visualization with metrics for views, engagement, and revenue**
- **API endpoints for all business operations**
- **Database schema with optimized tables and relationships**
