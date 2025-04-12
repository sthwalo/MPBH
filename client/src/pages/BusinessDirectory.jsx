import { useState, useEffect } from 'react'
import { useSearchParams } from 'react-router-dom'
import Fuse from 'fuse.js'
import BusinessCard from '../components/BusinessCard'

function BusinessDirectory() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [businesses, setBusinesses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '')
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '')
  const [selectedDistrict, setSelectedDistrict] = useState(searchParams.get('district') || '')
  const [filteredBusinesses, setFilteredBusinesses] = useState([])

  // Categories and districts from the database schema
  const categories = ['Tourism', 'Agriculture', 'Construction', 'Events']
  const districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge']

  // Fetch businesses from the API
  useEffect(() => {
    const fetchBusinesses = async () => {
      try {
        setLoading(true)
        let queryParams = new URLSearchParams()
        if (selectedCategory) queryParams.append('category', selectedCategory)
        if (selectedDistrict) queryParams.append('district', selectedDistrict)
        
        // Update the fetch URL to include /api prefix
        const response = await fetch(`/api/businesses?${queryParams.toString()}`)
        
        if (!response.ok) {
          throw new Error(`API request failed with status ${response.status}`)
        }
        
        const data = await response.json()
        setBusinesses(data)
        setFilteredBusinesses(data)
      } catch (err) {
        setError(err.message)
        // Move mock data inside the catch block
        const mockData = [
          {
            id: 1,
            name: "Kruger Gateway Lodge",
            category: "Tourism",
            district: "Mbombela",
            package_type: "Gold",
            rating: 4.8,
            description: "Luxury accommodation near Kruger National Park with guided safari tours.",
            contact: { phone: "+27123456789", email: "info@krugergateway.co.za", website: "https://krugergateway.co.za" },
            image: "/assets/images/tourism-1.jpg"
          },
          {
            id: 2,
            name: "Mpumalanga Organic Farms",
            category: "Agriculture",
            district: "Bushbuckridge",
            package_type: "Silver",
            rating: 4.5,
            description: "Certified organic produce supplier with distribution across the province.",
            contact: { phone: "+27123456790", email: "sales@mpumalangaorganic.co.za" },
            image: "/assets/images/agriculture-1.jpg"
          },
          {
            id: 3,
            name: "BuildRight Construction",
            category: "Construction",
            district: "Emalahleni",
            package_type: "Bronze",
            rating: 4.2,
            description: "Commercial and residential construction specialists with 15 years experience.",
            contact: { phone: "+27123456791" },
            image: "/assets/images/construction-1.jpg"
          },
          {
            id: 4,
            name: "Celebration Events",
            category: "Events",
            district: "Mbombela",
            package_type: "Gold",
            rating: 4.9,
            description: "Full-service event planning for corporate and private functions.",
            contact: { phone: "+27123456792", email: "bookings@celebrationevents.co.za", website: "https://celebrationevents.co.za" },
            image: "/assets/images/events-1.jpg"
          },
          {
            id: 5,
            name: "Panorama Tours",
            category: "Tourism",
            district: "Mbombela",
            package_type: "Silver",
            rating: 4.6,
            description: "Guided tours of the Panorama Route, Blyde River Canyon and surrounding attractions.",
            contact: { phone: "+27123456793", email: "tours@panorama.co.za" },
            image: "/assets/images/tourism-2.jpg"
          },
          {
            id: 6,
            name: "Lowveld Farm Supply",
            category: "Agriculture",
            district: "Bushbuckridge",
            package_type: "Basic",
            rating: 3.9,
            description: "Agricultural equipment and supplies for small and large-scale farmers.",
            image: "/assets/images/agriculture-2.jpg"
          }
        ]
        
        console.log('Loading mock data:', mockData) // Debug log
        setBusinesses(mockData)
        setFilteredBusinesses(mockData)
      } finally {
        setLoading(false)
      }
    }
    
    fetchBusinesses()
  }, [selectedCategory, selectedDistrict])

  // Update URL when filters change
  useEffect(() => {
    const params = new URLSearchParams()
    if (searchTerm) params.set('search', searchTerm)
    if (selectedCategory) params.set('category', selectedCategory)
    if (selectedDistrict) params.set('district', selectedDistrict)
    setSearchParams(params)
  }, [searchTerm, selectedCategory, selectedDistrict, setSearchParams])

  // Client-side search with Fuse.js
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

  // Handle search input
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
      
      {/* Search and Filters */}
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
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          {/* Categories */}
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
          
          {/* Districts */}
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
        
        {/* Active filters */}
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
      
      {/* Business Listings */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {loading ? (
          // Loading skeleton
          Array.from({ length: 6 }).map((_, index) => (
            <div key={index} className="bg-white rounded-lg shadow-md overflow-hidden">
              <div className="h-48 bg-gray-200 animate-pulse"></div>
              <div className="p-4">
                <div className="h-6 bg-gray-200 rounded animate-pulse mb-2"></div>
                <div className="h-4 bg-gray-200 rounded animate-pulse mb-2 w-2/3"></div>
                <div className="h-4 bg-gray-200 rounded animate-pulse mb-2 w-1/2"></div>
              </div>
            </div>
          ))
        ) : error ? (
          // Error message
          <div className="col-span-3 text-center py-8">
            <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-red-800">{error}</h3>
            <p className="mt-1 text-gray-500">Using mock data instead. In production, this would connect to the backend API.</p>
          </div>
        ) : filteredBusinesses.length === 0 ? (
          // No results
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
          // Business cards
          filteredBusinesses.map(business => {
            console.log('Rendering business:', business) // Debug log
            return <BusinessCard key={business.id} business={business} />
          })
        )}
      </div>
    </div>
  )
}

export default BusinessDirectory
