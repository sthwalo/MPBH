import { useState } from 'react'

function UpgradePlan({ businessData }) {
  const [selectedPlan, setSelectedPlan] = useState(businessData.package_type)
  const [billingCycle, setBillingCycle] = useState('monthly')
  const [isLoading, setIsLoading] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')

  // Define package tiers and their features
  const packages = [
    {
      id: 'Basic',
      name: 'Basic',
      monthlyPrice: 0,
      annualPrice: 0,
      features: [
        { name: 'Listing Visibility', included: true },
        { name: 'Contact Links', included: false },
        { name: 'E-Commerce', included: false },
        { name: 'Monthly Adverts', included: false, details: '0' }
      ]
    },
    {
      id: 'Bronze',
      name: 'Bronze',
      monthlyPrice: 200,
      annualPrice: 2000,
      features: [
        { name: 'Listing Visibility', included: true },
        { name: 'Contact Links', included: true },
        { name: 'E-Commerce', included: false },
        { name: 'Monthly Adverts', included: false, details: '0' }
      ]
    },
    {
      id: 'Silver',
      name: 'Silver',
      monthlyPrice: 500,
      annualPrice: 5000,
      popular: true,
      features: [
        { name: 'Listing Visibility', included: true },
        { name: 'Contact Links', included: true },
        { name: 'E-Commerce', included: true },
        { name: 'Monthly Adverts', included: true, details: '1' }
      ]
    },
    {
      id: 'Gold',
      name: 'Gold',
      monthlyPrice: 1000,
      annualPrice: 10000,
      features: [
        { name: 'Listing Visibility', included: true },
        { name: 'Contact Links', included: true },
        { name: 'E-Commerce', included: true },
        { name: 'Monthly Adverts', included: true, details: '4' }
      ]
    }
  ]

  const handlePlanChange = (planId) => {
    setSelectedPlan(planId)
    // Reset success message if any
    setSuccessMessage('')
  }

  const handleBillingCycleChange = (cycle) => {
    setBillingCycle(cycle)
    // Reset success message if any
    setSuccessMessage('')
  }

  const getDiscountPercentage = () => {
    return 15 // 15% discount for annual billing
  }

  const getCurrentPlanIndex = () => {
    return packages.findIndex(pkg => pkg.id === businessData.package_type)
  }

  const getSelectedPlanIndex = () => {
    return packages.findIndex(pkg => pkg.id === selectedPlan)
  }

  const isDowngrade = () => {
    return getSelectedPlanIndex() < getCurrentPlanIndex()
  }

  const isUpgrade = () => {
    return getSelectedPlanIndex() > getCurrentPlanIndex()
  }

  const isCurrentPlan = () => {
    return selectedPlan === businessData.package_type
  }

  const handleSubmit = async () => {
    if (isCurrentPlan()) return
    
    setIsLoading(true)
    
    // In production, this would redirect to PayFast or handle the payment process
    // const initiatePayment = async () => {
    //   try {
    //     const response = await fetch('/api/payments/upgrade', {
    //       method: 'POST',
    //       headers: {
    //         'Content-Type': 'application/json',
    //         'Authorization': `Bearer ${localStorage.getItem('token')}`
    //       },
    //       body: JSON.stringify({
    //         package: selectedPlan,
    //         billingCycle
    //       })
    //     })
    //     
    //     if (!response.ok) throw new Error('Failed to initiate payment')
    //     
    //     const data = await response.json()
    //     
    //     // Redirect to PayFast checkout
    //     window.location.href = data.paymentUrl
    //   } catch (error) {
    //     console.error('Error initiating payment:', error)
    //     setIsLoading(false)
    //   }
    // }
    // 
    // initiatePayment()
    
    // For demo purposes, we'll just simulate a successful upgrade
    setTimeout(() => {
      // Update the business data for this demo
      businessData.package_type = selectedPlan
      if (selectedPlan === 'Silver') {
        businessData.subscription.amount = billingCycle === 'monthly' ? 500 : 5000 / 12
        businessData.adverts_remaining = 1
      } else if (selectedPlan === 'Gold') {
        businessData.subscription.amount = billingCycle === 'monthly' ? 1000 : 10000 / 12
        businessData.adverts_remaining = 4
      } else if (selectedPlan === 'Bronze') {
        businessData.subscription.amount = billingCycle === 'monthly' ? 200 : 2000 / 12
        businessData.adverts_remaining = 0
      } else {
        businessData.subscription.amount = 0
        businessData.adverts_remaining = 0
      }
      
      setSuccessMessage(`Successfully ${isUpgrade() ? 'upgraded to' : 'changed to'} ${selectedPlan} package with ${billingCycle} billing.`)
      setIsLoading(false)
    }, 1500)
  }

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Upgrade Your Package</h2>
      
      {/* Success message */}
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 mb-6">
          {successMessage}
        </div>
      )}
      
      {/* Current Plan */}
      <div className="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
        <div className="flex items-center">
          <svg className="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <div>
            <p className="font-medium">Current Plan: <span className="font-bold">{businessData.package_type}</span></p>
            <p className="text-sm text-blue-600">
              Billing: R{businessData.subscription.amount}/month • Next payment on {new Date(businessData.subscription.next_billing_date).toLocaleDateString()}
            </p>
          </div>
        </div>
      </div>
      
      {/* Billing Cycle Toggle */}
      <div className="flex justify-center mb-8">
        <div className="bg-white border rounded-lg inline-flex">
          <button 
            className={`px-6 py-3 text-sm font-medium ${billingCycle === 'monthly' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
            onClick={() => handleBillingCycleChange('monthly')}
          >
            Monthly
          </button>
          <button 
            className={`px-6 py-3 text-sm font-medium ${billingCycle === 'annual' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
            onClick={() => handleBillingCycleChange('annual')}
          >
            Annual <span className="text-green-500 font-semibold">(Save {getDiscountPercentage()}%)</span>
          </button>
        </div>
      </div>
      
      {/* Pricing Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {packages.map((pkg) => {
          const isSelected = selectedPlan === pkg.id
          const isCurrent = businessData.package_type === pkg.id
          
          return (
            <div 
              key={pkg.id}
              className={`bg-white border rounded-lg overflow-hidden transition-all ${isSelected ? 'ring-2 ring-blue-600 shadow-lg' : ''} ${pkg.popular ? 'relative' : ''}`}
            >
              {pkg.popular && (
                <div className="absolute top-0 right-0 bg-blue-600 text-white font-bold py-1 px-4 rounded-bl-lg">
                  Popular
                </div>
              )}
              
              <div className="p-6">
                <h3 className="text-2xl font-bold mb-1">{pkg.name}</h3>
                <p className="text-gray-500 mb-4">Package</p>
                
                <div className="mb-4">
                  <div className="text-3xl font-bold">
                    R{billingCycle === 'monthly' ? pkg.monthlyPrice : Math.round(pkg.annualPrice / 12)}
                    <span className="text-xl text-gray-500 font-normal">/mo</span>
                  </div>
                  
                  {billingCycle === 'annual' && pkg.annualPrice > 0 && (
                    <div className="text-sm text-green-600 mt-1">
                      R{pkg.annualPrice} billed annually
                      <br />
                      <span className="font-medium">Save R{pkg.monthlyPrice * 12 - pkg.annualPrice}</span>
                    </div>
                  )}
                </div>
                
                <ul className="space-y-3 mb-6">
                  {pkg.features.map((feature, index) => (
                    <li key={index} className="flex items-start">
                      {feature.included ? (
                        <svg className="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                      ) : (
                        <svg className="h-5 w-5 text-red-500 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      )}
                      <span>
                        {feature.name}
                        {feature.details && <span className="text-gray-500 ml-1">({feature.details})</span>}
                      </span>
                    </li>
                  ))}
                </ul>
                
                <button 
                  onClick={() => handlePlanChange(pkg.id)}
                  className={`w-full py-2 px-4 rounded-md transition-colors ${isSelected 
                    ? 'bg-blue-600 text-white' 
                    : 'border border-blue-600 text-blue-600 hover:bg-blue-50'}`}
                  disabled={isCurrent && isSelected}
                >
                  {isCurrent ? 'Current Plan' : isSelected ? 'Selected' : 'Select'}
                </button>
              </div>
            </div>
          )
        })}
      </div>
      
      {/* Action Section */}
      <div className="bg-gray-50 border rounded-lg p-6">
        <div className="flex flex-col md:flex-row justify-between items-center">
          <div className="mb-4 md:mb-0">
            <h3 className="text-lg font-bold">
              {isCurrentPlan() 
                ? 'You are on this plan already' 
                : isUpgrade()
                  ? `Upgrade to ${selectedPlan} Package` 
                  : `Change to ${selectedPlan} Package`}
            </h3>
            <p className="text-gray-600">
              {isCurrentPlan() 
                ? 'You can choose another plan to upgrade or downgrade' 
                : isUpgrade()
                  ? 'Get more features and benefits' 
                  : 'You will lose some features but pay less'}
            </p>
          </div>
          
          <button 
            onClick={handleSubmit}
            disabled={isCurrentPlan() || isLoading}
            className={`btn ${isCurrentPlan() ? 'btn-gray' : isUpgrade() ? 'btn-primary' : 'btn-outline'} ${isLoading ? 'opacity-75' : ''}`}
          >
            {isLoading ? (
              <>
                <svg className="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </>
            ) : isCurrentPlan() 
              ? 'Current Plan' 
              : isUpgrade() 
                ? 'Upgrade Now' 
                : 'Change Plan'}
          </button>
        </div>
      </div>
    </div>
  )
}

export default UpgradePlan
