import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ApiErrorBoundary from '../../errors/ApiErrorBoundary';

/**
 * Admin Panel component for business approval workflow
 * Allows administrators to review and approve/reject business listings
 */
const AdminPanel = () => {
  const [pendingBusinesses, setPendingBusinesses] = useState([]);
  const [statistics, setStatistics] = useState({
    businesses: { total: 0, verified: 0, pending: 0, rejected: 0 },
    packages: { basic: 0, silver: 0, gold: 0 },
    payments: { recent: [], total_revenue: 0 }
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeTab, setActiveTab] = useState('pending');
  const navigate = useNavigate();

  // Fetch pending businesses and statistics on component mount
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        // Fetch pending businesses
        const pendingResponse = await fetch('/api/admin/businesses/pending', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (!pendingResponse.ok) {
          throw new Error('Failed to fetch pending businesses');
        }
        
        const pendingData = await pendingResponse.json();
        setPendingBusinesses(pendingData.data);
        
        // Fetch admin dashboard statistics
        const statsResponse = await fetch('/api/admin/dashboard', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (!statsResponse.ok) {
          throw new Error('Failed to fetch admin statistics');
        }
        
        const statsData = await statsResponse.json();
        setStatistics(statsData.data);
      } catch (err) {
        console.error('Error fetching admin data:', err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    
    fetchData();
  }, []);

  // Handle business approval/rejection
  const handleStatusUpdate = async (businessId, status) => {
    try {
      const response = await fetch(`/api/admin/businesses/${businessId}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ status })
      });
      
      if (!response.ok) {
        throw new Error(`Failed to ${status === 'verified' ? 'approve' : 'reject'} business`);
      }
      
      // Remove the business from pending list
      setPendingBusinesses(prev => prev.filter(business => business.id !== businessId));
      
      // Update statistics
      setStatistics(prev => ({
        ...prev,
        businesses: {
          ...prev.businesses,
          pending: prev.businesses.pending - 1,
          [status]: prev.businesses[status] + 1
        }
      }));
      
      // Show success message
      alert(`Business ${status === 'verified' ? 'approved' : 'rejected'} successfully`);
    } catch (err) {
      console.error('Error updating business status:', err);
      alert(err.message);
    }
  };

  // View business details
  const viewBusinessDetails = (businessId) => {
    navigate(`/admin/businesses/${businessId}`);
  };

  if (loading) {
    return <div className="admin-loading">Loading admin dashboard...</div>;
  }

  if (error) {
    return (
      <div className="admin-error">
        <h2>Error loading admin dashboard</h2>
        <p>{error}</p>
        <button onClick={() => window.location.reload()}>Try Again</button>
      </div>
    );
  }

  return (
    <ApiErrorBoundary>
      <div className="admin-panel">
        <h1>Admin Dashboard</h1>
        
        {/* Statistics Summary */}
        <div className="stats-summary">
          <div className="stat-card">
            <h3>Businesses</h3>
            <div className="stat-grid">
              <div className="stat-item">
                <span className="stat-value">{statistics.businesses.total}</span>
                <span className="stat-label">Total</span>
              </div>
              <div className="stat-item">
                <span className="stat-value">{statistics.businesses.verified}</span>
                <span className="stat-label">Verified</span>
              </div>
              <div className="stat-item">
                <span className="stat-value">{statistics.businesses.pending}</span>
                <span className="stat-label">Pending</span>
              </div>
              <div className="stat-item">
                <span className="stat-value">{statistics.businesses.rejected}</span>
                <span className="stat-label">Rejected</span>
              </div>
            </div>
          </div>
          
          <div className="stat-card">
            <h3>Package Distribution</h3>
            <div className="stat-grid">
              <div className="stat-item">
                <span className="stat-value">{statistics.packages.basic}</span>
                <span className="stat-label">Basic</span>
              </div>
              <div className="stat-item">
                <span className="stat-value">{statistics.packages.silver}</span>
                <span className="stat-label">Silver</span>
              </div>
              <div className="stat-item">
                <span className="stat-value">{statistics.packages.gold}</span>
                <span className="stat-label">Gold</span>
              </div>
            </div>
          </div>
          
          <div className="stat-card">
            <h3>Revenue</h3>
            <div className="stat-revenue">
              <span className="stat-currency">R</span>
              <span className="stat-amount">
                {new Intl.NumberFormat('en-ZA').format(statistics.payments.total_revenue)}
              </span>
            </div>
          </div>
        </div>
        
        {/* Tabs Navigation */}
        <div className="admin-tabs">
          <button 
            className={`tab-button ${activeTab === 'pending' ? 'active' : ''}`}
            onClick={() => setActiveTab('pending')}
          >
            Pending Approvals ({pendingBusinesses.length})
          </button>
          <button 
            className={`tab-button ${activeTab === 'payments' ? 'active' : ''}`}
            onClick={() => setActiveTab('payments')}
          >
            Recent Payments
          </button>
        </div>
        
        {/* Pending Businesses Approval Queue */}
        {activeTab === 'pending' && (
          <div className="approval-queue">
            <h2>Pending Businesses</h2>
            
            {pendingBusinesses.length === 0 ? (
              <div className="empty-state">
                <p>No pending businesses to approve</p>
              </div>
            ) : (
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Business Name</th>
                    <th>Category</th>
                    <th>District</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {pendingBusinesses.map(business => (
                    <tr key={business.id}>
                      <td>{business.name}</td>
                      <td>{business.category}</td>
                      <td>{business.district}</td>
                      <td>{new Date(business.created_at).toLocaleDateString()}</td>
                      <td className="action-buttons">
                        <button 
                          className="view-button"
                          onClick={() => viewBusinessDetails(business.id)}
                        >
                          View
                        </button>
                        <button 
                          className="approve-button"
                          onClick={() => handleStatusUpdate(business.id, 'verified')}
                        >
                          Approve
                        </button>
                        <button 
                          className="reject-button"
                          onClick={() => handleStatusUpdate(business.id, 'rejected')}
                        >
                          Reject
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
        
        {/* Recent Payments */}
        {activeTab === 'payments' && (
          <div className="recent-payments">
            <h2>Recent Payments</h2>
            
            {statistics.payments.recent.length === 0 ? (
              <div className="empty-state">
                <p>No recent payments</p>
              </div>
            ) : (
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Business</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  {statistics.payments.recent.map(payment => (
                    <tr key={payment.id}>
                      <td>{payment.business_name}</td>
                      <td>{payment.reference}</td>
                      <td>R {payment.amount.toFixed(2)}</td>
                      <td>
                        <span className={`payment-type ${payment.payment_type}`}>
                          {payment.payment_type}
                        </span>
                      </td>
                      <td>
                        <span className={`payment-status ${payment.status}`}>
                          {payment.status}
                        </span>
                      </td>
                      <td>{new Date(payment.created_at).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </div>
    </ApiErrorBoundary>
  );
};

export default AdminPanel;
