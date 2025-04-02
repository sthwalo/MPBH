# Components Documentation

## Overview

The Mpumalanga Business Hub frontend is built using a component-based architecture to promote reusability, maintainability, and a consistent user experience. This document provides detailed information about each component, its purpose, props, and implementation.

## Core Components

### Header

**File:** `/client/src/components/Header.jsx`

**Purpose:** Main navigation component that appears on all pages.

**Features:**
- Responsive navigation with mobile menu toggle
- Dynamic links based on authentication state
- Logo and branding elements

**Props:** None (uses Context API for authentication state)

**Implementation Details:**
- Uses React Router's `Link` and `NavLink` for navigation
- Implements a responsive design that collapses to a hamburger menu on mobile
- Conditionally renders login/register or dashboard links based on authentication

### Footer

**File:** `/client/src/components/Footer.jsx`

**Purpose:** Footer component with links and information.

**Features:**
- Important links organized by category
- Contact information and social media links
- Copyright information

**Props:** None

**Implementation Details:**
- Simple grid layout with responsive columns
- Includes links to key areas of the application

### BusinessCard

**File:** `/client/src/components/BusinessCard.jsx`

**Purpose:** Card component for displaying business information in the directory.

**Features:**
- Visual representation of business listings
- Displays key business information
- Link to business details page

**Props:**
- `business`: Object with business details
- `featured`: Boolean to indicate if the card should be highlighted

**Implementation Details:**
- Responsive card design with hover effects
- Displays business name, category, rating, and brief description
- Shows badge for premium listings (Gold package)

### AdminPanel

**File:** `/client/src/components/admin/AdminPanel.jsx`

**Purpose:** Main component for the admin dashboard.

**Features:**
- System-wide statistics
- Links to admin sections

**Props:** None

**Implementation Details:**
- Fetches system statistics on mount
- Conditionally renders admin sections based on user role

### ApiErrorBoundary

**File:** `/client/src/errors/ApiErrorBoundary.jsx`

**Purpose:** Error boundary component for handling API errors gracefully.

**Features:**
- Catches and displays API errors
- Provides a fallback UI for error states

**Props:**
- `children`: Child components to wrap
- `fallback`: Custom fallback UI renderer

**Implementation Details:**
- Uses React's Error Boundary API to catch errors
- Renders a default error message or custom fallback UI

### BusinessSearch

**File:** `/client/src/components/BusinessSearch.jsx`

**Purpose:** Advanced search component using Fuse.js for fuzzy search functionality.

**Features:**
- Search input with autocomplete suggestions
- Displays search results with pagination

**Props:**
- `businesses`: List of businesses to search
- `onResultsChange`: Callback for search results

**Implementation Details:**
- Uses Fuse.js for fuzzy search functionality
- Implements pagination for search results

## Dashboard Components

### DashboardHome

**File:** `/client/src/components/dashboard/DashboardHome.jsx`

**Purpose:** Main dashboard overview for business owners.

**Features:**
- Statistics overview (views, inquiries, reviews)
- Quick action buttons
- Recent activity summary

**Props:**
- `businessData`: Object containing business information and statistics

**Implementation Details:**
- Grid layout with statistics cards
- Responsive design that works on mobile and desktop
- Uses SVG icons for visual appeal

### BusinessProfile

**File:** `/client/src/components/dashboard/BusinessProfile.jsx`

**Purpose:** Form for editing business profile information.

**Features:**
- Form for updating business details
- Image upload for logo and cover
- Input validation

**Props:**
- `businessData`: Object containing current business information

**Implementation Details:**
- Comprehensive form with validation
- Handles image uploads and previews
- Success and error state management

### ManageProducts

**File:** `/client/src/components/dashboard/ManageProducts.jsx`

**Purpose:** Interface for adding and managing products/services.

**Features:**
- Product listing table
- Add/edit product form
- Tier-based access control

**Props:**
- `businessData`: Object containing business information and products

**Implementation Details:**
- CRUD operations for products
- Form validation for product details
- Conditional rendering based on membership tier

### AdvertsManagement

**File:** `/client/src/components/dashboard/AdvertsManagement.jsx`

**Purpose:** Component for creating and managing promotional adverts.

