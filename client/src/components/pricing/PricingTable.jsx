import React, { useState, useEffect } from 'react';
import { fetchPackageTiers } from '../../utils/api';

const PricingTable = () => {
  const [pricingData, setPricingData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [billingCycle, setBillingCycle] = useState('monthly'); // 'monthly' or 'yearly'

  useEffect(() => {
    const loadPricingData = async () => {
      try {
        setLoading(true);
        const response = await fetchPackageTiers();
        setPricingData(response.data.data);
        setError(null);
      } catch (err) {
        console.error('Error fetching pricing data:', err);
        setError('Failed to load pricing information. Please try again later.');
      } finally {
        setLoading(false);
      }
    };

    loadPricingData();
  }, []);

  if (loading) {
    return (
      <div className="flex justify-center items-center p-8">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center p-8">
        <p className="text-red-500">{error}</p>
        <button 
          className="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          onClick={() => window.location.reload()}
        >
          Try Again
        </button>
      </div>
    );
  }

  if (!pricingData) return null;

  const toggleBillingCycle = () => {
    setBillingCycle(billingCycle === 'monthly' ? 'yearly' : 'monthly');
  };

  const getFeatureIcon = (value) => {
    return value ? (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
    ) : (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
      </svg>
    );
  };

  return (
    <div className="py-12 bg-gray-100">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <h2 className="text-3xl font-extrabold text-gray-900 sm:text-4xl">
            Membership Packages
          </h2>
          <p className="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
            Choose the perfect package for your business
          </p>
        </div>

        {/* Billing toggle */}
        <div className="mt-8 flex justify-center">
          <div className="relative flex items-center rounded-full p-1 bg-gray-200">
            <button
              onClick={toggleBillingCycle}
              className={`${billingCycle === 'monthly' ? 'bg-white shadow-sm' : ''} rounded-full py-2 px-6 text-sm font-medium transition-all`}
            >
              Monthly
            </button>
            <button
              onClick={toggleBillingCycle}
              className={`${billingCycle === 'yearly' ? 'bg-white shadow-sm' : ''} rounded-full py-2 px-6 text-sm font-medium transition-all`}
            >
              Yearly <span className="text-green-600 font-semibold">Save up to 16%</span>
            </button>
          </div>
        </div>

        {/* Pricing cards */}
        <div className="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
          {Object.entries(pricingData).map(([tier, data]) => (
            <div key={tier} className="bg-white overflow-hidden shadow-lg rounded-lg divide-y divide-gray-200">
              {/* Package header */}
              <div className="px-6 py-8">
                <h3 className="text-2xl font-medium text-gray-900 text-center">{tier}</h3>
                <div className="mt-4 flex justify-center items-baseline">
                  <span className="text-5xl font-extrabold tracking-tight text-gray-900">
                    R{billingCycle === 'monthly' ? data.price_monthly : data.price_yearly}
                  </span>
                  <span className="ml-1 text-xl font-semibold text-gray-500">
                    /{billingCycle === 'monthly' ? 'mo' : 'yr'}
                  </span>
                </div>
                {tier !== 'Basic' && billingCycle === 'yearly' && (
                  <p className="mt-2 text-sm text-green-600 text-center">
                    Save R{data.price_monthly * 12 - data.price_yearly}
                  </p>
                )}
              </div>

              {/* Features list */}
              <div className="px-6 pt-6 pb-8">
                <h4 className="text-sm font-semibold text-gray-900 tracking-wide uppercase">What's included</h4>
                <ul className="mt-6 space-y-4">
                  <li className="flex">
                    {getFeatureIcon(data.features.business_name)}
                    <span className="ml-3 text-base text-gray-700">Business name listing</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.area_of_operation)}
                    <span className="ml-3 text-base text-gray-700">Area of operation</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.website)}
                    <span className="ml-3 text-base text-gray-700">Website link</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.whatsapp)}
                    <span className="ml-3 text-base text-gray-700">WhatsApp contact</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.email)}
                    <span className="ml-3 text-base text-gray-700">Email contact</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.star_ratings)}
                    <span className="ml-3 text-base text-gray-700">Star ratings</span>
                  </li>
                  <li className="flex">
                    {getFeatureIcon(data.features.product_catalog)}
                    <span className="ml-3 text-base text-gray-700">Product catalog</span>
                  </li>
                  <li className="flex">
                    <span className="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">
                      {data.features.monthly_adverts}
                    </span>
                    <span className="ml-3 text-base text-gray-700">Monthly adverts</span>
                  </li>
                  <li className="flex">
                    {data.features.social_media_feature ? (
                      <span className="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">
                        1
                      </span>
                    ) : getFeatureIcon(false)}
                    <span className="ml-3 text-base text-gray-700">Social media feature</span>
                  </li>
                </ul>
              </div>

              {/* CTA button */}
              <div className="px-6 py-6">
                <button
                  className={`w-full flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white ${tier === 'Gold' ? 'bg-yellow-600 hover:bg-yellow-700' : tier === 'Silver' ? 'bg-gray-400 hover:bg-gray-500' : tier === 'Bronze' ? 'bg-amber-700 hover:bg-amber-800' : 'bg-gray-300 hover:bg-gray-400'}`}
                >
                  {tier === 'Basic' ? 'Get Started' : 'Upgrade'}
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Feature comparison details */}
        <div className="mt-16 bg-white p-8 rounded-lg shadow-lg">
          <h3 className="text-2xl font-semibold text-gray-900 mb-6">Detailed Feature Comparison</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Feature
                  </th>
                  {Object.keys(pricingData).map(tier => (
                    <th key={tier} scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {tier}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {[
                  { id: 'business_name', name: 'Business Name Listing' },
                  { id: 'area_of_operation', name: 'Area of Operation' },
                  { id: 'website', name: 'Website Link' },
                  { id: 'whatsapp', name: 'WhatsApp Contact' },
                  { id: 'email', name: 'Email Contact' },
                  { id: 'star_ratings', name: 'Star Ratings' },
                  { id: 'product_catalog', name: 'Product Catalog' },
                  { id: 'monthly_adverts', name: 'Monthly Adverts', isNumeric: true },
                  { id: 'social_media_feature', name: 'Social Media Feature', isBoolean: true },
                ].map((feature) => (
                  <tr key={feature.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {feature.name}
                    </td>
                    {Object.entries(pricingData).map(([tier, data]) => (
                      <td key={`${tier}-${feature.id}`} className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {feature.isNumeric ? (
                          data.features[feature.id]
                        ) : feature.isBoolean ? (
                          data.features[feature.id] ? '1/month' : 'No'
                        ) : (
                          getFeatureIcon(data.features[feature.id])
                        )}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PricingTable;
