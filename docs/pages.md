# Pages Documentation

## Overview

The Mpumalanga Business Hub application consists of several key pages that provide the core functionality for both business owners and users. This document details each page, its purpose, features, and implementation details.

## Public Pages

### Home Page

**File:** `/client/src/pages/Home.jsx`

**URL:** `/`

**Purpose:** Serves as the landing page and primary entry point to the application.

**Features:**
- Hero section with call-to-action buttons
- Category showcases for different business sectors
- Featured businesses section (Gold tier members get priority placement)
- Value proposition highlights
- Quick registration promotion
- Advanced search integration

**Implementation Details:**
- Responsive design with mobile-first approach
- Dynamic content loading based on featured businesses
- Smooth scrolling to sections
- Optimized images for performance
- Integration with Fuse.js for search functionality

**Code Excerpt:**
```jsx
const Home = () => {
  const [categories, setCategories] = useState([
    { id: 1, name: 'Tourism', icon: '🏞️', count: 45 },
    { id: 2, name: 'Agriculture', icon: '🌽', count: 32 },
    { id: 3, name: 'Construction', icon: '🏗️', count: 28 },
    { id: 4, name: 'Events', icon: '🎉', count: 19 }
  ])
  
  // Component implementation...
}
```

### Business Directory Page

**File:** `/client/src/pages/BusinessDirectory.jsx`

**URL:** `/directory`

**Purpose:** Allows users to browse, search, and filter business listings.

**Features:**
- Advanced search functionality with Fuse.js
- Category and location filters
- Grid and list view options
- Sorting by various criteria (name, rating, etc.)
- Pagination for results
- Search history logging for analytics

**Implementation Details:**
- Client-side search with Fuse.js for quick results
- Debounced search input to prevent excessive re-renders
- Filter combination logic for advanced searching
- Responsive grid that adapts to screen size
- Integration with search service via API

**Code Excerpt:**
```jsx
// Search implementation with Fuse.js
const fuse = new Fuse(businesses, {
  keys: ['name', 'description', 'category'],
  threshold: 0.4,
})

const results = searchTerm ? 
  fuse.search(searchTerm).map(result => result.item) : 
  businesses
```

### Business Details Page

**File:** `/client/src/pages/BusinessDetails.jsx`

**URL:** `/business/:id`

**Purpose:** Displays detailed information about a specific business.

**Features:**
- Comprehensive business profile information
- Image gallery
- Contact information and map
- Product/service listings (if applicable, based on tier)
- Reviews and ratings
- Share functionality
- Tier badge indicating business package type

**Implementation Details:**
- Dynamic routing with React Router params
- Conditional rendering based on business tier (Silver/Gold)
- Integration with Google Maps for location display
- Lazy loading of images for performance
- Tab-based navigation for different content sections
- API error boundary implementation for robust error handling

**Code Excerpt:**
```jsx
const { id } = useParams()
const [business, setBusiness] = useState(null)
const [loading, setLoading] = useState(true)

useEffect(() => {
  const fetchBusinessDetails = async () => {
    try {
      const response = await fetch(`/api/businesses/${id}`)
      if (!response.ok) throw new Error('Failed to fetch business details')
      const data = await response.json()
      setBusiness(data)
    } catch (error) {
      console.error('Error fetching business details:', error)
    } finally {
      setLoading(false)
    }
  }
  
  fetchBusinessDetails()
}, [id])
```

### Login Page

**File:** `/client/src/pages/Login.jsx`

**URL:** `/login`

**Purpose:** Authenticates existing users.

**Features:**
- Email and password login form
- Form validation
- Error handling and feedback
- "Remember me" functionality
- Password reset link
- Redirect to registration for new users
- Rate limiting protection

**Implementation Details:**
- Controlled form components with validation
- JWT token handling for authentication
- Secure storage of authentication state
- Redirect to protected routes after successful login
- Session management with PHP sessions

