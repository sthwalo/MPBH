import { Link } from 'react-router-dom'

function BusinessCard({ business }) {
  const { id, name, category, district, package_type, rating, description, contact, image } = business

  // Function to determine what contact information is visible based on package tier
  const getVisibleContact = () => {
    // Basic tier doesn't show contact info
    if (package_type === 'Basic') {
      return null
    }
    
    return (
      <div className="mt-4 pt-4 border-t border-gray-200">
        {contact?.phone && (
          <div className="flex items-center mb-2">
            <svg className="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <a href={`tel:${contact.phone}`} className="text-gray-600 hover:text-gray-900">{contact.phone}</a>
          </div>
        )}
        
        {contact?.email && (
          <div className="flex items-center mb-2">
            <svg className="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <a href={`mailto:${contact.email}`} className="text-gray-600 hover:text-gray-900">{contact.email}</a>
          </div>
        )}
        
        {contact?.website && (
          <div className="flex items-center">
            <svg className="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <a href={contact.website} target="_blank" rel="noopener noreferrer" className="text-gray-600 hover:text-gray-900 truncate">
              {contact.website.replace(/(^https?:\/\/|\/$)/g, '')}
            </a>
          </div>
        )}
      </div>
    )
  }

  // Get the appropriate badge styling based on package type
  const getBadgeStyle = () => {
    switch (package_type) {
      case 'Gold':
        return 'badge-gold'
      case 'Silver':
        return 'badge-silver'
      case 'Bronze':
        return 'badge-bronze'
      default:
        return 'badge-basic'
    }
  }

  return (
    <div className="card overflow-hidden">
      {/* Image */}
      <div className="relative h-48 bg-gray-200">
        {image ? (
          <img src={image} alt={name} className="w-full h-full object-cover" />
        ) : (
          <div className="flex items-center justify-center h-full bg-gray-100">
            <svg className="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        )}
        
        {/* Package badge */}
        <div className={`absolute top-2 right-2 badge ${getBadgeStyle()}`}>
          {package_type}
        </div>
        
        {/* Category badge */}
        <div className="absolute bottom-2 left-2 badge bg-blue-600 text-white">
          {category}
        </div>
      </div>
      
      {/* Content */}
      <div className="p-5">
        <div className="flex justify-between items-start mb-2">
          <h3 className="font-bold text-xl">{name}</h3>
          
          {/* Rating */}
          <div className="flex items-center">
            <svg className="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span className="ml-1 text-sm font-semibold">{rating?.toFixed(1) || 'N/A'}</span>
          </div>
        </div>
        
        <div className="text-sm text-gray-500 mb-2">
          {district}
        </div>
        
        <p className="text-gray-700">
          {description?.substring(0, 120)}{description?.length > 120 ? '...' : ''}
        </p>
        
        {/* Contact info (only for Bronze tier and above) */}
        {getVisibleContact()}
        
        {/* View Details button */}
        <div className="mt-4">
          <Link 
            to={`/business/${id}`} 
            className="w-full block text-center py-2 px-4 border border-blue-600 rounded text-blue-600 font-medium hover:bg-blue-600 hover:text-white transition-colors"
          >
            View Details
          </Link>
        </div>
      </div>
    </div>
  )
}

export default BusinessCard
