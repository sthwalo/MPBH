import { useState, useEffect } from 'react'
import { Routes, Route, Link, useNavigate, useLocation } from 'react-router-dom'
import DashboardHome from '../components/dashboard/DashboardHome'
import BusinessProfile from '../components/dashboard/BusinessProfile'
import ManageProducts from '../components/dashboard/ManageProducts'
import AdvertsManagement from '../components/dashboard/AdvertsManagement'
import PaymentHistory from '../components/dashboard/PaymentHistory'
import UpgradePlan from '../components/dashboard/UpgradePlan'

function Dashboard() {
  const [businessData, setBusinessData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const navigate = useNavigate()
  const location = useLocation()
  
  // Check which dashboard tab is active
  const isActive = (path) => {
    return location.pathname === path || location.pathname === path + '/'
  }

  useEffect(() => {
    // In production, this would fetch the user's business data from the API
    // const fetchBusinessData = async () => {
    //   try {
    //     const response = await fetch('/api/businesses/my-business', {
    //       headers: {
    //         'Authorization': `Bearer ${localStorage.getItem('token')}`
    //       }
    //     })
    //     if (!response.ok) throw new Error('Failed to fetch business data')
    //     const data = await response.json()
    //     setBusinessData(data)
    //   } catch (error) {
    //     console.error('Error fetching business data:', error)
    //   } finally {
    //     setLoading(false)
    //   }
    // }
    // 
    // fetchBusinessData()
    
    // For demo, we'll use mock data
    setTimeout(() => {
      setBusinessData({
        id: 1,
        name: 'Kruger Gateway Lodge',
        category: 'Tourism',
        district: 'Mbombela',
        package_type: 'Gold',
        rating: 4.8,
        adverts_remaining: 3,
        subscription: {
          status: 'active',
          next_billing_date: '2025-05-01',
          amount: 1000
        },
        statistics: {
          views: 256,
          contacts: 48,
          reviews: 15
        }
      })
      setLoading(false)
    }, 1000)
  }, [])
  
  const handleLogout = () => {
    // Clear authentication token
    localStorage.removeItem('token')
    // Redirect to login page
    navigate('/login')
  }

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen)
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-100 flex justify-center items-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Dashboard header */}
      <header className="bg-white shadow-sm">
        <div className="container mx-auto px-4 py-4 flex justify-between items-center">
          <div className="flex items-center">
            <button 
              className="lg:hidden mr-4 text-gray-500 hover:text-gray-700"
              onClick={toggleSidebar}
            >
              <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Business Dashboard</h1>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="text-right">
              <p className="font-medium text-gray-800">{businessData.name}</p>
              <div className="text-sm text-gray-500 flex items-center">
                <span className={`inline-block w-2 h-2 rounded-full mr-1 ${businessData.package_type === 'Gold' ? 'bg-yellow-400' : businessData.package_type === 'Silver' ? 'bg-gray-400' : businessData.package_type === 'Bronze' ? 'bg-yellow-600' : 'bg-gray-300'}`}></span>
                {businessData.package_type} Package
              </div>
            </div>
            <div className="relative">
              <button className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold uppercase">
                {businessData.name.charAt(0)}
              </button>
            </div>
          </div>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Sidebar navigation */}
          <aside className={`lg:w-1/4 bg-white rounded-lg shadow-md overflow-hidden ${sidebarOpen ? 'block' : 'hidden'} lg:block`}>
            <nav className="p-4">
              <ul className="space-y-2">
                <li>
                  <Link 
                    to="/dashboard" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                      </svg>
                      Dashboard Home
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/profile" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/profile') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      Business Profile
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/products" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/products') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                      </svg>
                      Manage Products
                      {(businessData.package_type === 'Basic' || businessData.package_type === 'Bronze') && (
                        <span className="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                          Upgrade
                        </span>
                      )}
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/adverts" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/adverts') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                      </svg>
                      Adverts Management
                      <span className="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                        {businessData.adverts_remaining}
                      </span>
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/payments" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/payments') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      Payment History
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/upgrade" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/upgrade') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                      </svg>
                      Upgrade Plan
                    </div>
                  </Link>
                </li>
                <li className="border-t mt-4 pt-4">
                  <button 
                    onClick={handleLogout}
                    className="block w-full text-left px-4 py-2 rounded-md hover:bg-gray-100 text-red-600"
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Logout
                    </div>
                  </button>
                </li>
              </ul>
            </nav>
          </aside>

          {/* Main content area */}
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
      </div>
    </div>
  )
}

export default Dashboard