**Code Excerpt:**
```jsx
const handleSubmit = async (e) => {
  e.preventDefault()
  if (!validateForm()) return
  
  try {
    setLoading(true)
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to login')
    }
    
    const data = await response.json()
    localStorage.setItem('token', data.token)
    setAuthState({ isAuthenticated: true, user: data.user })
    navigate('/dashboard')
  } catch (error) {
    setError(error.message)
  } finally {
    setLoading(false)
  }
}
```

### Register Page

**File:** `/client/src/pages/Register.jsx`

**URL:** `/register`

**Purpose:** Allows new businesses to create an account.

**Features:**
- Multi-step registration form
- Business information collection
- Package selection
- Terms and conditions acceptance
- Email verification
- Admin approval notification

**Implementation Details:**
- Step-based form with progress indicator
- Validation for each step before proceeding
- State persistence between steps
- Clear error feedback
- Integration with admin approval workflow

**Code Excerpt:**
```jsx
const Register = () => {
  const [step, setStep] = useState(1)
  const [formData, setFormData] = useState({
    businessName: '',
    email: '',
    password: '',
    confirmPassword: '',
    // Additional fields omitted for brevity
  })
  
  const handleSubmit = async () => {
    try {
      const response = await fetch('/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      })
      
      if (!response.ok) {
        throw new Error('Registration failed')
      }
      
      // Registration success
      navigate('/register/success')
    } catch (error) {
      setError(error.message)
    }
  }
  
  // Render different form steps based on 'step' state
}
```

### Search Results Page

**File:** `/client/src/pages/SearchResults.jsx`

**URL:** `/search`

**Purpose:** Displays search results based on user queries.

**Features:**
- Advanced filtering options
- Sorting capabilities
- Result highlighting
- Related search suggestions
- Search analytics tracking

**Implementation Details:**
- Integration with SearchService via API
- Client-side result filtering with Fuse.js
- Search query parameter handling
- Performance optimizations for large result sets

**Code Excerpt:**
```jsx
const SearchResults = () => {
  const [searchParams] = useSearchParams();
  const query = searchParams.get('q') || '';
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const fetchResults = async () => {
      try {
        setLoading(true);
        const response = await fetch(`/api/search?query=${encodeURIComponent(query)}`);
        const data = await response.json();
        setResults(data.results);
      } catch (error) {
        console.error('Search error:', error);
      } finally {
        setLoading(false);
      }
    };
    
    if (query) {
      fetchResults();
    } else {
      setResults([]);
      setLoading(false);
    }
  }, [query]);
  
  // Render search results
}
```

## Protected Pages

### Dashboard Page

**File:** `/client/src/pages/Dashboard.jsx`

**URL:** `/dashboard`

**Purpose:** Central hub for business owners to manage their business listings.

**Features:**
- Overview of business statistics
- Quick links to management sections
- Tier status and upgrade options
- Notifications panel
- Recently viewed analytics

**Implementation Details:**
- Protected route requiring authentication
- Dynamic content based on user's subscription tier
- Real-time statistics using the BusinessService
- Responsive dashboard layout

**Code Excerpt:**
```jsx
const Dashboard = () => {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)
  
  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await fetch('/api/businesses/dashboard', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        const data = await response.json()
        setStats(data)
      } catch (error) {
        console.error('Error fetching dashboard stats:', error)
      }
    }
    
    fetchStats()
  }, [])
  
  // Render dashboard components based on user tier
  return (
    <div className="dashboard">
      <DashboardNav />
      <div className="dashboard-content">
        {/* Dashboard components */}
      </div>
    </div>
  )
}
```

### Business Profile Page

**File:** `/client/src/pages/dashboard/BusinessProfile.jsx`

**URL:** `/dashboard/profile`

**Purpose:** Allows business owners to view and edit their business profile.

**Features:**
- Comprehensive business information form
- Image upload and management
- Contact information management
- Business hours settings
- Social media links
- Preview functionality

**Implementation Details:**
- Form validation for all fields
- Image optimization before upload
- Auto-save functionality
- Protected route requiring authentication

