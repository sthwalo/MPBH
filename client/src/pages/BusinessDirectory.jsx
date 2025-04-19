import { useState, useEffect } from 'react'
import { useSearchParams } from 'react-router-dom'
import Fuse from 'fuse.js'
import BusinessCard from '../components/BusinessCard'

// Main Business Directory component
function BusinessDirectory() {
  // State management
  const [searchParams, setSearchParams] = useSearchParams()
  const [businesses, setBusinesses] = useState([]) // All businesses from API
  const [loading, setLoading] = useState(true) // Loading state
  const [error, setError] = useState(null) // Error state
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '') // Search input
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '') // Selected category filter
  const [selectedDistrict, setSelectedDistrict] = useState(searchParams.get('district') || '') // Selected district filter
  const [filteredBusinesses, setFilteredBusinesses] = useState([]) // Filtered businesses based on search and filters
  const [debugMode, setDebugMode] = useState(false) // Debug mode toggle

  // Predefined categories and districts from database schema
  const categories = ['Tourism', 'Agriculture', 'Construction', 'Events']
  const districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge']

  // Fetch businesses from API with filters
  useEffect(() => {
    const fetchBusinesses = async () => {
      try {
        setLoading(true)
        const queryParams = new URLSearchParams()
        
        // Add filters to query parameters
        if (selectedCategory) queryParams.append('category', selectedCategory)
        if (selectedDistrict) queryParams.append('district', selectedDistrict)
        
        // Fetch data from API
        const response = await fetch(`/api/businesses?${queryParams.toString()}`)
        
        if (!response.ok) {
          throw new Error(`API request failed with status ${response.status}`)
        }
        
        const data = await response.json()
        
        // Validate API response
        if (data.status !== 'success') {
          throw new Error('Invalid API response format')
        }
        
        // Update state with fetched data
        setBusinesses(data.data)
        setFilteredBusinesses(data.data)
        
      } catch (err) {
        // Log error for debugging
        console.error('Error fetching businesses:', err)
        setError(err.message)
      } finally {
        setLoading(false)
      }
    }
    
    // Execute the fetch function
    fetchBusinesses()
  }, [selectedCategory, selectedDistrict]) // Re-fetch when filters change

  // Update URL parameters when filters change
  useEffect(() => {
    const params = new URLSearchParams()
    if (searchTerm) params.set('search', searchTerm)
    if (selectedCategory) params.set('category', selectedCategory)
    if (selectedDistrict) params.set('district', selectedDistrict)
    setSearchParams(params)
  }, [searchTerm, selectedCategory, selectedDistrict, setSearchParams])

  // Handle search functionality using Fuse.js
  useEffect(() => {
    if (searchTerm) {
      const fuse = new Fuse(businesses, {
        keys: ['name', 'description', 'category'],
        threshold: 0.4,
      })
      const results = fuse.search(searchTerm).map(result => result.item)
      setFilteredBusinesses(results)
    } else {
      // Apply only category and district filters if no search term
      let filtered = [...businesses]
      
      if (selectedCategory) {
        filtered = filtered.filter(business => business.category === selectedCategory)
      }
      
      if (selectedDistrict) {
        filtered = filtered.filter(business => business.district === selectedDistrict)
      }
      
      setFilteredBusinesses(filtered)
    }
  }, [searchTerm, businesses, selectedCategory, selectedDistrict])

  // Handle search input changes
  const handleSearchChange = (e) => {
    setSearchTerm(e.target.value)
  }

  // Handle category selection
  const handleCategoryChange = (category) => {
    setSelectedCategory(category === selectedCategory ? '' : category)
  }

  // Handle district selection
  const handleDistrictChange = (district) => {
    setSelectedDistrict(district === selectedDistrict ? '' : district)
  }

  // Clear all filters
  const clearFilters = () => {
    setSearchTerm('')
    setSelectedCategory('')
    setSelectedDistrict('')
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Business Directory</h1>
      
      {/* Search and Filters section */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-8">
        <div className="mb-6">
          <label htmlFor="search" className="sr-only">Search businesses</label>
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              id="search"
              className="form-control pl-10"
              placeholder="Search by business name or description"
              value={searchTerm}
              onChange={handleSearchChange}
            />
          </div>
        </div>
        
        {/* Debug toggle button */}
        <div className="mb-6">
          <button onClick={() => setDebugMode(!debugMode)}>
            {debugMode ? 'Hide Debug' : 'Show Debug'}
          </button>
        </div>
      
        {/* Debug information display */}
        {debugMode && (
          <pre style={{ 
            whiteSpace: 'pre-wrap', 
            backgroundColor: '#f0f0f0', 
            padding: '1rem', 
            borderRadius: '4px',
            overflow: 'auto'
          }}>
            {JSON.stringify({
              businesses: businesses.length,
              filtered: filteredBusinesses.length,
              error,
              loading,
              searchTerm,
              selectedCategory,
              selectedDistrict
            }, null, 2)}
          </pre>
        )}
        
        {/* Filters grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          {/* Categories filter */}
          <div>
            <h3 className="font-semibold mb-3">Categories</h3>
            <div className="flex flex-wrap gap-2">
              {categories.map(category => (
                <button
                  key={category}
                  className={`px-4 py-2 rounded-full text-sm ${selectedCategory === category 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200'}`}
                  onClick={() => handleCategoryChange(category)}
                >
                  {category}
                </button>
              ))}
            </div>
          </div>
          
          {/* Districts filter */}
          <div>
            <h3 className="font-semibold mb-3">Districts</h3>
            <div className="flex flex-wrap gap-2">
              {districts.map(district => (
                <button
                  key={district}
                  className={`px-4 py-2 rounded-full text-sm ${selectedDistrict === district 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200'}`}
                  onClick={() => handleDistrictChange(district)}
                >
                  {district}
                </button>
              ))}
            </div>
          </div>
        </div>
        
        {/* Active filters display */}
        {(searchTerm || selectedCategory || selectedDistrict) && (
          <div className="flex items-center justify-between">
            <div className="text-sm text-gray-600">
              <span className="font-medium">{filteredBusinesses.length}</span> businesses found
            </div>
            <button 
              className="text-sm text-red-600 hover:text-red-800 font-medium"
              onClick={clearFilters}
            >
              Clear all filters
            </button>
          </div>
        )}
      </div>
      
      {/* Business Listings section */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {loading ? (
          // Loading state - show skeleton cards
          Array.from({ length: 6 }).map((_, index) => (
            <div key={index} className="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
              <div className="h-48 bg-gray-200"></div>
              <div className="p-4">
                <div className="h-6 bg-gray-200 rounded mb-2"></div>
                <div className="h-4 bg-gray-200 rounded mb-2 w-2/3"></div>
                <div className="h-4 bg-gray-200 rounded mb-2 w-1/2"></div>
              </div>
            </div>
          ))
        ) : error ? (
          // Error state - show error message
          <div className="col-span-3 text-center py-8">
            <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-red-800">{error}</h3>
            <p className="mt-1 text-gray-500">Using mock data instead. In production, this would connect to the backend API.</p>
          </div>
        ) : filteredBusinesses.length === 0 ? (
          // No results state - show empty state message
          <div className="col-span-3 text-center py-8">
            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-gray-900">No businesses found</h3>
            <p className="mt-1 text-gray-500">Try adjusting your search or filter criteria</p>
            <button 
              className="mt-4 text-blue-600 hover:text-blue-800 font-medium"
              onClick={clearFilters}
            >
              Clear all filters
            </button>
          </div>
        ) : (
          // Success state - render business cards
          filteredBusinesses.map(business => (
            <BusinessCard key={business.id} business={business} />
          ))
        )}
      </div>
    </div>
  )
}

export default BusinessDirectory