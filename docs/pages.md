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
- Featured businesses section
- Value proposition highlights
- Quick registration promotion

**Implementation Details:**
- Responsive design with mobile-first approach
- Dynamic content loading based on featured businesses
- Smooth scrolling to sections
- Optimized images for performance

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
- Search functionality with Fuse.js
- Category and location filters
- Grid and list view options
- Sorting by various criteria (name, rating, etc.)
- Pagination for results

**Implementation Details:**
- Client-side search with Fuse.js for quick results
- Debounced search input to prevent excessive re-renders
- Filter combination logic for advanced searching
- Responsive grid that adapts to screen size

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
- Product/service listings (if applicable)
- Reviews and ratings
- Share functionality

**Implementation Details:**
- Dynamic routing with React Router params
- Conditional rendering based on business tier
- Integration with Google Maps for location display
- Lazy loading of images for performance
- Tab-based navigation for different content sections

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

**Implementation Details:**
- Controlled form components with validation
- JWT token handling for authentication
- Secure storage of authentication state
- Redirect to protected routes after successful login

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

**Implementation Details:**
- Step-based form with progress indicator
- Validation for each step before proceeding
- State persistence between steps
- Clear error feedback

**Code Excerpt:**
```jsx
const [step, setStep] = useState(1)
const [formData, setFormData] = useState({
  // User account info
  email: '',
  password: '',
  confirmPassword: '',
  
  // Business info
  businessName: '',
  category: '',
  description: '',
  address: '',
  phone: '',
  
  // Package selection
  packageType: 'Basic',
  
  // Terms
  acceptTerms: false
})

const nextStep = () => {
  if (step === 1 && validateStep1()) {
    setStep(2)
  } else if (step === 2 && validateStep2()) {
    setStep(3)
  } else if (step === 3 && validateStep3()) {
    setStep(4)
  }
}
```

### NotFound Page

**File:** `/client/src/pages/NotFound.jsx`

**URL:** `*` (catch-all for undefined routes)

**Purpose:** Displays a 404 error page for invalid routes.

**Features:**
- Friendly error message
- Link back to home page
- Search functionality (optional)

**Implementation Details:**
- Simple, clean design
- Clear navigation options
- Custom illustration

## Authenticated Pages

### Dashboard Page

**File:** `/client/src/pages/Dashboard.jsx`

**URL:** `/dashboard/*`

**Purpose:** Main interface for business owners to manage their listings and accounts.

**Features:**
- Sidebar navigation to different dashboard sections
- Authentication state verification
- Nested routing for dashboard components

**Implementation Details:**
- Protected route that requires authentication
- Nested routing with React Router
- Responsive layout with collapsible sidebar for mobile
- Business data fetching and state management

**Code Excerpt:**
```jsx
const Dashboard = () => {
  const [businessData, setBusinessData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const navigate = useNavigate()
  const location = useLocation()
  
  // Authentication check
  useEffect(() => {
    const token = localStorage.getItem('token')
    if (!token) {
      navigate('/login')
      return
    }
    
    // Fetch business data...
  }, [])
  
  return (
    <div className="min-h-screen bg-gray-100">
      {/* Dashboard layout and nested routes */}
      <main className="flex-1 bg-white rounded-lg shadow-md p-6">
        <Routes>
          <Route path="/" element={<DashboardHome businessData={businessData} />} />
          <Route path="/profile" element={<BusinessProfile businessData={businessData} />} />
          <Route path="/products" element={<ManageProducts businessData={businessData} />} />
          <Route path="/adverts" element={<AdvertsManagement businessData={businessData} />} />
          <Route path="/payments" element={<PaymentHistory businessData={businessData} />} />
          <Route path="/upgrade" element={<UpgradePlan businessData={businessData} />} />
        </Routes>
      </main>
    </div>
  )
}
```

## Routing Structure

The application uses React Router for client-side routing. The main routing structure is defined in `App.jsx`:

```jsx
function App() {
  return (
    <Router>
      <AuthProvider>
        <div className="app">
          <Header />
          <main className="main-content">
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/directory" element={<BusinessDirectory />} />
              <Route path="/business/:id" element={<BusinessDetails />} />
              <Route path="/login" element={<Login />} />
              <Route path="/register" element={<Register />} />
              <Route path="/dashboard/*" element={
                <ProtectedRoute>
                  <Dashboard />
                </ProtectedRoute>
              } />
              <Route path="*" element={<NotFound />} />
            </Routes>
          </main>
          <Footer />
        </div>
      </AuthProvider>
    </Router>
  )
}
```

## Protected Routes

The application implements protected routes to restrict access to authenticated users:

```jsx
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated } = useAuth()
  const location = useLocation()
  
  if (!isAuthenticated) {
    // Redirect to login page with return URL
    return <Navigate to="/login" state={{ from: location }} replace />
  }
  
  return children
}
```

## Page Transitions

Page transitions are implemented for a smoother user experience:

```jsx
<AnimatePresence mode="wait">
  <Routes location={location} key={location.pathname}>
    {/* Routes defined here */}
  </Routes>
</AnimatePresence>
```

## Lazy Loading

Pages are lazy-loaded to improve initial load performance:

```jsx
const Home = lazy(() => import('./pages/Home'))
const BusinessDirectory = lazy(() => import('./pages/BusinessDirectory'))
const BusinessDetails = lazy(() => import('./pages/BusinessDetails'))
const Login = lazy(() => import('./pages/Login'))
const Register = lazy(() => import('./pages/Register'))
const Dashboard = lazy(() => import('./pages/Dashboard'))
const NotFound = lazy(() => import('./pages/NotFound'))

// Wrapped with Suspense
<Suspense fallback={<Loading />}>
  <Routes>
    {/* Routes defined here */}
  </Routes>
</Suspense>
```

## Code Organization

Each page component is organized following a consistent structure:

1. Imports
2. Component definition
3. State declarations
4. Effect hooks
5. Event handlers
6. Helper functions
7. Render method with JSX
8. Export statement

This consistent organization makes it easier to understand and maintain page components.
