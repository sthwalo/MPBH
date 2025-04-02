/**
 * Configuration for Fuse.js search library
 * Used for client-side fuzzy searching of businesses and products
 */

export const fuseOptions = {
  // Keys to search in (listed in order of importance)
  keys: [
    { name: 'name', weight: 2 },
    { name: 'category', weight: 1.5 },
    { name: 'description', weight: 1 },
    { name: 'district', weight: 1 }
  ],
  // Search configuration
  includeScore: true,
  threshold: 0.4,
  location: 0,
  distance: 100,
  minMatchCharLength: 2,
  shouldSort: true,
  // Match all tokens in the search string
  useExtendedSearch: true,
  // Limit the number of results
  limit: 50
};

/**
 * Initialize a new Fuse instance with data
 * @param {Array} data - Array of objects to search through
 * @param {Object} options - Custom options to override defaults
 * @returns {Fuse} - Configured Fuse instance
 */
export const initFuse = (data, options = {}) => {
  // Dynamically import Fuse to ensure it's only loaded when needed
  return import('fuse.js').then(({ default: Fuse }) => {
    return new Fuse(data, { ...fuseOptions, ...options });
  });
};

/**
 * Format search results from Fuse
 * @param {Array} results - Raw Fuse.js results with scores
 * @returns {Array} - Clean array of result objects
 */
export const formatSearchResults = (results) => {
  return results.map(result => ({
    ...result.item,
    score: result.score
  }));
};

/**
 * Helper function to search businesses by category
 * @param {Fuse} fuse - Initialized Fuse instance
 * @param {string} category - Category to search for
 * @returns {Array} - Businesses matching the category
 */
export const searchByCategory = (fuse, category) => {
  return formatSearchResults(
    fuse.search({ field: 'category', value: category })
  );
};

/**
 * Helper function to search businesses by district
 * @param {Fuse} fuse - Initialized Fuse instance
 * @param {string} district - District to search for
 * @returns {Array} - Businesses matching the district
 */
export const searchByDistrict = (fuse, district) => {
  return formatSearchResults(
    fuse.search({ field: 'district', value: district })
  );
};
