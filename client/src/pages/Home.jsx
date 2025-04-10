import { Link } from 'react-router-dom'
import mpbhLogo from '../assets/images/MPBH.jpeg'

function Home() {
  return (
    <div>
      {/* Hero Section */}
      <section className="bg-brand-black text-brand-white py-16 md:py-24">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row items-center">
            <div className="md:w-1/2 md:pr-12 mb-8 md:mb-0">
              <h1 className="text-4xl md:text-5xl font-bold mb-4">Discover Mpumalanga's Best Businesses</h1>
              <p className="text-xl mb-8">Connect with top-rated tourism, agriculture, construction, and event service providers across the province.</p>
              <div className="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <Link to="/directory" className="px-4 py-2 bg-brand-white text-brand-black font-medium rounded hover:bg-brand-gray-200 transition-colors">Browse Directory</Link>
                <Link to="/register" className="px-4 py-2 border border-brand-white text-brand-white font-medium rounded hover:bg-brand-white hover:text-brand-black transition-colors">Register Business</Link>
              </div>
            </div>
            <div className="md:w-1/2">
              <img src={mpbhLogo} alt="Mpumalanga Business Hub" className="rounded-lg shadow-xl w-full" />
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-brand-gray-100">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">Explore Business Categories</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* Tourism Card */}
            <div className="card hover:shadow-brand-lg bg-brand-white overflow-hidden border border-brand-gray-200">
              <div className="h-48 bg-brand-gray-100">
                <img src="/src/assets/images/tourism.jpg" alt="Tourism" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2 font-serif">Tourism</h3>
                <p className="text-brand-gray-600 mb-4">Discover lodges, tour guides, and attractions across Mpumalanga's scenic landscapes.</p>
                <Link to="/directory?category=Tourism" className="text-brand-black hover:text-brand-gray-600 font-medium">View Tourism Businesses →</Link>
              </div>
            </div>

            {/* Agriculture Card */}
            <div className="card hover:shadow-brand-lg bg-brand-white overflow-hidden border border-brand-gray-200">
              <div className="h-48 bg-brand-gray-100">
                <img src="/src/assets/images/agriculture.png" alt="Agriculture" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2 font-serif">Agriculture</h3>
                <p className="text-brand-gray-600 mb-4">Connect with farms, suppliers, and agricultural services throughout the region.</p>
                <Link to="/directory?category=Agriculture" className="text-brand-black hover:text-brand-gray-600 font-medium">View Agriculture Businesses →</Link>
              </div>
            </div>

            {/* Construction Card */}
            <div className="card hover:shadow-brand-lg bg-brand-white overflow-hidden border border-brand-gray-200">
              <div className="h-48 bg-brand-gray-100">
                <img src="/src/assets/images/construction.jpg" alt="Construction" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2 font-serif">Construction</h3>
                <p className="text-brand-gray-600 mb-4">Find reliable contractors, suppliers, and construction services for your projects.</p>
                <Link to="/directory?category=Construction" className="text-brand-black hover:text-brand-gray-600 font-medium">View Construction Businesses →</Link>
              </div>
            </div>

            {/* Events Card */}
            <div className="card hover:shadow-brand-lg bg-brand-white overflow-hidden border border-brand-gray-200">
              <div className="h-48 bg-brand-gray-100">
                <img src="/src/assets/images/events.jpg" alt="Events" className="w-full h-full object-cover" />
              </div>
              <div className="p-6">
                <h3 className="font-bold text-xl mb-2 font-serif">Events</h3>
                <p className="text-brand-gray-600 mb-4">Discover event planners, venues, and service providers for your next gathering.</p>
                <Link to="/directory?category=Events" className="text-brand-black hover:text-brand-gray-600 font-medium">View Event Businesses →</Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Membership Tiers */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-4 font-serif">Premium Membership Packages</h2>
          <p className="text-xl text-center text-brand-gray-600 mb-12 max-w-3xl mx-auto">Enhance your business visibility with our tiered membership options</p>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* Basic Tier */}
            <div className="card text-center hover:shadow-brand-lg border border-brand-gray-200">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4 font-serif">Basic</h3>
                <p className="text-brand-gray-500 mb-4">Free listing for all businesses</p>
                <div className="text-4xl font-bold mb-6">R0<span className="text-xl text-brand-gray-500 font-normal">/mo</span></div>
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
                <Link to="/register" className="px-4 py-2 border border-brand-black text-brand-black font-medium rounded inline-block w-full hover:bg-brand-black hover:text-brand-white transition-colors">Register</Link>
              </div>
            </div>
            
            {/* Bronze Tier */}
            <div className="card text-center hover:shadow-brand-lg border border-brand-gray-200">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4 font-serif">Bronze</h3>
                <p className="text-brand-gray-500 mb-4">Essential visibility package</p>
                <div className="text-4xl font-bold mb-6">R200<span className="text-xl text-brand-gray-500 font-normal">/mo</span></div>
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
            <div className="card text-center hover:shadow-brand-lg relative border border-brand-gray-200">
              <div className="absolute top-0 right-0 bg-brand-black text-brand-white font-bold py-1 px-4 rounded-bl-lg">Popular</div>
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4 font-serif">Silver</h3>
                <p className="text-brand-gray-500 mb-4">Business growth package</p>
                <div className="text-4xl font-bold mb-6">R500<span className="text-xl text-brand-gray-500 font-normal">/mo</span></div>
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
            <div className="card text-center hover:shadow-brand-lg border border-brand-gray-200">
              <div className="px-6 py-8">
                <h3 className="text-2xl font-bold mb-4 font-serif">Gold</h3>
                <p className="text-brand-gray-500 mb-4">Premium promotion package</p>
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
      <section className="bg-brand-black text-brand-white py-16">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold mb-4 font-serif">Ready to grow your business?</h2>
          <p className="text-xl mb-8 max-w-2xl mx-auto">Join thousands of businesses across Mpumalanga connecting with new customers every day</p>
          <Link to="/register" className="px-6 py-3 bg-brand-white text-brand-black font-medium rounded hover:bg-brand-gray-200 transition-colors text-lg">Register Your Business Today</Link>
        </div>
      </section>
    </div>
  )
}

export default Home
