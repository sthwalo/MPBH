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
│   │   └── ui/          # Generic UI components
│   ├── context/        # State management using Context API
│   ├── hooks/          # Custom React hooks
│   ├── pages/          # Page components
│   ├── utils/          # Utility functions
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
