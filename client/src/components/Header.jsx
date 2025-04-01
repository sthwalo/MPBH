import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'

function Header({ isAuthenticated }) {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const navigate = useNavigate()

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen)
  }

  const handleLogout = () => {
    // TODO: Implement proper logout functionality
    localStorage.removeItem('token')
    navigate('/login')
    window.location.reload()
  }

  return (
    <header className="bg-white shadow-sm">
      <div className="container mx-auto py-4 px-4 flex justify-between items-center">
        <Link to="/" className="flex items-center">
          <span className="text-2xl font-bold text-primary-600">Mpumalanga Business Hub</span>
        </Link>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex space-x-8">
          <Link to="/" className="font-medium hover:text-primary-600">Home</Link>
          <Link to="/directory" className="font-medium hover:text-primary-600">Directory</Link>
          {isAuthenticated ? (
            <>
              <Link to="/dashboard" className="font-medium hover:text-primary-600">Dashboard</Link>
              <button onClick={handleLogout} className="font-medium hover:text-primary-600">Logout</button>
            </>
          ) : (
            <>
              <Link to="/login" className="font-medium hover:text-primary-600">Login</Link>
              <Link to="/register" className="btn btn-primary">Register Business</Link>
            </>
          )}
        </nav>

        {/* Mobile Menu Button */}
        <button className="md:hidden" onClick={toggleMenu}>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-6 h-6">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      {/* Mobile Navigation */}
      {isMenuOpen && (
        <div className="md:hidden bg-white shadow-md">
          <div className="container mx-auto px-4 py-3 space-y-3">
            <Link to="/" className="block font-medium hover:text-primary-600" onClick={toggleMenu}>Home</Link>
            <Link to="/directory" className="block font-medium hover:text-primary-600" onClick={toggleMenu}>Directory</Link>
            {isAuthenticated ? (
              <>
                <Link to="/dashboard" className="block font-medium hover:text-primary-600" onClick={toggleMenu}>Dashboard</Link>
                <button onClick={() => { handleLogout(); toggleMenu(); }} className="block w-full text-left font-medium hover:text-primary-600">Logout</button>
              </>
            ) : (
              <>
                <Link to="/login" className="block font-medium hover:text-primary-600" onClick={toggleMenu}>Login</Link>
                <Link to="/register" className="block btn btn-primary w-full text-center" onClick={toggleMenu}>Register Business</Link>
              </>
            )}
          </div>
        </div>
      )}
    </header>
  )
}

export default Header
