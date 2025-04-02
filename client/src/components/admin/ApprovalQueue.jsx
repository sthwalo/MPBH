import { useEffect, useState } from 'react';
import api from '../../utils/api';
import { toast } from 'react-toastify';
import { format } from 'date-fns';
import { LazyLoadImage } from '../ui/LazyLoadImage';
import { getTierBadgeData } from '../../config/tierConfig';

/**
 * Admin approval queue component for managing pending business listings
 * Allows admins to review, approve, or reject business listings with comments
 */
export default function ApprovalQueue() {
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedListing, setSelectedListing] = useState(null);
  const [feedback, setFeedback] = useState('');
  const [showRejectionModal, setShowRejectionModal] = useState(false);
  const [filters, setFilters] = useState({
    category: '',
    district: '',
    sort: 'oldest'
  });

  // Fetch pending listings when component mounts or filters change
  useEffect(() => {
    fetchPendingListings();
  }, [filters]);

  const fetchPendingListings = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const params = new URLSearchParams();
      if (filters.category) params.append('category', filters.category);
      if (filters.district) params.append('district', filters.district);
      params.append('sort', filters.sort);
      
      const response = await api.get(`/admin/pending-listings?${params}`);
      setListings(response.data.data || []);
    } catch (err) {
      setError('Failed to fetch pending listings. Please try again.');
      console.error('Error fetching pending listings:', err);
      toast.error('Failed to load pending listings');
    } finally {
      setLoading(false);
    }
  };

  const handleApproval = async (id, approved) => {
    try {
      await api.post(`/admin/listings/${id}/approve`, { 
        status: approved ? 'verified' : 'rejected',
        feedback: approved ? '' : feedback
      });
      
      // Update local state
      setListings(listings.filter(listing => listing.id !== id));
      toast.success(`Business ${approved ? 'approved' : 'rejected'} successfully`);
      
      // Reset modal state if rejecting
      if (!approved) {
        setShowRejectionModal(false);
        setFeedback('');
        setSelectedListing(null);
      }
    } catch (err) {
      console.error('Error updating business status:', err);
      toast.error('Failed to update business status');
    }
  };

  const openRejectionModal = (listing) => {
    setSelectedListing(listing);
    setFeedback('');
    setShowRejectionModal(true);
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  if (loading && listings.length === 0) {
    return <div className="approval-queue loading">Loading pending listings...</div>;
  }

  if (error) {
    return (
      <div className="approval-queue error">
        <p>{error}</p>
        <button onClick={fetchPendingListings}>Try Again</button>
      </div>
    );
  }

  return (
    <div className="approval-queue">
      <h2>Pending Business Approvals ({listings.length})</h2>
      
      {/* Filtering options */}
      <div className="filters">
        <div className="filter-group">
          <label htmlFor="category">Category:</label>
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
          <label htmlFor="district">District:</label>
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
          <label htmlFor="sort">Sort By:</label>
          <select 
            id="sort" 
            name="sort" 
            value={filters.sort} 
            onChange={handleFilterChange}
          >
            <option value="oldest">Oldest First</option>
            <option value="newest">Newest First</option>
          </select>
        </div>
      </div>

      {listings.length === 0 ? (
        <div className="no-listings">
          <p>No pending business listings found.</p>
        </div>
      ) : (
        <div className="listings-grid">
          {listings.map(listing => {
            const tierData = getTierBadgeData(listing.package_type);
            return (
              <div key={listing.id} className="listing-card">
                <div className="listing-header">
                  <LazyLoadImage 
                    src={listing.logo || '/images/placeholder-logo.png'} 
                    alt={`${listing.name} logo`} 
                    className="listing-logo"
                    width="80"
                    height="80"
                    placeholderSrc="/images/placeholder-logo.png"
                  />
                  <div className="listing-title">
                    <h3>{listing.name}</h3>
                    <div className="listing-meta">
                      <span className="category">{listing.category}</span>
                      <span className="district">{listing.district}</span>
                      <span 
                        className="package-badge" 
                        style={{ backgroundColor: tierData.color }}
                      >
                        {tierData.displayName}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div className="listing-details">
                  <p className="description">{listing.description}</p>
                  <div className="contact-info">
                    <p><strong>Contact:</strong> {listing.contact_person}</p>
                    <p><strong>Email:</strong> {listing.email}</p>
                    <p><strong>Phone:</strong> {listing.phone}</p>
                  </div>
                  <div className="submission-info">
                    <p>
                      <strong>Submitted:</strong> {format(new Date(listing.created_at), 'PPP')}
                    </p>
                  </div>
                </div>
                
                <div className="listing-actions">
                  <button 
                    className="approve-btn"
                    onClick={() => handleApproval(listing.id, true)}
                  >
                    Approve
                  </button>
                  <button
                    className="reject-btn"
                    onClick={() => openRejectionModal(listing)}
                  >
                    Reject
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
      
      {/* Rejection modal */}
      {showRejectionModal && selectedListing && (
        <div className="modal-overlay">
          <div className="rejection-modal">
            <h3>Reject {selectedListing.name}</h3>
            <p>Please provide feedback for the business owner:</p>
            
            <textarea
              value={feedback}
              onChange={(e) => setFeedback(e.target.value)}
              placeholder="Explain why this listing is being rejected..."
              rows="4"
              required
            />
            
            <div className="modal-actions">
              <button 
                className="cancel-btn"
                onClick={() => setShowRejectionModal(false)}
              >
                Cancel
              </button>
              <button
                className="confirm-reject-btn"
                onClick={() => handleApproval(selectedListing.id, false)}
                disabled={feedback.trim().length === 0}
              >
                Confirm Rejection
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
