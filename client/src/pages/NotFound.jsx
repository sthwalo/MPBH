import { Link } from 'react-router-dom'

function NotFound() {
  return (
    <div className="container mx-auto px-4 py-16 flex flex-col items-center">
      <div className="text-center">
        <h1 className="text-6xl font-bold text-blue-600 mb-4">404</h1>
        <h2 className="text-3xl font-bold mb-6">Page Not Found</h2>
        <p className="text-xl text-gray-600 mb-8">
          The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div className="flex flex-col sm:flex-row justify-center gap-4">
          <Link to="/" className="btn btn-primary">
            Go to Homepage
          </Link>
          <Link to="/directory" className="btn btn-outline">
            Browse Businesses
          </Link>
        </div>
      </div>
    </div>
  )
}

export default NotFound
