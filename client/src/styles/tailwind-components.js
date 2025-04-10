/**
 * Reusable Tailwind CSS component classes for MPBH
 * Import and use these in your components for consistent styling
 */

export const button = {
  primary: 'inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors',
  secondary: 'inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors',
  tertiary: 'inline-flex items-center justify-center px-4 py-2 text-base font-medium text-blue-600 hover:text-blue-800 focus:outline-none transition-colors',
};

export const card = {
  base: 'bg-white rounded-lg shadow-brand-md p-6 transition-all hover:shadow-brand-lg',
  interactive: 'bg-white rounded-lg shadow-brand-md p-6 transition-all hover:shadow-brand-lg cursor-pointer transform hover:-translate-y-1',
  tier: {
    basic: 'border-t-4 border-tier-basic',
    bronze: 'border-t-4 border-tier-bronze',
    silver: 'border-t-4 border-tier-silver',
    gold: 'border-t-4 border-tier-gold',
  }
};

export const form = {
  label: 'block text-sm font-medium text-brand-gray-700 mb-1',
  input: 'w-full rounded-md border-brand-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
  select: 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-brand-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md',
  checkbox: 'h-4 w-4 text-blue-600 focus:ring-blue-500 border-brand-gray-300 rounded',
  radio: 'h-4 w-4 text-blue-600 focus:ring-blue-500 border-brand-gray-300 rounded-full',
};

export const layout = {
  section: 'py-12 px-4 sm:px-6 lg:px-8',
  container: 'max-w-7xl mx-auto',
  grid: {
    cols2: 'grid grid-cols-1 md:grid-cols-2 gap-6',
    cols3: 'grid grid-cols-1 md:grid-cols-3 gap-6',
    cols4: 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6',
  },
};

export const typography = {
  h1: 'text-4xl font-bold text-brand-gray-900 sm:text-5xl',
  h2: 'text-3xl font-bold text-brand-gray-900',
  h3: 'text-2xl font-bold text-brand-gray-900',
  h4: 'text-xl font-bold text-brand-gray-900',
  subtitle: 'text-xl text-brand-gray-600',
  body: 'text-base text-brand-gray-700',
  small: 'text-sm text-brand-gray-500',
};

export const iconButton = {
  primary: 'inline-flex items-center justify-center p-2 rounded-full text-brand-gray-500 hover:text-brand-black hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all',
  secondary: 'inline-flex items-center justify-center p-2 rounded-full text-brand-gray-400 hover:text-brand-gray-600 focus:outline-none transition-colors',
};
