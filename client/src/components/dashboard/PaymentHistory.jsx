import { useState, useEffect } from 'react'

function PaymentHistory({ businessData }) {
  const [payments, setPayments] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // In production, this would fetch the payment history from the API
    // const fetchPayments = async () => {
    //   try {
    //     const response = await fetch('/api/payments/history', {
    //       headers: {
    //         'Authorization': `Bearer ${localStorage.getItem('token')}`
    //       }
    //     })
    //     if (!response.ok) throw new Error('Failed to fetch payment history')
    //     const data = await response.json()
    //     setPayments(data)
    //   } catch (error) {
    //     console.error('Error fetching payment history:', error)
    //   } finally {
    //     setLoading(false)
    //   }
    // }
    // 
    // fetchPayments()
    
    // For demo, we'll use mock data
    setTimeout(() => {
      setPayments([
        {
          id: 'INV-2025-003',
          date: '2025-03-01',
          amount: 1000,
          status: 'paid',
          package: 'Gold',
          paymentMethod: 'PayFast',
          billingPeriod: 'Mar 1, 2025 - Mar 31, 2025'
        },
        {
          id: 'INV-2025-002',
          date: '2025-02-01',
          amount: 1000,
          status: 'paid',
          package: 'Gold',
          paymentMethod: 'PayFast',
          billingPeriod: 'Feb 1, 2025 - Feb 28, 2025'
        },
        {
          id: 'INV-2025-001',
          date: '2025-01-01',
          amount: 1000,
          status: 'paid',
          package: 'Gold',
          paymentMethod: 'PayFast',
          billingPeriod: 'Jan 1, 2025 - Jan 31, 2025'
        },
        {
          id: 'INV-2024-012',
          date: '2024-12-01',
          amount: 500,
          status: 'paid',
          package: 'Silver',
          paymentMethod: 'PayFast',
          billingPeriod: 'Dec 1, 2024 - Dec 31, 2024'
        }
      ])
      setLoading(false)
    }, 1000)
  }, [])

  // Get status badge styling
  const getStatusBadge = (status) => {
    switch (status) {
      case 'paid':
        return 'bg-green-100 text-green-800'
      case 'pending':
        return 'bg-yellow-100 text-yellow-800'
      case 'failed':
        return 'bg-red-100 text-red-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Payment History</h2>
      
      {/* Subscription Status Summary */}
      <div className="bg-white border rounded-lg p-6 mb-6">
        <h3 className="text-lg font-bold mb-4">Current Subscription</h3>
        <div className="flex flex-col md:flex-row md:justify-between">
          <div>
            <div className="flex items-center text-lg font-medium">
              <span className={`inline-block w-3 h-3 rounded-full mr-2 ${businessData.subscription.status === 'active' ? 'bg-green-500' : 'bg-red-500'}`}></span>
              {businessData.package_type} Package - R{businessData.subscription.amount}/month
            </div>
            <p className="text-gray-600 mt-1">
              Next billing date: {new Date(businessData.subscription.next_billing_date).toLocaleDateString()}
            </p>
          </div>
          <div className="mt-4 md:mt-0">
            <button className="btn btn-outline">
              Download Invoice
            </button>
          </div>
        </div>
      </div>

      {/* Payment History Table */}
      <div className="bg-white border rounded-lg overflow-hidden">
        <h3 className="text-lg font-bold p-6 border-b">Transaction History</h3>
        
        {loading ? (
          <div className="text-center py-10">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p className="mt-3 text-gray-500">Loading payment history...</p>
          </div>
        ) : payments.length === 0 ? (
          <div className="text-center py-10">
            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-gray-900">No payment history yet</h3>
            <p className="mt-1 text-gray-500">Your payment history will appear here once you make a payment.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Invoice #
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Package
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {payments.map(payment => (
                  <tr key={payment.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {payment.id}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {new Date(payment.date).toLocaleDateString()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {payment.package}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      R{payment.amount.toFixed(2)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadge(payment.status)}`}>
                        {payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <button className="text-blue-600 hover:text-blue-800 mr-3">
                        <span className="flex items-center">
                          <svg className="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                          View
                        </span>
                      </button>
                      <button className="text-blue-600 hover:text-blue-800">
                        <span className="flex items-center">
                          <svg className="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                          </svg>
                          Download
                        </span>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
      
      {/* Payment Methods Section */}
      <div className="mt-8 bg-white border rounded-lg p-6">
        <h3 className="text-lg font-bold mb-4">Payment Methods</h3>
        <div className="flex items-center justify-between p-4 border rounded-md bg-gray-50">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-md mr-4">
              <svg className="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <p className="font-medium">PayFast</p>
              <p className="text-sm text-gray-500">Auto-renewing monthly</p>
            </div>
          </div>
          <button className="text-blue-600 hover:text-blue-800 text-sm font-medium">
            Update Payment Method
          </button>
        </div>
      </div>
    </div>
  )
}

export default PaymentHistory
