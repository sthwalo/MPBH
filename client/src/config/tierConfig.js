/**
 * Configuration file for business tier features
 * Defines what features are available at each subscription tier
 */

const tierConfig = {
  // Basic (Free) Tier
  Basic: {
    name: 'Basic',
    displayName: 'Basic Listing',
    color: 'gray',
    features: {
      listing: true,            // Basic business listing
      contactInfo: false,       // Contact information display
      websiteLink: false,       // Website link
      whatsappButton: false,    // WhatsApp contact button
      socialLinks: false,       // Social media links
      businessHours: false,     // Business hours display
      products: false,          // Product/service catalog
      maxProducts: 0,           // Maximum number of products
      adverts: false,           // Promotional advertisements
      maxAdverts: 0,            // Maximum number of adverts
      featuredPlacement: false, // Featured placement in listings
      homepageShowcase: false,  // Homepage features section
      analyticsBasic: false,    // Basic analytics dashboard
      analyticsAdvanced: false, // Advanced analytics
      prioritySupport: false    // Priority support
    },
    description: 'Free basic listing in the business directory',
    maxFileSize: 1,            // Max file size in MB for uploads
    maxImages: 3               // Max number of images
  },
  
  // Bronze Tier
  Bronze: {
    name: 'Bronze',
    displayName: 'Bronze Package',
    color: '#CD7F32',
    features: {
      listing: true,
      contactInfo: true,
      websiteLink: true,
      whatsappButton: true,
      socialLinks: true,
      businessHours: true,
      products: false,
      maxProducts: 0,
      adverts: false,
      maxAdverts: 0,
      featuredPlacement: false,
      homepageShowcase: false,
      analyticsBasic: true,
      analyticsAdvanced: false,
      prioritySupport: false
    },
    description: 'Enhanced listing with contact details and online presence',
    maxFileSize: 2,
    maxImages: 6
  },
  
  // Silver Tier
  Silver: {
    name: 'Silver',
    displayName: 'Silver Package',
    color: '#C0C0C0',
    features: {
      listing: true,
      contactInfo: true,
      websiteLink: true,
      whatsappButton: true,
      socialLinks: true,
      businessHours: true,
      products: true,
      maxProducts: 10,
      adverts: true,
      maxAdverts: 3,
      featuredPlacement: false,
      homepageShowcase: false,
      analyticsBasic: true,
      analyticsAdvanced: true,
      prioritySupport: false
    },
    description: 'Promotional capabilities with product catalog',
    maxFileSize: 5,
    maxImages: 12
  },
  
  // Gold Tier
  Gold: {
    name: 'Gold',
    displayName: 'Gold Package',
    color: '#FFD700',
    features: {
      listing: true,
      contactInfo: true,
      websiteLink: true,
      whatsappButton: true,
      socialLinks: true,
      businessHours: true,
      products: true,
      maxProducts: 30,
      adverts: true,
      maxAdverts: 10,
      featuredPlacement: true,
      homepageShowcase: true,
      analyticsBasic: true,
      analyticsAdvanced: true,
      prioritySupport: true
    },
    description: 'Premium visibility with maximum promotional tools',
    maxFileSize: 10,
    maxImages: 30
  }
};

/**
 * Check if a specific feature is available for a given tier
 * @param {string} tier - The tier name (Basic, Bronze, Silver, Gold)
 * @param {string} feature - The feature key to check
 * @returns {boolean} Whether the feature is available
 */
export const hasFeature = (tier, feature) => {
  if (!tierConfig[tier] || !tierConfig[tier].features.hasOwnProperty(feature)) {
    return false;
  }
  
  return tierConfig[tier].features[feature];
};

/**
 * Get tier badge data for displaying tier information
 * @param {string} tier - The tier name
 * @returns {Object} Badge data object with name, color, etc.
 */
export const getTierBadgeData = (tier) => {
  if (!tierConfig[tier]) {
    return {
      name: 'Unknown',
      displayName: 'Unknown Tier',
      color: 'gray',
      description: 'Unknown tier'
    };
  }
  
  return {
    name: tierConfig[tier].name,
    displayName: tierConfig[tier].displayName,
    color: tierConfig[tier].color,
    description: tierConfig[tier].description
  };
};

/**
 * Get the maximum allowed count for a specific feature (products, adverts, etc.)
 * @param {string} tier - The tier name
 * @param {string} feature - The feature to get max count for (e.g., 'maxProducts')
 * @returns {number} The maximum count allowed
 */
export const getMaxCount = (tier, feature) => {
  if (!tierConfig[tier] || !tierConfig[tier].hasOwnProperty(feature)) {
    return 0;
  }
  
  return tierConfig[tier][feature];
};

/**
 * Get all available tiers for display in upgrade options
 * @returns {Array} Array of tier objects with key details
 */
export const getAllTiers = () => {
  return Object.keys(tierConfig).map(key => ({
    name: tierConfig[key].name,
    displayName: tierConfig[key].displayName,
    color: tierConfig[key].color,
    description: tierConfig[key].description,
    features: Object.entries(tierConfig[key].features)
      .filter(([_, enabled]) => enabled)
      .map(([feature]) => feature)
  }));
};

export default tierConfig;
