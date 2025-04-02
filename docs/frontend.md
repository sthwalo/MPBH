# Frontend Documentation

## Overview

The Mpumalanga Business Hub frontend is built using React with Vite as the build tool. The UI is designed to be responsive, intuitive, and focused on providing an optimal user experience for both businesses and customers.

## Technology Stack

- **Framework**: React 18.x
- **Build Tool**: Vite
- **Routing**: React Router 6
- **Styling**: CSS with Tailwind utility classes
- **State Management**: React Context API
- **Search Functionality**: Fuse.js for client-side searching
- **Form Handling**: Custom form hooks
- **Admin Panel**: Custom admin dashboard for moderators
- **Error Handling**: API error boundaries for robust error handling
- **Tier-Based UI Rendering**: Conditional rendering based on subscription tier

## Project Structure

The frontend code is organized in a modular structure to maintain separation of concerns and improve maintainability:

```
client/
├── public/
│   ├── favicon.ico
│   └── index.html
├── src/
│   ├── assets/         # Static assets like images and icons
│   ├── components/      # Reusable UI components
│   │   ├── dashboard/   # Dashboard-specific components
│   │   ├── admin/       # Admin components
│   │   │   └── AdminPanel.jsx        # Admin dashboard panel
│   │   └── ui/          # Generic UI components
│   ├── context/        # State management using Context API
│   ├── hooks/          # Custom React hooks
│   ├── pages/          # Page components
│   ├── utils/          # Utility functions
│   ├── errors/         # Error handling components
│   │   └── ApiErrorBoundary.jsx     # API error boundary
│   ├── App.jsx         # Main application component
│   ├── App.css         # Global styles
│   ├── index.css       # Tailwind imports and base styles
│   └── main.jsx        # Entry point
├── package.json        # Dependencies and scripts
└── vite.config.js      # Vite configuration
```

## Key Components

### Core Components

- **Header**: Navigation bar with links to main sections and authentication state
- **Footer**: Footer with links to important pages and contact information
- **BusinessCard**: Card component for displaying business listings in a grid or list view
- **SearchBar**: Input component for searching businesses with filters
- **CategoryFilter**: Dropdown filters for business categories
- **RatingStars**: Component for displaying and capturing ratings

### Dashboard Components

- **DashboardHome**: Overview of business statistics and quick access links
- **BusinessProfile**: Form for updating business information
- **ManageProducts**: Interface for managing products and services
- **AdvertsManagement**: Tools for creating and managing promotional adverts
- **PaymentHistory**: Display of subscription payment history
- **UpgradePlan**: Interface for upgrading membership tier

### Admin Components

- **AdminPanel**: Custom admin dashboard for moderators
- **AdminBusinessDetails**: Detailed view of a specific business for moderators

## Pages

### Public Pages

- **Home**: Landing page with category showcases and promotional content
- **BusinessDirectory**: Searchable and filterable business listings
- **BusinessDetails**: Detailed view of a specific business
- **Login**: Authentication page for existing users
- **Register**: Multi-step registration form for new businesses
- **NotFound**: 404 error page for invalid routes

### Authenticated Pages

- **Dashboard**: Main entry point for business management

## State Management

State management is implemented using React's Context API, providing a lightweight solution without introducing external dependencies:

- **AuthContext**: Manages user authentication state
- **BusinessContext**: Maintains business data across components
- **UIContext**: Handles UI-specific state like theme or sidebar visibility

## Routing

React Router is used for client-side routing:

```jsx
<Routes>
  <Route path="/" element={<Home />} />
  <Route path="/directory" element={<BusinessDirectory />} />
  <Route path="/business/:id" element={<BusinessDetails />} />
  <Route path="/login" element={<Login />} />
  <Route path="/register" element={<Register />} />
  <Route path="/dashboard/*" element={<Dashboard />} />
  <Route path="/admin/*" element={<AdminPanel />} />
  <Route path="*" element={<NotFound />} />
</Routes>
```

Dashboard routing is implemented as nested routes:

```jsx
<Routes>
  <Route path="/" element={<DashboardHome businessData={businessData} />} />
  <Route path="/profile" element={<BusinessProfile businessData={businessData} />} />
  <Route path="/products" element={<ManageProducts businessData={businessData} />} />
  <Route path="/adverts" element={<AdvertsManagement businessData={businessData} />} />
  <Route path="/payments" element={<PaymentHistory businessData={businessData} />} />
  <Route path="/upgrade" element={<UpgradePlan businessData={businessData} />} />
</Routes>
```

## API Integration

API calls are managed using the Fetch API, with custom hooks to handle loading states, errors, and API responses:

