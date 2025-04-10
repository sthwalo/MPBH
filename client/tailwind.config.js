/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/**/*.{js,jsx,ts,tsx}',
    './public/index.html',
  ],
  theme: {
    extend: {
      colors: {
        // Strictly black and white with elegant grayscale
        'brand-black': '#000000',
        'brand-white': '#FFFFFF',
        'brand-gray': {
          100: '#F8F8F8',
          200: '#E0E0E0',
          300: '#BDBDBD',
          400: '#999999',
          500: '#666666',
          600: '#4A4A4A',
          700: '#333333',
          800: '#1F1F1F',
          900: '#101010',
        },
        // Tier colors in grayscale for elegance
        'tier-basic': '#4A4A4A',
        'tier-bronze': '#757575',  // Dark gray for bronze tier
        'tier-silver': '#C0C0C0',  // Silver is already grayscale
        'tier-gold': '#E0E0E0',   // Light gray for gold tier
      },
      fontFamily: {
        sans: ['Montserrat', 'sans-serif'],
        serif: ['Playfair Display', 'serif'],
        script: ['Great Vibes', 'cursive'],
      },
      boxShadow: {
        'brand-sm': '0 2px 4px rgba(0, 0, 0, 0.1)',
        'brand-md': '0 4px 8px rgba(0, 0, 0, 0.12)',
        'brand-lg': '0 8px 16px rgba(0, 0, 0, 0.14)',
      },
    },
  },
  plugins: [],
};
