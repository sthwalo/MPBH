import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'

function BusinessDetails() {
  const { id } = useParams()
  const [business, setBusiness] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [userRating, setUserRating] = useState(0)

  useEffect(() => {
    const fetchBusinessDetails = async () => {
      try {
        setLoading(true)
        // In production, this would fetch from the API
        // const response = await fetch(`/api/businesses/${id}`)
        // if (!response.ok) throw new Error('Business not found')
        // const data = await response.json()
        
        // Mock data for development
        setTimeout(() => {
          const mockData = {
            id: parseInt(id),
            name: "Kruger Gateway Lodge",
            category: "Tourism",
            district: "Mbombela",
            package_type: "Gold",
            rating: 4.8,
            description: "Luxury accommodation near Kruger National Park with guided safari tours. Our lodge offers comfortable rooms, a restaurant serving local cuisine, and a pool overlooking the bush. We organize daily game drives into Kruger National Park with experienced guides who know where to find the Big Five. Our location just 10 minutes from Numbi Gate makes us the ideal base for your safari adventure.",
            contact: { 
              phone: "+27123456789", 
              email: "info@krugergateway.co.za", 
              website: "https://krugergateway.co.za",
              address: "123 Safari Road, Hazyview, Mpumalanga",
              whatsapp: "+27123456789"
            },
            images: [
              "/assets/images/tourism-1.jpg",
              "/assets/images/tourism-2.jpg",
              "/assets/images/tourism-3.jpg"
            ],
            products: [
              {
                id: 1,
                name: "Safari Day Trip",
                price: 1200,
                description: "Full day game drive in Kruger National Park with lunch included."
              },
              {
                id: 2,
                name: "Panorama Route Tour",
                price: 950,
                description: "Guided tour of Blyde River Canyon, God's Window, and waterfalls."
              },
              {
                id: 3,
                name: "Bush Dinner Experience",
                price: 650,
                description: "Evening dinner in the bush with traditional music and dancing."
              }
            ],
            hours: {
              monday: "8:00 - 18:00",
              tuesday: "8:00 - 18:00",
              wednesday: "8:00 - 18:00",
              thursday: "8:00 - 18:00",
              friday: "8:00 - 18:00",
              saturday: "9:00 - 17:00",
              sunday: "9:00 - 16:00"
            },
            reviews: [
              {
                id: 1,
                user: "Sarah M.",
                rating: 5,
                comment: "Amazing experience! The safari guides were knowledgeable and we saw all the Big Five.",
                date: "2023-08-15"
              },
              {
                id: 2,
                user: "David K.",
                rating: 4,
                comment: "Great accommodation and friendly staff. The food was excellent.",
                date: "2023-07-22"
              },
              {
                id: 3,
                user: "Thabo N.",
                rating: 5,
                comment: "Perfect location near Kruger. The rooms were clean and comfortable.",
                date: "2023-06-30"
              }
            ],
            location: {
              lat: -25.052,
              lng: 31.132
            }
          }
          setBusiness(mockData)
          setLoading(false)
        }, 1000) // Simulate API delay
        
      } catch (err) {
        setError(err.message)
        setLoading(false)
      }
    }
    
    fetchBusinessDetails()
    
    // Scroll to top when component mounts
    window.scrollTo(0, 0)
  }, [id])

  const handleRatingSubmit = async (rating) => {
    setUserRating(rating)
    // In production, this would send to the API
    // await fetch('/api/ratings', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({ business_id: id, rating })
    // })
    
    // For demo, just show a success message
    alert(`Thank you for rating ${business.name} ${rating} stars!`)
  }

  // Determine what features to show based on package tier
  const canShowProducts = business?.package_type === 'Silver' || business?.package_type === 'Gold'
  const canShowContactInfo = business?.package_type !== 'Basic'
  
  // Function to render star rating selector
  const RatingStars = () => (
    <div className="flex items-center">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          onClick={() => handleRatingSubmit(star)}
          className="text-gray-300 hover:text-yellow-400 focus:outline-none"
        >
          <svg 
            className={`h-8 w-8 ${userRating >= star ? 'text-yellow-400' : ''}`}
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </button>
      ))}
    </div>
  )

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/3 mb-6"></div>
          <div className="h-64 bg-gray-200 rounded mb-6"></div>
          <div className="h-4 bg-gray-200 rounded w-2/3 mb-4"></div>
          <div className="h-4 bg-gray-200 rounded mb-4"></div>
          <div className="h-4 bg-gray-200 rounded w-1/2 mb-8"></div>
          <div className="h-32 bg-gray-200 rounded mb-6"></div>
        </div>
      </div>
    )
  }

  if (error || !business) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <h2 className="text-2xl font-bold text-red-600 mb-4">Error Loading Business</h2>
        <p className="mb-6">{error || 'Business not found'}</p>
        <Link to="/directory" className="btn btn-primary">Return to Directory</Link>
      </div>
    )
  }

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Back Button */}
      <Link to="/directory" className="flex items-center text-blue-600 mb-6 hover:text-blue-800">
        <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Directory
      </Link>
      
      {/* Business Header */}
      <div className="flex flex-col lg:flex-row mb-8 gap-8">
        <div className="lg:w-2/3">
          {/* Image Gallery */}
          <div className="bg-white rounded-lg overflow-hidden shadow-md">
            <div className="relative h-96">
              <img 
                src={business.images?.[0] || '/assets/images/placeholder.jpg'} 
                alt={business.name} 
                className="w-full h-full object-cover"
              />
              <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6">
                <div className="flex items-center mb-2">
                  <span className={`badge ${business.package_type === 'Gold' ? 'badge-gold' : business.package_type === 'Silver' ? 'badge-silver' : business.package_type === 'Bronze' ? 'badge-bronze' : 'badge-basic'}`}>
                    {business.package_type}
                  </span>
                  <span className="badge bg-blue-600 text-white ml-2">{business.category}</span>
                </div>
                <h1 className="text-3xl font-bold text-white">{business.name}</h1>
                <div className="flex items-center text-white mt-2">
                  <div className="flex items-center mr-4">
                    <svg className="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span className="ml-1">{business.rating.toFixed(1)} ({business.reviews?.length || 0} reviews)</span>
                  </div>
                  <div className="text-white">{business.district}</div>
                </div>
              </div>
            </div>
            
            {/* Thumbnail Gallery */}
            {business.images?.length > 1 && (
              <div className="flex p-4 space-x-2 overflow-x-auto">
                {business.images.map((image, index) => (
                  <div key={index} className="flex-shrink-0 w-24 h-24 cursor-pointer rounded overflow-hidden">
                    <img src={image} alt={`${business.name} ${index + 1}`} className="w-full h-full object-cover" />
                  </div>
                ))}
              </div>
            )}
          </div>
          
          {/* Description */}
          <div className="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 className="text-2xl font-bold mb-4">About {business.name}</h2>
            <p className="text-gray-700 mb-6 whitespace-pre-line">{business.description}</p>
            
            {/* Rate this business */}
            <div className="mt-8 border-t pt-6">
              <h3 className="text-lg font-semibold mb-2">Rate this business</h3>
              <div className="flex items-center">
                <RatingStars />
                <span className="ml-2 text-sm text-gray-500">
                  {userRating > 0 ? `You rated: ${userRating} stars` : 'Select rating'}
                </span>
              </div>
            </div>
          </div>
          
          {/* Products/Services - Only for Silver and Gold tiers */}
          {canShowProducts && business.products?.length > 0 && (
            <div className="mt-8 bg-white rounded-lg shadow-md p-6">
              <h2 className="text-2xl font-bold mb-6">Products & Services</h2>
              <div className="grid md:grid-cols-2 gap-6">
                {business.products.map(product => (
                  <div key={product.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 className="font-bold text-lg mb-1">{product.name}</h3>
                    <div className="text-lg font-medium text-green-600 mb-2">R{product.price.toFixed(2)}</div>
                    <p className="text-gray-600">{product.description}</p>
                    <button className="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors w-full">
                      Contact for Details
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}
          
          {/* Reviews */}
          <div className="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 className="text-2xl font-bold mb-6">Customer Reviews</h2>
            {business.reviews?.length > 0 ? (
              <div className="space-y-6">
                {business.reviews.map(review => (
                  <div key={review.id} className="border-b pb-6 last:border-b-0 last:pb-0">
                    <div className="flex justify-between items-start mb-2">
                      <div>
                        <div className="font-semibold">{review.user}</div>
                        <div className="text-gray-500 text-sm">{new Date(review.date).toLocaleDateString()}</div>
                      </div>
                      <div className="flex items-center">
                        <div className="text-yellow-400">
                          {[...Array(5)].map((_, i) => (
                            <span key={i}>
                              {i < review.rating ? 'u2605' : 'u2606'}
                            </span>
                          ))}
                        </div>
                      </div>
                    </div>
                    <p className="text-gray-700">{review.comment}</p>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-gray-500">No reviews yet. Be the first to review this business!</p>
            )}
          </div>
        </div>
        
        {/* Business Info Sidebar */}
        <div className="lg:w-1/3">
          {/* Contact Information - Only for Bronze tier and above */}
          {canShowContactInfo && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Contact Information</h2>
              <div className="space-y-4">
                {business.contact?.phone && (
                  <div className="flex">
                    <svg className="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <div>
                      <div className="text-sm text-gray-500">Phone</div>
                      <a href={`tel:${business.contact.phone}`} className="text-blue-600 hover:text-blue-800">
                        {business.contact.phone}
                      </a>
                    </div>
                  </div>
                )}
                
                {business.contact?.email && (
                  <div className="flex">
                    <svg className="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <div>
                      <div className="text-sm text-gray-500">Email</div>
                      <a href={`mailto:${business.contact.email}`} className="text-blue-600 hover:text-blue-800 break-all">
                        {business.contact.email}
                      </a>
                    </div>
                  </div>
                )}
                
                {business.contact?.website && (
                  <div className="flex">
                    <svg className="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <div>
                      <div className="text-sm text-gray-500">Website</div>
                      <a href={business.contact.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800 break-all">
                        {business.contact.website.replace(/(^https?:\/\/|\/$)/g, '')}
                      </a>
                    </div>
                  </div>
                )}
                
                {business.contact?.whatsapp && (
                  <div className="flex">
                    <svg className="h-6 w-6 text-gray-500 mr-3" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    <div>
                      <div className="text-sm text-gray-500">WhatsApp</div>
                      <a href={`https://wa.me/${business.contact.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800">
                        {business.contact.whatsapp}
                      </a>
                    </div>
                  </div>
                )}
                
                {business.contact?.address && (
                  <div className="flex">
                    <svg className="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div>
                      <div className="text-sm text-gray-500">Address</div>
                      <address className="not-italic">{business.contact.address}</address>
                    </div>
                  </div>
                )}
              </div>
              
              {/* Call to action buttons */}
              <div className="mt-6 space-y-3">
                {business.contact?.phone && (
                  <a 
                    href={`tel:${business.contact.phone}`} 
                    className="btn btn-primary w-full flex items-center justify-center"
                  >
                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Call Now
                  </a>
                )}
                
                {business.contact?.email && (
                  <a 
                    href={`mailto:${business.contact.email}`} 
                    className="btn btn-outline w-full flex items-center justify-center"
                  >
                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                  </a>
                )}
              </div>
            </div>
          )}
          
          {/* Business Hours */}
          {business.hours && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Business Hours</h2>
              <div className="space-y-2">
                {Object.entries(business.hours).map(([day, hours]) => (
                  <div key={day} className="flex justify-between items-center">
                    <div className="capitalize">{day}</div>
                    <div className="font-medium">{hours}</div>
                  </div>
                ))}
              </div>
            </div>
          )}
          
          {/* Location Map */}
          {business.location && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Location</h2>
              <div className="h-64 bg-gray-200 rounded-lg mb-4">
                {/* In production, this would be a Google Maps component */}
                <div className="flex items-center justify-center h-full">
                  <p className="text-gray-500">Map would display here</p>
                </div>
              </div>
              {business.contact?.address && (
                <address className="not-italic text-gray-700">{business.contact.address}</address>
              )}
              {canShowContactInfo && business.location && (
                <a 
                  href={`https://www.google.com/maps/search/?api=1&query=${business.location.lat},${business.location.lng}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="mt-4 inline-block text-blue-600 hover:text-blue-800"
                >
                  Get Directions
                </a>
              )}
            </div>
          )}
          
          {/* Membership Upgrade Prompt (for Basic tier) */}
          {!canShowContactInfo && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
              <h3 className="text-lg font-bold text-yellow-800 mb-2">Contact Information Hidden</h3>
              <p className="text-yellow-700 mb-4">This business is on a Basic membership plan. Contact information is only visible for Bronze tier and above.</p>
              <Link to="/pricing" className="btn btn-primary w-full">View Membership Options</Link>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default BusinessDetails
