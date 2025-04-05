import { useState, useEffect, useCallback } from 'react';
import { initFuse, formatSearchResults } from '../../utils/fuseConfig';
import { LazyLoadImage } from '../ui/LazyLoadImage';
import { useNavigate } from 'react-router-dom';
import { debounce } from 'lodash';
import api from '../../utils/api';

/**
 * Advanced business search component using Fuse.js for fuzzy searching
 * Provides instant search results with filtering and sorting options
 */
function BusinessSearch() {
  const [query, setQuery] = useState('');
  const [businesses, setBusinesses] = useState([]);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(true);
  const [fuseInstance, setFuseInstance] = useState(null);
  const [filters, setFilters] = useState({
    category: '',
    district: '',
    packageType: ''
  });
  const navigate = useNavigate();

  // Fetch all businesses on component mount
  useEffect(() => {
    const fetchBusinesses = async () => {
      try {
        setLoading(true);
        const response = await api.get('/businesses');
        if (response.data && response.data.data && response.data.data.businesses) {
          setBusinesses(response.data.data.businesses);
        } else {
          // Fallback to mock data if API response format is unexpected
          console.warn('API response format unexpected, using mock data');
          // You can import mock data or use a fallback array here
          setBusinesses([]);
        }
      } catch (error) {
        console.error('Error fetching businesses:', error);
        // Handle the error gracefully with fallback mock data
        console.warn('API request failed, using mock data instead. In production, this would connect to the backend API.');
        setBusinesses([]);
      } finally {
        setLoading(false);
      }
    };

    fetchBusinesses();
  }, []);

  // Initialize Fuse instance when businesses data is available
  useEffect(() => {
    if (businesses.length > 0) {
      initFuse(businesses).then(fuse => {
        setFuseInstance(fuse);
      });
    }
  }, [businesses]);

  // Log search query for analytics (debounced)
  const logSearch = useCallback(
    debounce((searchQuery) => {
      if (searchQuery.trim().length > 2) {
        api.post('/search/log', { query: searchQuery.trim() })
          .catch(err => console.error('Error logging search:', err));
      }
    }, 1000),
    []
  );

  // Perform search when query or filters change
  useEffect(() => {
    if (!fuseInstance || query.trim().length < 2) {
      // Apply only filters if query is empty
      if (query.trim().length === 0) {
        let filteredResults = [...businesses];
        
        // Apply category filter
        if (filters.category) {
          filteredResults = filteredResults.filter(
            business => business.category === filters.category
          );
        }
        
        // Apply district filter
        if (filters.district) {
          filteredResults = filteredResults.filter(
            business => business.district === filters.district
          );
        }
        
        // Apply package type filter
        if (filters.packageType) {
          filteredResults = filteredResults.filter(
            business => business.package_type === filters.packageType
          );
        }
        
        setResults(filteredResults);
      } else {
        setResults([]);
      }
      return;
    }
    
    // Log search for analytics
    logSearch(query);
    
    // Perform search with Fuse.js
    let searchResults = fuseInstance.search(query);
    let formattedResults = formatSearchResults(searchResults);
    
    // Apply filters to search results
    if (filters.category) {
      formattedResults = formattedResults.filter(
        result => result.category === filters.category
      );
    }
    
    if (filters.district) {
      formattedResults = formattedResults.filter(
        result => result.district === filters.district
      );
    }
    
    if (filters.packageType) {
      formattedResults = formattedResults.filter(
        result => result.package_type === filters.packageType
      );
    }
    
    setResults(formattedResults);
  }, [query, fuseInstance, filters, businesses, logSearch]);

  const handleInputChange = (e) => {
    setQuery(e.target.value);
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const navigateToBusinessDetail = (id) => {
    navigate(`/businesses/${id}`);
  };

  return (
    <div className="business-search">
      <div className="search-bar">
        <input
          type="search"
          value={query}
          onChange={handleInputChange}
          placeholder="Search businesses by name, category, or keywords..."
          aria-label="Search businesses"
        />
      </div>
      
      <div className="filter-controls">
        <div className="filter-group">
          <label htmlFor="category">Category</label>
          <select 
            id="category" 
            name="category" 
            value={filters.category} 
            onChange={handleFilterChange}
          >
            <option value="">All Categories</option>
            <option value="Tourism">Tourism</option>
            <option value="Agriculture">Agriculture</option>
            <option value="Construction">Construction</option>
            <option value="Events">Events</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Services">Services</option>
            <option value="Retail">Retail</option>
          </select>
        </div>
        
        <div className="filter-group">
          <label htmlFor="district">District</label>
          <select 
            id="district" 
            name="district" 
            value={filters.district} 
            onChange={handleFilterChange}
          >
            <option value="">All Districts</option>
            <option value="Ehlanzeni">Ehlanzeni</option>
            <option value="Gert Sibande">Gert Sibande</option>
            <option value="Nkangala">Nkangala</option>
          </select>
        </div>
        
        <div className="filter-group">
          <label htmlFor="packageType">Package Type</label>
          <select 
            id="packageType" 
            name="packageType" 
            value={filters.packageType} 
            onChange={handleFilterChange}
          >
            <option value="">All Packages</option>
            <option value="Basic">Basic</option>
            <option value="Bronze">Bronze</option>
            <option value="Silver">Silver</option>
            <option value="Gold">Gold</option>
          </select>
        </div>
      </div>
      
      <div className="search-results">
        <h3>
          {query ? `Search Results for "${query}"` : 'All Businesses'}
          {results.length > 0 && <span className="result-count">({results.length})</span>}
        </h3>
        
        {loading ? (
          <div className="loading-state">Loading businesses...</div>
        ) : results.length > 0 ? (
          <div className="result-grid">
            {results.map(business => (
              <div 
                key={business.id} 
                className="business-card" 
                onClick={() => navigateToBusinessDetail(business.id)}
              >
                <div className="business-image">
                  <LazyLoadImage
                    src={business.logo || '/images/placeholder-logo.png'}
                    alt={`${business.name} logo`}
                    width="100"
                    height="100"
                    placeholderSrc="/images/placeholder-logo.png"
                    effect="blur"
                  />
                </div>
                
                <div className="business-info">
                  <h4>{business.name}</h4>
                  <div className="business-meta">
                    <span className="category">{business.category}</span>
                    <span className="district">{business.district}</span>
                  </div>
                  <p className="description">
                    {business.description.length > 100 ? 
                      `${business.description.substring(0, 100)}...` : 
                      business.description}
                  </p>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="no-results">
            {query ? 
              <p>No businesses found matching "{query}". Try a different search term or adjusting filters.</p> : 
              <p>No businesses found with the selected filters.</p>
            }
          </div>
        )}
      </div>
    </div>
  );
}

export default BusinessSearch;