**Code Excerpt:**
```jsx
const BusinessProfile = () => {
  const { businessId } = useParams()
  const [business, setBusiness] = useState(null)
  const [loading, setLoading] = useState(true)
  const [success, setSuccess] = useState(false)
  
  const handleSubmit = async (formData) => {
    try {
      const response = await fetch(`/api/businesses/${businessId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(formData)
      })
      
      if (response.ok) {
        setSuccess(true)
        setTimeout(() => setSuccess(false), 3000)
      }
    } catch (error) {
      console.error('Error updating business:', error)
    }
  }
  
  // Component implementation
}
```

### Products Management Page

**File:** `/client/src/pages/dashboard/ManageProducts.jsx`

**URL:** `/dashboard/products`

**Purpose:** Allows business owners to manage their products or services.

**Features:**
- Product/service listing with CRUD operations
- Image upload for products
- Pricing information
- Categorization
- Tier-based limits on number of products
- Drag-and-drop reordering

**Implementation Details:**
- Protected route with tier validation
- Form validation for all fields
- Image optimization
- Implementation of tier limits through BusinessService

**Code Excerpt:**
```jsx
const ManageProducts = () => {
  const { businessId } = useParams()
  const [products, setProducts] = useState([])
  const [tierLimit, setTierLimit] = useState(0)
  const [count, setCount] = useState(0)
  
  useEffect(() => {
    // Fetch products and tier information
    const fetchData = async () => {
      try {
        const [productsRes, tierRes] = await Promise.all([
          fetch(`/api/businesses/${businessId}/products`),
          fetch(`/api/businesses/${businessId}/tier-info`)
        ])
        
        const productsData = await productsRes.json()
        const tierData = await tierRes.json()
        
        setProducts(productsData)
        setTierLimit(tierData.productLimit)
        setCount(productsData.length)
      } catch (error) {
        console.error('Error fetching data:', error)
      }
    }
    
    fetchData()
  }, [businessId])
  
  // CRUD operations for products
}
```

### Adverts Management Page

**File:** `/client/src/pages/dashboard/ManageAdverts.jsx`

**URL:** `/dashboard/adverts`

**Purpose:** Allows business owners to create and manage advertisements.

**Features:**
- Advert creation and management
- Image upload
- Duration settings
- Budget allocation
- Performance statistics
- Tier-based access control

**Implementation Details:**
- Protected route with tier validation
- Integration with advertisement service
- Date picker for scheduling
- Real-time preview

**Code Excerpt:**
```jsx
const ManageAdverts = () => {
  const { businessId } = useParams()
  const [adverts, setAdverts] = useState([])
  const [tierInfo, setTierInfo] = useState(null)
  
  // Check if user can create adverts based on tier
  const canCreateAdverts = tierInfo && ['Silver', 'Gold'].includes(tierInfo.package_type)
  
  // Component implementation
}
```

### Payment History Page

**File:** `/client/src/pages/dashboard/PaymentHistory.jsx`

**URL:** `/dashboard/payments`

**Purpose:** Displays transaction history for the business owner.

**Features:**
- List of all transactions
- Filtering by date and status
- Invoice download
- Payment method management
- Subscription details

**Implementation Details:**
- Protected route requiring authentication
- Integration with payment gateway service
- PDF generation for invoices
- Pagination for transaction list

**Code Excerpt:**
```jsx
const PaymentHistory = () => {
  const [transactions, setTransactions] = useState([])
  const [loading, setLoading] = useState(true)
  const [pagination, setPagination] = useState({
    currentPage: 1,
    totalPages: 1,
    totalItems: 0
  })
  
  // Fetch payment history
  // Handle pagination
  // Download invoice function
}
```

### Subscription Upgrade Page

**File:** `/client/src/pages/dashboard/UpgradePlan.jsx`

**URL:** `/dashboard/upgrade`

**Purpose:** Allows business owners to upgrade their subscription tier.

**Features:**
- Tier comparison table
- Pricing information
- Feature highlights
- Payment integration
- Proration calculation

**Implementation Details:**
- Protected route requiring authentication
- Integration with PayFast payment gateway
- Secure handling of payment information
- Clear presentation of tier benefits

**Code Excerpt:**
```jsx
const UpgradePlan = () => {
  const [currentTier, setCurrentTier] = useState(null)
  const [tiers, setTiers] = useState([])
  const [selectedTier, setSelectedTier] = useState(null)
  
  const handleSelectTier = (tier) => {
    setSelectedTier(tier)
  }
  
  const handleUpgrade = async () => {
    try {
      const response = await fetch('/api/payments/create-upgrade', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          tierId: selectedTier.id
        })
      })
      
      const data = await response.json()
      window.location.href = data.paymentUrl
    } catch (error) {
      console.error('Error initiating upgrade:', error)
    }
  }
  
  // Component implementation
}
```

## Admin Pages

### Admin Dashboard

**File:** `/client/src/pages/admin/AdminDashboard.jsx`

**URL:** `/admin`

**Purpose:** Provides system administrators with an overview of the platform.

**Features:**
- System statistics and metrics
- Quick access to administrative functions
- Recent activity log
- Performance graphs
- Registration trends

**Implementation Details:**
- Protected route requiring admin role
- Data visualization with charts
- Integration with admin service layer
- Real-time data updates

**Code Excerpt:**
```jsx
const AdminDashboard = () => {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  
  useEffect(() => {
    const fetchAdminStats = async () => {
      try {
        const response = await fetch('/api/admin/stats', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (!response.ok) throw new Error('Failed to fetch admin statistics')
        
        const data = await response.json()
        setStats(data)
      } catch (error) {
        console.error('Error fetching admin stats:', error)
      } finally {
        setLoading(false)
      }
    }
    
    fetchAdminStats()
  }, [])
  
  // Render admin dashboard components
}
```

### Business Approval Page

**File:** `/client/src/pages/admin/BusinessApprovals.jsx`

**URL:** `/admin/approvals`

**Purpose:** Allows administrators to review and approve/reject business registrations.

**Features:**
- List of pending business approvals
- Detailed view of business information
- Approval/rejection functionality
- Feedback provision for rejections
- Bulk actions for efficiency

**Implementation Details:**
- Protected route requiring admin role
- Integration with admin approval workflow
- Email notifications for business owners
- Status tracking and history

**Code Excerpt:**
```jsx
const BusinessApprovals = () => {
  const [pendingBusinesses, setPendingBusinesses] = useState([])
  const [loading, setLoading] = useState(true)
  const [selectedBusiness, setSelectedBusiness] = useState(null)
  
  const handleApprove = async (businessId, feedback = '') => {
    try {
      const response = await fetch(`/api/admin/businesses/${businessId}/approve`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ feedback })
      })
      
      if (response.ok) {
        // Update the list of pending businesses
        setPendingBusinesses(prev => prev.filter(business => business.id !== businessId))
      }
    } catch (error) {
      console.error('Error approving business:', error)
    }
  }
  
  const handleReject = async (businessId, feedback) => {
    // Similar to approve but with reject endpoint
  }
  
  // Component implementation
}
```

### Search Analytics Page

**File:** `/client/src/pages/admin/SearchAnalytics.jsx`

**URL:** `/admin/search-analytics`

**Purpose:** Provides insights into user search behavior and patterns.

**Features:**
- Search query analytics
- Popular search terms
- Failed search analysis
- Search trend graphs
- Export functionality

**Implementation Details:**
- Protected route requiring admin role
- Data visualization with charts
- Date range filtering
- Integration with search logs database

**Code Excerpt:**
```jsx
const SearchAnalytics = () => {
  const [searchData, setSearchData] = useState(null)
  const [dateRange, setDateRange] = useState({ start: null, end: null })
  const [loading, setLoading] = useState(true)
  
  const fetchSearchAnalytics = async () => {
    try {
      const queryParams = new URLSearchParams()
      if (dateRange.start) queryParams.append('start_date', dateRange.start)
      if (dateRange.end) queryParams.append('end_date', dateRange.end)
      
      const response = await fetch(`/api/admin/search-analytics?${queryParams}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      const data = await response.json()
      setSearchData(data)
    } catch (error) {
      console.error('Error fetching search analytics:', error)
    } finally {
      setLoading(false)
    }
  }
  
  // Component implementation
}
```

## Utility Pages

### Error Page

**File:** `/client/src/pages/Error.jsx`

**URL:** Various error routes

**Purpose:** Displays user-friendly error messages.

**Features:**
- Custom error messages based on error code
- Helpful troubleshooting tips
- Contact support option
- Return to safety links

**Implementation Details:**
- Integration with API error boundary
- Comprehensive error categorization
- Analytics tracking for errors
- Error reporting functionality

**Code Excerpt:**
```jsx
const Error = () => {
  const { error } = useRouteError()
  const navigate = useNavigate()
  
  // Determine error message and actions based on error code
  let title = 'An error occurred'
  let message = 'Something went wrong. Please try again later.'
  
  if (error?.status === 404) {
    title = 'Page not found'
    message = 'The page you are looking for does not exist.'
  } else if (error?.status === 403) {
    title = 'Access denied'
    message = 'You do not have permission to access this resource.'
  }
  
  return (
    <div className="error-page">
      <h1>{title}</h1>
      <p>{message}</p>
      <button onClick={() => navigate(-1)}>Go Back</button>
      <button onClick={() => navigate('/')}>Return to Home</button>
    </div>
  )
}
```

### NotFound Page

**File:** `/client/src/pages/NotFound.jsx`

**URL:** `*` (Catch-all route)

**Purpose:** Custom 404 page for non-existent routes.

**Features:**
- User-friendly 404 message
- Search functionality
- Suggested popular pages
- Return to home option

**Implementation Details:**
- Catch-all route in React Router
- Analytics tracking for 404s
- Helpful navigation suggestions

**Code Excerpt:**
```jsx
const NotFound = () => {
  const navigate = useNavigate()
  
  useEffect(() => {
    // Log 404 error for analytics
    // This could call an API endpoint to track 404s
  }, [])
  
  return (
    <div className="not-found">
      <h1>404 - Page Not Found</h1>
      <p>The page you are looking for doesn't exist or has been moved.</p>
      <button onClick={() => navigate('/')}>Return to Home</button>
      
      <div className="popular-pages">
        <h2>Popular Pages</h2>
        <ul>
          <li><Link to="/directory">Business Directory</Link></li>
          <li><Link to="/register">Register Your Business</Link></li>
          <li><Link to="/contact">Contact Us</Link></li>
        </ul>
      </div>
    </div>
  )
}
```

## Code Splitting and Lazy Loading

The application uses React's lazy loading and Suspense to optimize performance:

```jsx
// In the main routing file
import { lazy, Suspense } from 'react'
import Loading from './components/ui/Loading'

// Lazy load page components
const Home = lazy(() => import('./pages/Home'))
const BusinessDirectory = lazy(() => import('./pages/BusinessDirectory'))
const BusinessDetails = lazy(() => import('./pages/BusinessDetails'))
const Login = lazy(() => import('./pages/Login'))
const Register = lazy(() => import('./pages/Register'))
const Dashboard = lazy(() => import('./pages/Dashboard'))
const AdminDashboard = lazy(() => import('./pages/admin/AdminDashboard'))
const NotFound = lazy(() => import('./pages/NotFound'))

// Wrapped with Suspense
const AppRoutes = () => (
  <Suspense fallback={<Loading />}>
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/directory" element={<BusinessDirectory />} />
      <Route path="/business/:id" element={<BusinessDetails />} />
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      
      {/* Protected routes */}
      <Route element={<ProtectedRoute />}>
        <Route path="/dashboard/*" element={<Dashboard />} />
      </Route>
      
      {/* Admin routes */}
      <Route element={<AdminRoute />}>
        <Route path="/admin/*" element={<AdminDashboard />} />
      </Route>
      
      <Route path="*" element={<NotFound />} />
    </Routes>
  </Suspense>
)
