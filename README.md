# Mpumalanga Business Hub

A web platform connecting businesses in Mpumalanga Province with potential customers through a comprehensive business directory and resource center.

## Overview

The Mpumalanga Business Hub is designed to promote economic growth in the region by providing businesses with online visibility and connecting them with customers. The platform features a tiered membership system with increasing benefits based on subscription level.

## Features

- **User Registration & Authentication**: Secure JWT-based authentication
- **Business Profiles**: Comprehensive business information including contact details, operating hours, and descriptions
- **Business Directory**: Searchable and filterable listing of local businesses
- **Business Dashboard**: Statistics and profile management for business owners
- **Tiered Memberships**: Different packages with varying benefits

## Technology Stack

### Frontend
- **Framework**: React with Vite
- **Styling**: Tailwind CSS
- **State Management**: React Context API
- **Routing**: React Router v6

### Backend
- **Language**: PHP 8.x
- **Database**: PostgreSQL
- **Authentication**: JWT (JSON Web Tokens)
- **Server**: Apache (for production)

## Membership Tiers

| Feature                    | Basic (Free) | Bronze (R200) | Silver (R500) | Gold (R1000) |
|----------------------------|-------------|--------------|--------------|-------------|
| **Business Name Listing**  | u2705        | u2705         | u2705         | u2705        |
| **Area of Operation**      | u2705        | u2705         | u2705         | u2705        |
| **Website Link**           | u274c        | u2705         | u2705         | u2705        |
| **WhatsApp Number**        | u274c        | u2705         | u2705         | u2705        |
| **Email Contact**          | u274c        | u274c         | u2705         | u2705        |
| **Star Ratings**           | u274c        | u2705         | u2705         | u2705        |
| **Product Catalog**        | u274c        | u274c         | u2705         | u2705        |
| **Monthly Adverts**        | 0           | 0            | 1            | 4           |
| **Social Media Feature**   | u274c        | u274c         | u274c         | 1/month     |

**Annual Pricing**: Bronze: R2,000/year, Silver: R5,000/year, Gold: R10,000/year

## Getting Started

### Prerequisites
- Node.js (>= 14.x)
- PHP (>= 8.1)
- PostgreSQL (>= 13.0)
- Composer

### Installation

1. Clone the repository
```bash
git clone <repository-url>
cd MPBH
```

2. Install backend dependencies
```bash
cd server
composer install
```

3. Configure environment variables
```bash
cp .env.example .env
# Edit .env with your database credentials
```

4. Set up the database
```bash
php setup-database.php
```

5. Install frontend dependencies
```bash
cd ../client
npm install
```

### Running the Development Server

1. Start the PHP backend server
```bash
cd server
php -S localhost:8000 -t public
```

2. Start the frontend development server
```bash
cd client
npm run dev
```

3. Access the application at http://localhost:3000

## API Endpoints

The API documentation is available in the [docs/api-reference.md](./docs/api-reference.md) file.

## Deployment

Deployment instructions for Afrihost shared hosting are available in the [docs/deployment.md](./docs/deployment.md) file.

## Documentation

Comprehensive documentation is available in the [docs](./docs) directory:

- [Project Overview](./docs/project-overview.md)
- [API Reference](./docs/api-reference.md)
- [Database Schema](./docs/database-schema.md)
- [Deployment Guide](./docs/deployment.md)
- [Membership Tiers](./docs/membership-tiers.md)
