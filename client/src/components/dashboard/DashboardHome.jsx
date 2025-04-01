import { Link } from 'react-router-dom'

function DashboardHome({ businessData }) {
  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Welcome back to {businessData.name}</h2>
      
      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-blue-50 border border-blue-100 rounded-lg p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-blue-600">Profile Views</p>
              <h3 className="text-3xl font-bold mt-2">{businessData.statistics.views}</h3>
            </div>
            <div className="p-3 bg-blue-100 rounded-full">
              <svg className="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
          </div>
          <div className="mt-2 text-sm text-blue-600">Last 30 days</div>
        </div>
        
        <div className="bg-green-50 border border-green-100 rounded-lg p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-green-600">Contact Clicks</p>
              <h3 className="text-3xl font-bold mt-2">{businessData.statistics.contacts}</h3>
            </div>
            <div className="p-3 bg-green-100 rounded-full">
              <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </div>
          </div>
          <div className="mt-2 text-sm text-green-600">+12% from last month</div>
        </div>
        
        <div className="bg-yellow-50 border border-yellow-100 rounded-lg p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-yellow-600">Reviews</p>
              <h3 className="text-3xl font-bold mt-2">{businessData.statistics.reviews}</h3>
            </div>
            <div className="p-3 bg-yellow-100 rounded-full">
              <svg className="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
          </div>
          <div className="mt-2 text-sm text-yellow-600">Average Rating: {businessData.rating.toFixed(1)}/5</div>
        </div>
      </div>
      
      {/* Subscription Status */}
      <div className="bg-white border rounded-lg p-6 mb-8">
        <h3 className="text-lg font-bold mb-4">Subscription Status</h3>
        <div className="flex flex-col md:flex-row md:items-center justify-between">
          <div>
            <div className="flex items-center">
              <span className={`inline-block w-3 h-3 rounded-full mr-2 ${businessData.subscription.status === 'active' ? 'bg-green-500' : 'bg-red-500'}`}></span>
              <span className="font-medium">{businessData.subscription.status === 'active' ? 'Active' : 'Inactive'}</span>
            </div>
            <p className="text-gray-600 mt-1">
              {businessData.package_type} Package - R{businessData.subscription.amount}/month
            </p>
            <p className="text-gray-600 mt-1">
              Next billing date: {new Date(businessData.subscription.next_billing_date).toLocaleDateString()}
            </p>
          </div>
          <div className="mt-4 md:mt-0">
            <Link to="/dashboard/upgrade" className="btn btn-primary">
              Upgrade Package
            </Link>
          </div>
        </div>
      </div>
      
      {/* Quick Actions */}
      <h3 className="text-lg font-bold mb-4">Quick Actions</h3>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link to="/dashboard/profile" className="border rounded-lg p-4 hover:bg-gray-50 flex items-center">
          <div className="p-3 bg-blue-100 rounded-full mr-4">
            <svg className="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </div>
          <div>
            <h4 className="font-medium">Edit Profile</h4>
            <p className="text-sm text-gray-500">Update your business information</p>
          </div>
        </Link>
        
        {(businessData.package_type === 'Silver' || businessData.package_type === 'Gold') && (
          <Link to="/dashboard/products" className="border rounded-lg p-4 hover:bg-gray-50 flex items-center">
            <div className="p-3 bg-green-100 rounded-full mr-4">
              <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div>
              <h4 className="font-medium">Add Products</h4>
              <p className="text-sm text-gray-500">Showcase your products and services</p>
            </div>
          </Link>
        )}
        
        {businessData.adverts_remaining > 0 && (
          <Link to="/dashboard/adverts" className="border rounded-lg p-4 hover:bg-gray-50 flex items-center">
            <div className="p-3 bg-yellow-100 rounded-full mr-4">
              <svg className="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
              </svg>
            </div>
            <div>
              <h4 className="font-medium">Create Advert</h4>
              <p className="text-sm text-gray-500">{businessData.adverts_remaining} adverts available</p>
            </div>
          </Link>
        )}
        
        <Link to="/business/1" className="border rounded-lg p-4 hover:bg-gray-50 flex items-center">
          <div className="p-3 bg-purple-100 rounded-full mr-4">
            <svg className="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <div>
            <h4 className="font-medium">View Public Profile</h4>
            <p className="text-sm text-gray-500">See how customers view your listing</p>
          </div>
        </Link>
      </div>
    </div>
  )
}

export default DashboardHome
