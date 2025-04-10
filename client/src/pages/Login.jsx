import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

function Login({ setIsAuthenticated }) {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    rememberMe: false
  })
  const [errors, setErrors] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const navigate = useNavigate()

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setFormData(prevData => ({
      ...prevData,
      [name]: type === 'checkbox' ? checked : value
    }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!formData.email) {
      newErrors.email = 'Email is required'
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid'
    }
    
    if (!formData.password) {
      newErrors.password = 'Password is required'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) return
    
    setIsLoading(true)
    
    try {
      // Show loading message
      const loadingToast = toast.loading('Logging in...');
      
      // Make the real API call
      const response = await fetch('http://localhost:8000/api/auth/login', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: formData.email,
          password: formData.password
        })
      });
      
      // Clear loading toast
      toast.dismiss(loadingToast);
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Login failed');
      }
      
      // Parse the response data
      const data = await response.json();
      console.log('Login successful:', data);
      
      // Store the token and user info
      localStorage.setItem('mpbh_token', data.data.token);
      localStorage.setItem('mpbh_user', JSON.stringify({
        id: data.data.user.id,
        businessId: data.data.business.id
      }));
      
      // Show success message
      toast.success('Login successful!');
      
      // Update authentication state
      setIsAuthenticated(true);
      
      // Redirect to dashboard
      navigate('/dashboard');
    } catch (error) {
      setErrors({ form: error.message || 'Login failed. Please try again.' })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-brand-white rounded-lg shadow-md border border-brand-gray-200 overflow-hidden">
        <div className="p-8">
          <h2 className="text-2xl font-bold text-center mb-6">Login to Your Account</h2>
          
          {errors.form && (
            <div className="bg-red-50 text-red-800 p-4 rounded-md mb-6">
              {errors.form}
            </div>
          )}
          
          <form onSubmit={handleSubmit}>
            <div className="mb-6">
              <label htmlFor="email" className="block text-brand-black font-medium mb-1">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                className={`w-full p-2 border ${errors.email ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                value={formData.email}
                onChange={handleChange}
                placeholder="youremail@example.com"
              />
              {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
            </div>
            
            <div className="mb-6">
              <div className="flex justify-between items-center">
                <label htmlFor="password" className="block text-brand-black font-medium mb-1">Password</label>
                <Link to="/forgot-password" className="text-sm text-brand-black hover:text-brand-gray-600 border-b border-brand-gray-400">
                  Forgot Password?
                </Link>
              </div>
              <input
                type="password"
                id="password"
                name="password"
                className={`w-full p-2 border ${errors.password ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                value={formData.password}
                onChange={handleChange}
                placeholder="••••••••"
              />
              {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
            </div>
            
            <div className="flex items-center mb-6">
              <input
                type="checkbox"
                id="rememberMe"
                name="rememberMe"
                checked={formData.rememberMe}
                onChange={handleChange}
                className="h-4 w-4 text-brand-black border-brand-gray-300 rounded focus:ring-brand-black"
              />
              <label htmlFor="rememberMe" className="ml-2 text-sm text-brand-gray-700">
                Remember me
              </label>
            </div>
            
            <button
              type="submit"
              className={`px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors w-full ${isLoading ? 'opacity-75' : ''}`}
              disabled={isLoading}
            >
              {isLoading ? (
                <>
                  <svg className="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Logging in...
                </>
              ) : 'Login'}
            </button>
          </form>
          
          <div className="text-center mt-6">
            <p className="text-brand-gray-600">
              Don't have an account?{' '}
              <Link to="/register" className="text-brand-black hover:text-brand-gray-600 border-b border-brand-gray-400 font-medium">
                Register Your Business
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Login