**Features:**
- Create new adverts with start/end dates
- View active and scheduled adverts
- Limited by membership tier

**Props:**
- `businessData`: Object containing business information and advert allocations

**Implementation Details:**
- Form for creating new adverts with date validation
- Display of remaining advert allocations
- Listing of current adverts with status indicators
- Tier-based restrictions (only Silver and Gold)

### PaymentHistory

**File:** `/client/src/components/dashboard/PaymentHistory.jsx`

**Purpose:** Display payment and subscription history.

**Features:**
- Current subscription status
- Payment transaction history
- Invoice downloads

**Props:**
- `businessData`: Object containing subscription and payment information

**Implementation Details:**
- Tabular display of payment transactions
- Status badges for payment state
- Responsive design for all screen sizes

### UpgradePlan

**File:** `/client/src/components/dashboard/UpgradePlan.jsx`

**Purpose:** Interface for upgrading membership tier.

**Features:**
- Package comparison table
- Monthly/annual billing toggle
- Payment processing flow

**Props:**
- `businessData`: Object containing current subscription details

**Implementation Details:**
- Visual comparison of features across tiers
- Price calculation for different billing periods
- Integration with payment processing
- Upgrade/downgrade confirmation flows

## Form Components

### InputField

**File:** `/client/src/components/ui/InputField.jsx`

**Purpose:** Reusable input field component.

**Features:**
- Label, input, and error message
- Various input types support

**Props:**
- `id`: String identifier
- `label`: String for the input label
- `type`: String input type (text, email, password, etc.)
- `value`: Current input value
- `onChange`: Function to handle changes
- `error`: Error message string
- `placeholder`: Placeholder text
- `required`: Boolean to indicate if field is required

**Implementation Details:**
- Consistent styling across forms
- Error state visual feedback
- Accessibility attributes

### SelectField

**File:** `/client/src/components/ui/SelectField.jsx`

**Purpose:** Reusable dropdown select component.

**Features:**
- Label, select dropdown, and error message
- Option list from props

**Props:**
- `id`: String identifier
- `label`: String for the select label
- `value`: Current selected value
- `onChange`: Function to handle changes
- `options`: Array of option objects
- `error`: Error message string
- `required`: Boolean to indicate if field is required

**Implementation Details:**
- Consistent styling with other form elements
- Option rendering from array
- Error state handling

## Utility Components

### LoadingSpinner

**File:** `/client/src/components/ui/LoadingSpinner.jsx`

**Purpose:** Visual loading indicator.

**Features:**
- Animated spinner
- Optional loading text

**Props:**
- `size`: String size (small, medium, large)
- `text`: Optional loading text

**Implementation Details:**
- CSS animations for spinner
- Accessible with aria attributes

### Alert

**File:** `/client/src/components/ui/Alert.jsx`

**Purpose:** Display feedback messages to users.

**Features:**
- Different alert types (success, error, info, warning)
- Dismissible option

**Props:**
- `type`: String alert type
- `message`: String message to display
- `dismissible`: Boolean to allow closing
- `onDismiss`: Function to call when dismissed

**Implementation Details:**
- Styled differently based on alert type
- Dismiss button when applicable
- Auto-dismiss timer option

### LazyLoadImage

**File:** `/client/src/components/ui/LazyLoadImage.jsx`

**Purpose:** Performance-optimized image component that only loads images when they enter the viewport.

**Features:**
- Lazy loading of images for improved page performance
- Placeholder images while loading
- Smooth loading transitions
- Error handling for failed image loads

**Props:**
- `src`: String (required) - The source URL of the image
- `alt`: String (required) - Alt text for the image
- `placeholderSrc`: String - URL for placeholder image while loading
- `width`: String/Number - Width of the image
- `height`: String/Number - Height of the image
- `className`: String - CSS classes to apply
- `effect`: String - Loading effect ("blur", "fade", "none")

**Implementation Details:**
- Uses Intersection Observer API to detect when images enter the viewport
- Only loads full images when they are about to become visible
- Smooth transition effects when images load
- Error handling that falls back to placeholder if image fails to load

### TierBadge

**File:** `/client/src/components/ui/TierBadge.jsx`

**Purpose:** Visual representation of business membership tiers.

