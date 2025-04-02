import { hasFeature, getMaxCount } from '../config/tierConfig';

/**
 * Utility functions for checking feature access based on business tier
 * These functions help enforce tier-based access throughout the application
 */

/**
 * Check if a business has access to a specific feature based on its package tier
 * @param {Object} business - The business object containing package_type
 * @param {string} feature - The feature to check access for
 * @returns {boolean} Whether the business has access to the feature
 */
export const canAccessFeature = (business, feature) => {
  if (!business || !business.package_type) {
    return false;
  }
  
  // If the business has a custom tier override for this feature, use that
  if (business.feature_overrides && 
      business.feature_overrides[feature] !== undefined) {
    return business.feature_overrides[feature];
  }
  
  return hasFeature(business.package_type, feature);
};

/**
 * Check if a business has reached the limit for a specific feature
 * @param {Object} business - The business object containing package_type
 * @param {string} featureType - The type of feature to check (e.g., 'products', 'adverts')
 * @param {number} currentCount - The current count of items
 * @returns {boolean} Whether the business has reached its limit
 */
export const isAtFeatureLimit = (business, featureType, currentCount) => {
  if (!business || !business.package_type) {
    return true; // Assume at limit if no business data
  }
  
  const maxKey = `max${featureType.charAt(0).toUpperCase() + featureType.slice(1)}`;
  const maxAllowed = getMaxCount(business.package_type, maxKey);
  
  return currentCount >= maxAllowed;
};

/**
 * Get formatted error message for feature access denial
 * @param {string} featureName - The name of the feature being accessed
 * @param {string} currentTier - The current tier
 * @param {string} requiredTier - The minimum tier required
 * @returns {string} Formatted error message
 */
export const getFeatureAccessError = (featureName, currentTier, requiredTier) => {
  return `This feature (${featureName}) requires a ${requiredTier} package or higher. Your current package is ${currentTier}.`;
};

/**
 * Check if a business can upload more media based on their tier limits
 * @param {Object} business - The business object
 * @param {number} currentImageCount - Current number of images
 * @returns {boolean} Whether more images can be uploaded
 */
export const canUploadMoreMedia = (business, currentImageCount) => {
  if (!business || !business.package_type) {
    return false;
  }
  
  const maxImages = getMaxCount(business.package_type, 'maxImages');
  return currentImageCount < maxImages;
};

/**
 * Generate a list of upgrade benefits for moving from current tier to target tier
 * @param {string} currentTier - The current tier name
 * @param {string} targetTier - The target tier name to upgrade to
 * @returns {Array} List of new features/benefits gained by upgrading
 */
export const getUpgradeBenefits = (currentTier, targetTier) => {
  const tiers = ['Basic', 'Bronze', 'Silver', 'Gold'];
  const currentIndex = tiers.indexOf(currentTier);
  const targetIndex = tiers.indexOf(targetTier);
  
  // If downgrading or invalid tiers, return empty array
  if (targetIndex <= currentIndex || currentIndex === -1 || targetIndex === -1) {
    return [];
  }
  
  const benefits = [];
  
  // Check each feature for differences
  const featureKeys = [
    { key: 'contactInfo', label: 'Contact Information Display' },
    { key: 'websiteLink', label: 'Website Link' },
    { key: 'whatsappButton', label: 'WhatsApp Button' },
    { key: 'socialLinks', label: 'Social Media Links' },
    { key: 'businessHours', label: 'Business Hours' },
    { key: 'products', label: 'Product Catalog' },
    { key: 'adverts', label: 'Promotional Adverts' },
    { key: 'featuredPlacement', label: 'Featured Placement' },
    { key: 'homepageShowcase', label: 'Homepage Showcase' },
    { key: 'analyticsBasic', label: 'Basic Analytics' },
    { key: 'analyticsAdvanced', label: 'Advanced Analytics' },
    { key: 'prioritySupport', label: 'Priority Support' }
  ];
  
  featureKeys.forEach(({ key, label }) => {
    if (!hasFeature(currentTier, key) && hasFeature(targetTier, key)) {
      benefits.push(label);
    }
  });
  
  // Check for increased limits
  const limitKeys = [
    { key: 'maxProducts', label: 'products' },
    { key: 'maxAdverts', label: 'adverts' },
    { key: 'maxImages', label: 'images' },
    { key: 'maxFileSize', label: 'file size (MB)' }
  ];
  
  limitKeys.forEach(({ key, label }) => {
    const currentLimit = getMaxCount(currentTier, key);
    const targetLimit = getMaxCount(targetTier, key);
    
    if (targetLimit > currentLimit) {
      benefits.push(`Increased ${label} limit: ${currentLimit} → ${targetLimit}`);
    }
  });
  
  return benefits;
};
