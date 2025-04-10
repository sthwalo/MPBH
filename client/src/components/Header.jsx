import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import mpbhLogo from '../assets/images/MPBH.jpeg'

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
    <header className="sticky top-0 z-50 bg-brand-white shadow-brand-sm border-b border-brand-gray-200">
      <div className="container mx-auto py-4 px-4 flex justify-between items-center">
        <Link to="/" className="flex items-center">
          <img src={mpbhLogo} alt="MPBH Logo" className="h-14" />
        </Link>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex space-x-8">
          <Link to="/" className="font-medium text-brand-gray-600 hover:text-brand-black transition-colors">Home</Link>
          <Link to="/directory" className="font-medium text-brand-gray-600 hover:text-brand-black transition-colors">Directory</Link>
          {isAuthenticated ? (
            <>
              <Link to="/dashboard" className="font-medium text-brand-gray-600 hover:text-brand-black transition-colors">Dashboard</Link>
              <button onClick={handleLogout} className="font-medium text-brand-gray-600 hover:text-brand-black transition-colors">Logout</button>
            </>
          ) : (
            <>
              <Link to="/login" className="font-medium text-brand-gray-600 hover:text-brand-black transition-colors">Login</Link>
              <Link to="/register" className="px-4 py-2 bg-brand-black text-brand-white rounded-md font-medium hover:bg-brand-gray-800 transition-colors">Register Business</Link>
            </>
          )}
        </nav>

        {/* Mobile Menu Button */}
        <button className="md:hidden text-brand-gray-700 hover:text-brand-black" onClick={toggleMenu}>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-6 h-6">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      {/* Mobile Navigation */}
      {isMenuOpen && (
        <div className="md:hidden bg-white shadow-brand-md">
          <div className="container mx-auto px-4 py-3 space-y-3">
            <Link to="/" className="block font-medium text-brand-gray-700 hover:text-brand-black transition-colors" onClick={toggleMenu}>Home</Link>
            <Link to="/directory" className="block font-medium text-brand-gray-700 hover:text-brand-black transition-colors" onClick={toggleMenu}>Directory</Link>
            {isAuthenticated ? (
              <>
                <Link to="/dashboard" className="block font-medium text-brand-gray-700 hover:text-brand-black transition-colors" onClick={toggleMenu}>Dashboard</Link>
                <button onClick={() => { handleLogout(); toggleMenu(); }} className="block w-full text-left font-medium text-brand-gray-700 hover:text-brand-black transition-colors">Logout</button>
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
