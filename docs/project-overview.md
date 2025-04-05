# Mpumalanga Business Hub - Project Overview

The Mpumalanga Business Hub is a comprehensive web platform designed to connect local businesses with customers and promote economic growth in the Mpumalanga province. The platform offers tiered membership packages (Basic, Silver, Gold) with varying features and benefits.

## Current Implementation Status

As of April 2025, the following core functionalities have been implemented:

- **User Registration & Authentication**: Complete registration flow with JWT-based authentication
- **Business Profile Management**: Creation and management of business profiles with detailed information
- **Business Directory**: Searchable and filterable business directory with pagination
- **Dashboard Interface**: Business owners can view statistics and manage their listings

## Technology Stack

### Frontend
- **Framework**: React with Vite for fast development
- **Styling**: Tailwind CSS for responsive design
- **State Management**: React Context API
- **Routing**: React Router v6

### Backend
- **Language**: PHP 8.x
- **Database**: PostgreSQL hosted on Afrihost
- **Authentication**: JWT (JSON Web Tokens)
- **API**: RESTful API endpoints

### Deployment
- **Hosting**: Afrihost Shared Hosting environment
- **Domain**: TBD

## Key Features

1. **Business Listings**: Searchable directory of businesses in Mpumalanga
2. **Tiered Memberships**: Basic, Silver, and Gold packages with increasing benefits
3. **Business Dashboard**: Analytics, profile management, and business tools
4. **Admin Interface**: For content moderation and user management

## Project Architecture

The application follows a client-server architecture with a clear separation between frontend and backend:

### Frontend Structure
- **Components**: Reusable UI elements
- **Pages**: Full page views that combine components
- **Utils**: Helper functions and utilities
- **Config**: Configuration settings

### Backend Structure
- **Models**: Database entity representations
- **Controllers**: Request handlers
- **Services**: Business logic
- **Middleware**: Request processing layers
- **Config**: System configuration
