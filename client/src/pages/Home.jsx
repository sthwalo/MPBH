import { Link } from 'react-router-dom'

function Home() {
  return (
    <div>
      {/* Hero Section */}
      <section className="bg-gradient-to-r from-blue-700 to-blue-500 text-white py-16 md:py-24">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row items-center">
            <div className="md:w-1/2 md:pr-12 mb-8 md:mb-0">
              <h1 className="text-4xl md:text-5xl font-bold mb-4">Discover Mpumalanga's Best Businesses</h1>
              <p className="text-xl mb-8">Connect with top-rated tourism, agriculture, construction, and event service providers across the province.</p>
              <div className="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <Link to="/directory" className="btn btn-primary bg-white text-blue-700 hover:bg-gray-100">Browse Directory</Link>
                <Link to="/register" className="btn btn-outline text-white border-white hover:bg-white hover:text-blue-700">Register Business</Link>
              </div>
            </div>
            <div className="md:w-1/2">
              <img src="/assets/images/mpumalanga-hero.jpg" alt="Mpumalanga Landscape" className="rounded-lg shadow-xl" />
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">Explore Business Categories</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* Tourism Card */}
            <div className="card hover:shadow-lg bg-white overflow-hidden">
              <div className="h-48 bg-blue-100">
                <img src="/assets/images/tourism.jpg" alt="Tourism" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2">Tourism</h3>
                <p className="text-gray-600 mb-4">Discover lodges, tour guides, and attractions across Mpumalanga's scenic landscapes.</p>
                <Link to="/directory?category=Tourism" className="text-blue-600 hover:text-blue-800 font-medium">View Tourism Businesses →</Link>
              </div>
            </div>

            {/* Agriculture Card */}
            <div className="card hover:shadow-lg bg-white overflow-hidden">
              <div className="h-48 bg-green-100">
                <img src="/assets/images/agriculture.jpg" alt="Agriculture" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2">Agriculture</h3>
                <p className="text-gray-600 mb-4">Connect with farms, suppliers, and agricultural services throughout the region.</p>
                <Link to="/directory?category=Agriculture" className="text-blue-600 hover:text-blue-800 font-medium">View Agriculture Businesses →</Link>
              </div>
            </div>

            {/* Construction Card */}
            <div className="card hover:shadow-lg bg-white overflow-hidden">
              <div className="h-48 bg-yellow-100">
                <img src="/assets/images/construction.jpg" alt="Construction" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2">Construction</h3>
                <p className="text-gray-600 mb-4">Find reliable contractors, suppliers, and construction services for your projects.</p>
                <Link to="/directory?category=Construction" className="text-blue-600 hover:text-blue-800 font-medium">View Construction Businesses →</Link>
              </div>
            </div>

            {/* Events Card */}
            <div className="card hover:shadow-lg bg-white overflow-hidden">
              <div className="h-48 bg-purple-100">
                <img src="/assets/images/events.jpg" alt="Events" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2">Events</h3>
                <p className="text-gray-600 mb-4">Discover event planners, venues, and service providers for your next gathering.</p>
                <Link to="/directory?category=Events" className="text-blue-600 hover:text-blue-800 font-medium">View Event Businesses →</Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Membership Tiers */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-4">Premium Membership Packages</h2>
          <p className="text-xl text-center text-gray-600 mb-12 max-w-3xl mx-auto">Enhance your business visibility with our tiered membership options</p>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* Basic Tier */}
            <div className="card text-center hover:shadow-lg">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4">Basic</h3>
                <p className="text-gray-500 mb-4">Free listing for all businesses</p>
                <div className="text-4xl font-bold mb-6">R0<span className="text-xl text-gray-500 font-normal">/mo</span></div>
                <ul className="space-y-3 mb-8 text-left">
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Listing Visibility
                  </li>
                  <li className="flex items-center text-gray-400">
                    <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Contact Links
                  </li>
                  <li className="flex items-center text-gray-400">
                    <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    E-Commerce
                  </li>
                  <li className="flex items-center text-gray-400">
                    <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Monthly Adverts
                  </li>
                </ul>
                <Link to="/register" className="btn btn-outline w-full">Register</Link>
              </div>
            </div>
            
            {/* Bronze Tier */}
            <div className="card text-center hover:shadow-lg">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4">Bronze</h3>
                <p className="text-gray-500 mb-4">Essential visibility package</p>
                <div className="text-4xl font-bold mb-6">R200<span className="text-xl text-gray-500 font-normal">/mo</span></div>
                <ul className="space-y-3 mb-8 text-left">
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Listing Visibility
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Contact Links
                  </li>
                  <li className="flex items-center text-gray-400">
                    <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    E-Commerce
                  </li>
                  <li className="flex items-center text-gray-400">
                    <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Monthly Adverts
                  </li>
                </ul>
                <Link to="/pricing" className="btn btn-primary w-full">Upgrade</Link>
              </div>
            </div>
            
            {/* Silver Tier */}
            <div className="card text-center hover:shadow-lg relative">
              <div className="absolute top-0 right-0 bg-blue-600 text-white font-bold py-1 px-4 rounded-bl-lg">Popular</div>
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4">Silver</h3>
                <p className="text-gray-500 mb-4">Business growth package</p>
                <div className="text-4xl font-bold mb-6">R500<span className="text-xl text-gray-500 font-normal">/mo</span></div>
                <ul className="space-y-3 mb-8 text-left">
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Listing Visibility
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Contact Links
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    E-Commerce
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    1 Monthly Advert
                  </li>
                </ul>
                <Link to="/pricing" className="btn btn-primary w-full">Upgrade</Link>
              </div>
            </div>
            
            {/* Gold Tier */}
            <div className="card text-center hover:shadow-lg">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4">Gold</h3>
                <p className="text-gray-500 mb-4">Premium promotion package</p>
                <div className="text-4xl font-bold mb-6">R1000<span className="text-xl text-gray-500 font-normal">/mo</span></div>
                <ul className="space-y-3 mb-8 text-left">
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Listing Visibility
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Contact Links
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    E-Commerce
                  </li>
                  <li className="flex items-center">
                    <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                    4 Monthly Adverts
                  </li>
                </ul>
                <Link to="/pricing" className="btn btn-primary w-full">Upgrade</Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Call to Action */}
      <section className="bg-blue-600 text-white py-16">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold mb-4">Ready to grow your business?</h2>
          <p className="text-xl mb-8 max-w-2xl mx-auto">Join thousands of businesses across Mpumalanga connecting with new customers every day</p>
          <Link to="/register" className="btn btn-primary bg-white text-blue-600 hover:bg-gray-100 text-lg py-3 px-8">Register Your Business Today</Link>
        </div>
      </section>
    </div>
  )
}

export default Home
