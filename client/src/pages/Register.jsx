import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'

function Register() {
  const [step, setStep] = useState(1)
  const [formData, setFormData] = useState({
    // Business Information
    businessName: '',
    category: '',
    district: '',
    description: '',
    // Contact Information
    phone: '',
    email: '',
    website: '',
    address: '',
    // Account Information
    password: '',
    confirmPassword: '',
    agreeToTerms: false
  })
  const [errors, setErrors] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const navigate = useNavigate()

  // Available options based on the database schema
  const categories = ['Tourism', 'Agriculture', 'Construction', 'Events']
  const districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge']
  
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

  const validateStep1 = () => {
    const newErrors = {}
    
    if (!formData.businessName.trim()) {
      newErrors.businessName = 'Business name is required'
    }
    
    if (!formData.category) {
      newErrors.category = 'Please select a category'
    }
    
    if (!formData.district) {
      newErrors.district = 'Please select a district'
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Business description is required'
    } else if (formData.description.length < 50) {
      newErrors.description = 'Description should be at least 50 characters'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const validateStep2 = () => {
    const newErrors = {}
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required'
    } else if (!/^\+?[\d\s-]{10,15}$/.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid phone number'
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required'
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address'
    }
    
    if (formData.website && !/^(https?:\/\/)?[\w-]+(\.[\w-]+)+(\/[\w-./?%&=]*)?$/.test(formData.website)) {
      newErrors.website = 'Please enter a valid website URL'
    }
    
    if (!formData.address.trim()) {
      newErrors.address = 'Business address is required'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const validateStep3 = () => {
    const newErrors = {}
    
    if (!formData.password) {
      newErrors.password = 'Password is required'
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters'
    }
    
    if (!formData.confirmPassword) {
      newErrors.confirmPassword = 'Please confirm your password'
    } else if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match'
    }
    
    if (!formData.agreeToTerms) {
      newErrors.agreeToTerms = 'You must agree to the terms and conditions'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleNextStep = () => {
    let isValid = false
    
    switch (step) {
      case 1:
        isValid = validateStep1()
        break
      case 2:
        isValid = validateStep2()
        break
      default:
        isValid = false
    }
    
    if (isValid) {
      setStep(step + 1)
      window.scrollTo(0, 0)
    }
  }

  const handlePreviousStep = () => {
    setStep(step - 1)
    window.scrollTo(0, 0)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateStep3()) return
    
    setIsLoading(true)
    
    try {
      // In production, this would be a real API call
      // const response = await fetch('/api/auth/register', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify(formData)
      // })
      
      // if (!response.ok) {
      //   const errorData = await response.json()
      //   throw new Error(errorData.message || 'Registration failed')
      // }
      
      // For demo purposes, we're just simulating a successful registration
      setTimeout(() => {
        // Navigate to success page or login
        navigate('/registration-success')
      }, 1500)
    } catch (error) {
      setErrors({ form: error.message || 'Registration failed. Please try again.' })
      window.scrollTo(0, 0)
    } finally {
      setIsLoading(false)
    }
  }

  // Render progress steps
  const renderProgressSteps = () => (
    <div className="flex items-center justify-between mb-8">
      <div className="flex items-center">
        <div className={`rounded-full h-10 w-10 flex items-center justify-center ${step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'}`}>
          1
        </div>
        <div className={`ml-2 text-sm font-medium ${step >= 1 ? 'text-blue-600' : 'text-gray-500'}`}>Business Details</div>
      </div>
      <div className={`flex-1 h-1 mx-4 ${step >= 2 ? 'bg-blue-600' : 'bg-gray-200'}`}></div>
      <div className="flex items-center">
        <div className={`rounded-full h-10 w-10 flex items-center justify-center ${step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'}`}>
          2
        </div>
        <div className={`ml-2 text-sm font-medium ${step >= 2 ? 'text-blue-600' : 'text-gray-500'}`}>Contact Info</div>
      </div>
      <div className={`flex-1 h-1 mx-4 ${step >= 3 ? 'bg-blue-600' : 'bg-gray-200'}`}></div>
      <div className="flex items-center">
        <div className={`rounded-full h-10 w-10 flex items-center justify-center ${step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'}`}>
          3
        </div>
        <div className={`ml-2 text-sm font-medium ${step >= 3 ? 'text-blue-600' : 'text-gray-500'}`}>Account Setup</div>
      </div>
    </div>
  )

  // Step 1: Business Information
  const renderStep1 = () => (
    <>
      <h3 className="text-xl font-bold mb-6">Business Information</h3>
      <div className="mb-6">
        <label htmlFor="businessName" className="form-label">Business Name</label>
        <input
          type="text"
          id="businessName"
          name="businessName"
          className={`form-control ${errors.businessName ? 'border-red-500' : ''}`}
          value={formData.businessName}
          onChange={handleChange}
          placeholder="Your Business Name"
        />
        {errors.businessName && <p className="text-red-500 text-sm mt-1">{errors.businessName}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="category" className="form-label">Business Category</label>
        <select
          id="category"
          name="category"
          className={`form-control ${errors.category ? 'border-red-500' : ''}`}
          value={formData.category}
          onChange={handleChange}
        >
          <option value="">Select a category</option>
          {categories.map(category => (
            <option key={category} value={category}>{category}</option>
          ))}
        </select>
        {errors.category && <p className="text-red-500 text-sm mt-1">{errors.category}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="district" className="form-label">District</label>
        <select
          id="district"
          name="district"
          className={`form-control ${errors.district ? 'border-red-500' : ''}`}
          value={formData.district}
          onChange={handleChange}
        >
          <option value="">Select a district</option>
          {districts.map(district => (
            <option key={district} value={district}>{district}</option>
          ))}
        </select>
        {errors.district && <p className="text-red-500 text-sm mt-1">{errors.district}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="description" className="form-label">Business Description</label>
        <textarea
          id="description"
          name="description"
          rows="4"
          className={`form-control ${errors.description ? 'border-red-500' : ''}`}
          value={formData.description}
          onChange={handleChange}
          placeholder="Describe your business, services, and what makes you unique..."
        ></textarea>
        <div className="flex justify-between mt-1">
          <p className={`text-sm ${formData.description.length < 50 ? 'text-red-500' : 'text-gray-500'}`}>
            Minimum 50 characters
          </p>
          <p className="text-sm text-gray-500">
            {formData.description.length}/500
          </p>
        </div>
        {errors.description && <p className="text-red-500 text-sm">{errors.description}</p>}
      </div>
      
      <div className="mt-8">
        <button
          type="button"
          className="btn btn-primary w-full"
          onClick={handleNextStep}
        >
          Continue to Contact Information
        </button>
      </div>
    </>
  )

  // Step 2: Contact Information
  const renderStep2 = () => (
    <>
      <h3 className="text-xl font-bold mb-6">Contact Information</h3>
      <div className="mb-6">
        <label htmlFor="phone" className="form-label">Phone Number</label>
        <input
          type="tel"
          id="phone"
          name="phone"
          className={`form-control ${errors.phone ? 'border-red-500' : ''}`}
          value={formData.phone}
          onChange={handleChange}
          placeholder="+27 12 345 6789"
        />
        {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="email" className="form-label">Email Address</label>
        <input
          type="email"
          id="email"
          name="email"
          className={`form-control ${errors.email ? 'border-red-500' : ''}`}
          value={formData.email}
          onChange={handleChange}
          placeholder="your@email.com"
        />
        {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="website" className="form-label">Website (Optional)</label>
        <input
          type="url"
          id="website"
          name="website"
          className={`form-control ${errors.website ? 'border-red-500' : ''}`}
          value={formData.website}
          onChange={handleChange}
          placeholder="https://www.yourbusiness.co.za"
        />
        {errors.website && <p className="text-red-500 text-sm mt-1">{errors.website}</p>}
      </div>
      
      <div className="mb-6">
        <label htmlFor="address" className="form-label">Business Address</label>
        <textarea
          id="address"
          name="address"
          rows="3"
          className={`form-control ${errors.address ? 'border-red-500' : ''}`}
          value={formData.address}
          onChange={handleChange}
          placeholder="123 Main Road, Nelspruit, Mpumalanga"
        ></textarea>
        {errors.address && <p className="text-red-500 text-sm mt-1">{errors.address}</p>}
      </div>
      
      <div className="mt-8 flex justify-between">
        <button
          type="button"
          className="btn btn-outline"
          onClick={handlePreviousStep}
        >
          Back
        </button>
        <button
          type="button"
          className="btn btn-primary"
          onClick={handleNextStep}
        >
          Continue to Account Setup
        </button>
      </div>
    </>
  )

  // Step 3: Account Information
  const renderStep3 = () => (
    <>
      <h3 className="text-xl font-bold mb-6">Create Your Account</h3>
      
      {errors.form && (
        <div className="bg-red-50 text-red-800 p-4 rounded-md mb-6">
          {errors.form}
        </div>
      )}
      
      <div className="mb-6">
        <label htmlFor="password" className="form-label">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          className={`form-control ${errors.password ? 'border-red-500' : ''}`}
          value={formData.password}
          onChange={handleChange}
          placeholder="u2022u2022u2022u2022u2022u2022u2022u2022"
        />
        {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
        <p className="text-gray-500 text-sm mt-1">Must be at least 8 characters</p>
      </div>
      
      <div className="mb-6">
        <label htmlFor="confirmPassword" className="form-label">Confirm Password</label>
        <input
          type="password"
          id="confirmPassword"
          name="confirmPassword"
          className={`form-control ${errors.confirmPassword ? 'border-red-500' : ''}`}
          value={formData.confirmPassword}
          onChange={handleChange}
          placeholder="u2022u2022u2022u2022u2022u2022u2022u2022"
        />
        {errors.confirmPassword && <p className="text-red-500 text-sm mt-1">{errors.confirmPassword}</p>}
      </div>
      
      <div className="mb-6">
        <div className="flex items-start">
          <div className="flex items-center h-5">
            <input
              type="checkbox"
              id="agreeToTerms"
              name="agreeToTerms"
              checked={formData.agreeToTerms}
              onChange={handleChange}
              className="h-4 w-4 text-blue-600 border-gray-300 rounded"
            />
          </div>
          <div className="ml-3 text-sm">
            <label htmlFor="agreeToTerms" className="text-gray-700">
              I agree to the <Link to="/terms" className="text-blue-600 hover:text-blue-800">Terms and Conditions</Link> and <Link to="/privacy" className="text-blue-600 hover:text-blue-800">Privacy Policy</Link>
            </label>
            {errors.agreeToTerms && <p className="text-red-500 text-sm mt-1">{errors.agreeToTerms}</p>}
          </div>
        </div>
      </div>
      
      <div className="mt-8 flex justify-between">
        <button
          type="button"
          className="btn btn-outline"
          onClick={handlePreviousStep}
        >
          Back
        </button>
        <button
          type="submit"
          className={`btn btn-primary ${isLoading ? 'opacity-75' : ''}`}
          disabled={isLoading}
        >
          {isLoading ? (
            <>
              <svg className="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Registering...
            </>
          ) : 'Register Business'}
        </button>
      </div>
    </>
  )

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div className="p-8">
          <h2 className="text-2xl font-bold text-center mb-6">Register Your Business</h2>
          
          {renderProgressSteps()}
          
          <form onSubmit={handleSubmit}>
            {step === 1 && renderStep1()}
            {step === 2 && renderStep2()}
            {step === 3 && renderStep3()}
          </form>
          
          <div className="text-center mt-6">
            <p className="text-gray-600">
              Already have an account?{' '}
              <Link to="/login" className="text-blue-600 hover:text-blue-800 font-medium">
                Login here
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Register
