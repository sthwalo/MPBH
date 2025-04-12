import { Link } from 'react-router-dom'
import PropTypes from 'prop-types'

function BusinessCard({ business }) {
  console.log('BusinessCard props:', business) // Debug log
  const { id, name, category, district, package_type, rating, description, contact, image } = business

  // Define tier badge styles in elegant black and white theme
  const tierBadgeStyles = {
    Gold: 'bg-brand-gray-200 text-brand-black',
    Silver: 'bg-brand-gray-300 text-brand-black',
    Bronze: 'bg-brand-gray-500 text-brand-white',
    Basic: 'bg-brand-gray-700 text-brand-white'
  };

  // Function to determine what contact information is visible based on package tier
  const getVisibleContact = () => {
    // Basic tier doesn't show contact info
    if (package_type === 'Basic') {
      return null
    }
    
    return (
      <div className="mt-4 pt-4 border-t border-brand-gray-200">
        {contact?.phone && (
          <div className="flex items-center mb-2">
            <svg className="h-5 w-5 text-brand-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <a href={`tel:${contact.phone}`} className="text-brand-gray-600 hover:text-brand-black transition-colors">{contact.phone}</a>
          </div>
        )}
        
        {contact?.email && (
          <div className="flex items-center mb-2">
            <svg className="h-5 w-5 text-brand-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <a href={`mailto:${contact.email}`} className="text-brand-gray-600 hover:text-brand-black transition-colors">{contact.email}</a>
          </div>
        )}
        
        {contact?.website && (
          <div className="flex items-center">
            <svg className="h-5 w-5 text-brand-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <a href={contact.website} target="_blank" rel="noopener noreferrer" className="text-brand-gray-600 hover:text-brand-black transition-colors truncate">
              {contact.website.replace(/(^https?:\/\/|\/$)/g, '')}
            </a>
          </div>
        )}
      </div>
    )
  }

  // Render premium features based on tier
  const renderPremiumFeatures = () => {
    if (package_type === 'Basic') {
      return null;
    }
    
    return (
      <div className="premium-features mt-3">
        {/* Website link - available for Bronze, Silver, Gold */}
        {contact?.website && (
          <a 
            href={contact.website} 
            target="_blank" 
            rel="noopener noreferrer" 
            className="inline-flex items-center mr-3 text-sm text-brand-gray-600 hover:text-brand-black transition-colors"
          >
            <svg className="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
              <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
            </svg>
            Website
          </a>
        )}
        
        {/* WhatsApp button - available for Bronze, Silver, Gold */}
        {contact?.whatsapp && (
          <a 
            href={`https://wa.me/${contact.whatsapp.replace(/[^0-9]/g, '')}`} 
            target="_blank" 
            rel="noopener noreferrer" 
            className="inline-flex items-center text-sm text-brand-gray-600 hover:text-brand-black transition-colors"
          >
            <svg className="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
              <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-18.5c-4.694 0-8.5 3.806-8.5 8.5s3.806 8.5 8.5 8.5 8.5-3.806 8.5-8.5S16.694 3.5 12 3.5z" />
            </svg>
            WhatsApp
          </a>
        )}
      </div>
    );
  }

  return (
    <div className="rounded-lg overflow-hidden shadow-brand-md hover:shadow-brand-lg transition-all duration-300 border border-brand-gray-200">
      {/* Image */}
      <div className="relative h-48 bg-brand-gray-200">
        {image ? (
          <img src={image} alt={name} className="w-full h-full object-cover" />
        ) : (
          <div className="flex items-center justify-center h-full bg-brand-gray-100">
            <svg className="h-12 w-12 text-brand-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        )}
        
        {/* Package badge */}
        <div className={`absolute top-2 right-2 px-2 py-1 rounded text-xs font-medium ${tierBadgeStyles[package_type] || tierBadgeStyles.Basic}`}>
          {package_type}
        </div>
        
        {/* Category badge */}
        <div className="absolute bottom-2 left-2 px-2 py-1 rounded text-xs font-medium bg-brand-black text-brand-white">
          {category}
        </div>
      </div>
      
      {/* Content */}
      <div className="p-5">
        <div className="flex justify-between items-start mb-2">
          <h3 className="font-bold text-xl font-serif text-brand-black">{name}</h3>
          
          {/* Rating */}
          <div className="flex items-center">
            <svg className="h-5 w-5 text-brand-gray-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span className="ml-1 text-sm font-semibold text-brand-gray-800">{rating?.toFixed(1) || 'N/A'}</span>
          </div>
        </div>
        
        <div className="text-sm text-brand-gray-500 mb-2">
          {district}
        </div>
        
        <p className="text-brand-gray-600 line-clamp-2 mb-2">{description}</p>
        
        {/* Premium features */}
        {renderPremiumFeatures()}
        
        <div className="mt-4 flex justify-between items-center">
          <Link to={`/business/${id}`} className="text-brand-black hover:text-brand-gray-700 transition-colors font-medium">
            View Details
          </Link>
          
          {/* Special badge for Gold tier */}
          {package_type === 'Gold' && (
            <span className="text-xs bg-brand-gray-200 px-2 py-1 rounded font-medium text-brand-black">
              Featured
            </span>
          )}
        </div>
        
        {/* Contact information */}
        {getVisibleContact()}
      </div>
    </div>
  )
}

BusinessCard.propTypes = {
  business: PropTypes.shape({
    id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    name: PropTypes.string.isRequired,
    category: PropTypes.string.isRequired,
    district: PropTypes.string,
    package_type: PropTypes.string.isRequired,
    rating: PropTypes.number,
    description: PropTypes.string,
    contact: PropTypes.object,
    image: PropTypes.string
  }).isRequired
}

export default BusinessCard
