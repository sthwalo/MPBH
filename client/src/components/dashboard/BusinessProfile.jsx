import { useState } from 'react'

function BusinessProfile({ businessData }) {
  const [formData, setFormData] = useState({
    businessName: businessData.name,
    category: businessData.category,
    district: businessData.district,
    description: "Luxury accommodation near Kruger National Park with guided safari tours. Our lodge offers comfortable rooms, a restaurant serving local cuisine, and a pool overlooking the bush. We organize daily game drives into Kruger National Park with experienced guides who know where to find the Big Five.",
    phone: "+27123456789",
    email: "info@krugergateway.co.za",
    website: "https://krugergateway.co.za",
    address: "123 Safari Road, Hazyview, Mpumalanga",
    operatingHours: {
      monday: "8:00 - 18:00",
      tuesday: "8:00 - 18:00",
      wednesday: "8:00 - 18:00",
      thursday: "8:00 - 18:00",
      friday: "8:00 - 18:00",
      saturday: "9:00 - 17:00",
      sunday: "9:00 - 16:00"
    }
  })
  const [errors, setErrors] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')

  // Available options based on the database schema
  const categories = ['Tourism', 'Agriculture', 'Construction', 'Events']
  const districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge']

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prevData => ({
      ...prevData,
      [name]: value
    }))
    
    // Clear error and success message when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
    if (successMessage) {
      setSuccessMessage('')
    }
  }

  const handleHoursChange = (day, value) => {
    setFormData(prevData => ({
      ...prevData,
      operatingHours: {
        ...prevData.operatingHours,
        [day]: value
      }
    }))
  }

  const validateForm = () => {
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
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required'
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

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) {
      window.scrollTo(0, 0)
      return
    }
    
    setIsLoading(true)
    
    try {
      // In production, this would be a real API call
      // const response = await fetch('/api/businesses/update', {
      //   method: 'PUT',
      //   headers: {
      //     'Content-Type': 'application/json',
      //     'Authorization': `Bearer ${localStorage.getItem('token')}`
      //   },
      //   body: JSON.stringify(formData)
      // })
      
      // if (!response.ok) {
      //   const errorData = await response.json()
      //   throw new Error(errorData.message || 'Failed to update business profile')
      // }
      
      // For demo purposes, we'll just simulate a success response
      setTimeout(() => {
        setSuccessMessage('Business profile updated successfully!')
        setIsLoading(false)
        window.scrollTo(0, 0)
      }, 1000)
    } catch (error) {
      setErrors({ form: error.message || 'Failed to update profile. Please try again.' })
      setIsLoading(false)
      window.scrollTo(0, 0)
    }
  }

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Business Profile</h2>
      
      {/* Success message */}
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 mb-6">
          {successMessage}
        </div>
      )}
      
      {/* Error message */}
      {errors.form && (
        <div className="bg-red-50 border border-red-200 text-red-800 rounded-md p-4 mb-6">
          {errors.form}
        </div>
      )}
      
      <form onSubmit={handleSubmit}>
        <div className="bg-blue-50 border border-blue-100 rounded-md p-4 mb-6">
          <p className="text-blue-800">
            <strong>Package Status:</strong> {businessData.package_type} Package
            {(businessData.package_type === 'Basic' || businessData.package_type === 'Bronze') && (
              <span className="ml-2">
                <a href="/dashboard/upgrade" className="text-blue-600 underline">Upgrade</a> to unlock more features.
              </span>
            )}
          </p>
        </div>
        
        <div className="bg-white border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">Basic Information</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="businessName" className="form-label">Business Name</label>
              <input
                type="text"
                id="businessName"
                name="businessName"
                className={`form-control ${errors.businessName ? 'border-red-500' : ''}`}
                value={formData.businessName}
                onChange={handleChange}
              />
              {errors.businessName && <p className="text-red-500 text-sm mt-1">{errors.businessName}</p>}
            </div>
            
            <div>
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
          </div>
          
          <div className="mt-6">
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
          
          <div className="mt-6">
            <label htmlFor="description" className="form-label">Business Description</label>
            <textarea
              id="description"
              name="description"
              rows="6"
              className={`form-control ${errors.description ? 'border-red-500' : ''}`}
              value={formData.description}
              onChange={handleChange}
            ></textarea>
            <div className="flex justify-between mt-1">
              <p className={`text-sm ${formData.description.length < 50 ? 'text-red-500' : 'text-gray-500'}`}>
                Minimum 50 characters
              </p>
              <p className="text-sm text-gray-500">
                {formData.description.length}/1000
              </p>
            </div>
            {errors.description && <p className="text-red-500 text-sm">{errors.description}</p>}
          </div>
        </div>
        
        <div className="bg-white border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">Contact Information</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="phone" className="form-label">Phone Number</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                className={`form-control ${errors.phone ? 'border-red-500' : ''}`}
                value={formData.phone}
                onChange={handleChange}
              />
              {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
            </div>
            
            <div>
              <label htmlFor="email" className="form-label">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                className={`form-control ${errors.email ? 'border-red-500' : ''}`}
                value={formData.email}
                onChange={handleChange}
              />
              {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
            </div>
          </div>
          
          <div className="mt-6">
            <label htmlFor="website" className="form-label">Website (Optional)</label>
            <input
              type="url"
              id="website"
              name="website"
              className={`form-control ${errors.website ? 'border-red-500' : ''}`}
              value={formData.website}
              onChange={handleChange}
            />
            {errors.website && <p className="text-red-500 text-sm mt-1">{errors.website}</p>}
          </div>
          
          <div className="mt-6">
            <label htmlFor="address" className="form-label">Business Address</label>
            <textarea
              id="address"
              name="address"
              rows="3"
              className={`form-control ${errors.address ? 'border-red-500' : ''}`}
              value={formData.address}
              onChange={handleChange}
            ></textarea>
            {errors.address && <p className="text-red-500 text-sm mt-1">{errors.address}</p>}
          </div>
        </div>
        
        <div className="bg-white border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">Operating Hours</h3>
          
          <div className="space-y-4">
            {Object.entries(formData.operatingHours).map(([day, hours]) => (
              <div key={day} className="flex items-center">
                <div className="w-32">
                  <span className="capitalize">{day}</span>
                </div>
                <input
                  type="text"
                  className="form-control"
                  value={hours}
                  onChange={(e) => handleHoursChange(day, e.target.value)}
                  placeholder="9:00 - 17:00"
                />
              </div>
            ))}
          </div>
        </div>
        
        <div className="flex justify-end">
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
                Saving Changes...
              </>
            ) : 'Save Changes'}
          </button>
        </div>
      </form>
    </div>
  )
}

export default BusinessProfile