**Features:**
- Color-coded badge showing membership tier
- Tooltip with tier benefits information

**Props:**
- `tier`: String (required) - The tier name (Basic, Bronze, Silver, Gold)
- `size`: String - Size of the badge (small, medium, large)
- `showTooltip`: Boolean - Whether to display tooltip with benefits

**Implementation Details:**
- Uses the tierConfig.js file for tier colors and descriptions
- Displays tooltip on hover with tier benefits
- Consistent styling across the application

## Admin Components

### ApprovalQueue

**File:** `/client/src/components/admin/ApprovalQueue.jsx`

**Purpose:** Interface for reviewing and approving pending business listings.

**Features:**
- List of pending business listings awaiting approval
- Detailed view of business information
- Filtering by category and district
- Approval/rejection workflow with feedback

**Props:** None (fetches data directly from API)

**Implementation Details:**
- Grid layout of business cards with approval/rejection buttons
- Modal for rejection feedback to send to business owners
- Sorting options (oldest/newest first)
- Loading and error states
- Uses LazyLoadImage for business logos

## Search Components

### BusinessSearch

**File:** `/client/src/components/search/BusinessSearch.jsx`

**Purpose:** Advanced search component using Fuse.js for fuzzy search functionality.

**Features:**
- Client-side fuzzy searching with Fuse.js
- Filtering by category, district, and package type
- Instant results as user types
- Search analytics logging

**Props:** None (fetches data directly from API)

**Implementation Details:**
- Uses Fuse.js for fuzzy matching and relevance scoring
- Debounced search input to prevent excessive API calls
- Combines search query with filters
- Logs searches for analytics purposes
- Uses LazyLoadImage for business logos in results

## Server Middleware

### CsrfMiddleware

**File:** `/server/src/middleware/CsrfMiddleware.php`

**Purpose:** Protects against Cross-Site Request Forgery (CSRF) attacks.

**Features:**
- Generates and validates CSRF tokens
- Enforces token validation for state-changing requests
- Token regeneration for GET requests

**Implementation Details:**
- Implements PSR-15 middleware interface
- Validates tokens from X-CSRF-Token header or csrf_token field
- Uses PHP's hash_equals for secure comparison
- Session-based token storage

### RateLimitMiddleware

**File:** `/server/src/middleware/RateLimitMiddleware.php`

**Purpose:** Prevents abuse by limiting the number of requests from a single IP.

**Features:**
- Configurable request limits per time window
- Response headers with rate limit information
- IP-based tracking with proxy awareness

**Implementation Details:**
- Uses Redis for rate limit tracking
- Falls back gracefully if Redis is unavailable
- Sets X-RateLimit headers on responses
- Handles various proxy headers for accurate client IP detection

## Cache Components

### RedisCache

**File:** `/server/src/cache/RedisCache.php`

**Purpose:** Caching utility to improve API performance.

**Features:**
- Memory-based caching of expensive operations
- Configurable TTL (time-to-live) for cached items
- Graceful fallback if Redis is unavailable

**Implementation Details:**
- Wraps the Redis PHP extension
- Provides simple remember/forget/flush methods
- JSON serialization for complex data structures
- Environment-based configuration

## Component Integration

Components are integrated into the application using a hierarchical structure:

1. **Page Components**: Top-level components that represent entire pages
2. **Layout Components**: Components that define the structure of pages (Header, Footer)
3. **Feature Components**: Components that implement specific features (BusinessCard, etc.)
4. **UI Components**: Low-level, reusable UI elements (InputField, Alert, etc.)

This hierarchy ensures a consistent user experience while maintaining component reusability and separation of concerns.

## Best Practices

The following best practices are employed in component development:

1. **Prop Validation**: Props are validated using PropTypes
2. **Default Props**: Default values are provided where appropriate
3. **Single Responsibility**: Each component has a single, well-defined responsibility
4. **Controlled Components**: Form inputs are implemented as controlled components
5. **Error Handling**: Components include appropriate error handling and feedback
6. **Accessibility**: ARIA attributes and semantic HTML for accessibility
7. **Performance**: Memoization for expensive renders and optimized re-renders
8. **Testing**: Components are designed to be testable with Jest and React Testing Library