```jsx
const fetchBusinesses = async () => {
  try {
    setLoading(true);
    const response = await fetch('/api/businesses');
    if (!response.ok) throw new Error('Failed to fetch businesses');
    const data = await response.json();
    setBusinesses(data);
  } catch (error) {
    console.error('Error fetching businesses:', error);
    setError(error.message);
  } finally {
    setLoading(false);
  }
};
```

## Authentication Flow

User authentication is implemented using a token-based approach:

1. User submits login credentials
2. Backend validates credentials and returns a JWT token
3. Frontend stores the token in localStorage
4. Token is included in Authorization headers for authenticated API requests
5. Protected routes check for valid token before rendering

## Responsive Design

The UI is designed to be fully responsive across different device sizes using Tailwind's responsive utility classes:

- Mobile-first approach with progressively enhanced layouts for larger screens
- Responsive navigation that collapses to a mobile menu on smaller screens
- Flexible grid layouts that adapt to different screen sizes
- Properly sized touch targets for mobile users

## Search Functionality

Client-side search is implemented using Fuse.js for fast and flexible searching capabilities:

```jsx
const fuse = new Fuse(businesses, {
  keys: ['name', 'description', 'category'],
  threshold: 0.4,
});

const searchResults = searchTerm ? 
  fuse.search(searchTerm).map(result => result.item) : 
  businesses;
```

## Form Handling

Forms use controlled components with validation for user input:

```jsx
const [formData, setFormData] = useState({
  email: '',
  password: ''
});

const [errors, setErrors] = useState({});

const handleChange = (e) => {
  const { name, value } = e.target;
  setFormData(prevData => ({
    ...prevData,
    [name]: value
  }));
};

const validateForm = () => {
  const newErrors = {};
  if (!formData.email) newErrors.email = 'Email is required';
  if (!formData.password) newErrors.password = 'Password is required';
  setErrors(newErrors);
  return Object.keys(newErrors).length === 0;
};
```

## Performance Considerations

- **Code Splitting**: Implemented at the route level using React.lazy and Suspense
- **Lazy Loading**: Images are lazy-loaded to improve initial page load time
- **Memoization**: React.memo is used for expensive renders
- **Throttling/Debouncing**: Applied to search inputs and resize handlers

## Tier-Based UI Rendering

The application conditionally renders UI elements based on the business's subscription tier:

```jsx
const BusinessFeatures = ({ business }) => {
  const { package_type } = business;
  
  const canAddProducts = ['Silver', 'Gold'].includes(package_type);
  const canCreateAdverts = ['Silver', 'Gold'].includes(package_type);
  const hasFeaturedPlacement = package_type === 'Gold';
  
  return (
    <div className="business-features">
      {canAddProducts && (
        <div className="feature">
          <h3>Product Management</h3>
          <p>Add and manage your products or services</p>
          <Link to="/dashboard/products">Manage Products</Link>
        </div>
      )}
      
      {canCreateAdverts && (
        <div className="feature">
          <h3>Advertising</h3>
          <p>Create promotional advertisements</p>
          <Link to="/dashboard/adverts">Manage Adverts</Link>
        </div>
      )}
      
      {hasFeaturedPlacement && (
        <div className="feature premium">
          <h3>Featured Placement</h3>
          <p>Your business is featured on the homepage</p>
        </div>
      )}
    </div>
  );
};
```

## Error Handling

The application implements robust error handling with API error boundaries:

```jsx
// Example usage of ApiErrorBoundary
import ApiErrorBoundary, { DefaultErrorMessage } from '../errors/ApiErrorBoundary';

const BusinessPage = () => {
  return (
    <ApiErrorBoundary
      fallback={(error, retry) => (
        <div className="error-container">
          <h2>Error loading business data</h2>
          <p>{error.message}</p>
          <button onClick={retry}>Try Again</button>
        </div>
      )}
    >
      <BusinessContent />
    </ApiErrorBoundary>
  );
};
```

## Responsive Design

The application is fully responsive, adapting to various screen sizes and devices using media queries and flexible layouts:

```css
/* Example responsive styling */
.business-card {
  width: 100%;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .business-card {
    width: calc(50% - 1rem);
    margin-right: 1rem;
  }
}

@media (min-width: 1024px) {
  .business-card {
    width: calc(33.333% - 1rem);
  }
}
```

## Performance Optimizations

The frontend implements several optimizations for better performance:

1. **Code Splitting**: Component-based code splitting with React.lazy
2. **Lazy Loading**: Images and components are loaded on demand
3. **Debounced Search**: Search queries are debounced to reduce API calls
4. **Pagination**: Data is paginated to reduce initial load times
5. **Memoization**: React.memo and useMemo for expensive computations

## API Integration

API calls are centralized using Axios with a custom instance:

```jsx
// API client setup
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Add authorization token to requests
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle response errors
apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Handle authentication errors
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;
