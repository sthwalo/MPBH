# Mpumalanga Business Hub

A directory platform connecting local businesses (Tourism, Agriculture, Construction, Events) in Mpumalanga with enhanced membership tiers and discovery features.

## Project Overview

### Objective
To build a comprehensive business directory platform for the Mpumalanga region, supporting businesses across Tourism, Agriculture, Construction, and Events sectors with tiered membership options.

### Key Metrics
- **Target Users**: 10,000+ local businesses  
- **Revenue Model**: Tiered subscriptions (R200-R1000/month)  
- **Tech Stack**: React + PHP + MySQL on Afrihost Shared Hosting

## Technology Stack

| **Layer**       | **Technology**          | **Version** | **Purpose**  |
|-----------------|-------------------------|-------------|------------|
| **Frontend**    | React + Vite            | 18.x        | Dynamic UI  |  
| **State**       | Context API             | -           | Client state |  
| **Backend**     | PHP Slim Framework      | 8.1+        | REST API    |  
| **Database**    | MySQL                   | 8.0         | Data storage |  
| **Search**      | Fuse.js                 | 6.6         | Client-side search |  
| **Payments**    | PayFast                 | -           | Subscription billing |  
| **Maps**        | Google Maps API         | -           | Location services |  

## Repository Structure

```
mpbusinesshub/
├── client/               # React Frontend
│   ├── src/
│   │   ├── modules/
│   │   │   ├── search/
│   │   │   ├── auth/
│   │   │   ├── dashboard/
│   │   │   └── payments/
├── server/               # PHP Backend
│   ├── api/
│   │   ├── businesses/
│   │   ├── auth/
│   │   └── payments/
│   └── config/
├── docs/                 # Documentation
└── scripts/              # Deployment
```

## Membership Tiers

| Feature               | Basic | Bronze | Silver | Gold |
|-----------------------|-------|--------|--------|------|
| **Listing Visibility** | ✅    | ✅     | ✅     | ✅   |
| **Contact Links**     | ❌    | ✅     | ✅     | ✅   |
| **E-Commerce**        | ❌    | ❌     | ✅     | ✅   |
| **Monthly Adverts**   | 0     | 0      | 1      | 4    |

## Getting Started

### Prerequisites
- Node.js (>= 14.x)
- PHP (>= 8.1)
- MySQL (>= 8.0)

### Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/mpbusinesshub.git
```

2. Install frontend dependencies
```bash
cd client
npm install
```

3. Install backend dependencies
```bash
cd server
composer install
```

4. Set up the database
```bash
mysql -u username -p database_name < schema.sql
```

## Development Roadmap

### Milestone 1: Core Directory (3 Weeks)
- Database Schema Design
- CRUD API for Businesses
- Search UI Implementation

### Milestone 2: Membership System (2 Weeks)
- PayFast Integration
- Tier Comparison UI

### Milestone 3: Admin Tools (1 Week)
- Admin Dashboard
- Analytics Implementation

## License

This project is licensed under the terms specified in the LICENSE file.
