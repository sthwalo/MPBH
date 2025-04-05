/**
 * MPBH Theme Configuration
 * Based on the official brand colors from the logo
 */

export const theme = {
  colors: {
    // Primary colors
    primary: '#000000', // Black from logo
    secondary: '#333333', // Slightly lighter black for secondary elements
    accent: '#4A4A4A', // Accent color for highlights
    
    // UI colors
    background: '#FFFFFF',
    backgroundAlt: '#F8F8F8',
    text: '#101010',
    textLight: '#666666',
    border: '#E0E0E0',
    
    // Brand colors by tier
    basic: '#4A4A4A', // Gray for Basic tier
    bronze: '#CD7F32', // Bronze color for Bronze tier
    silver: '#C0C0C0', // Silver color for Silver tier
    gold: '#FFD700', // Gold color for Gold tier
    
    // Feedback colors
    success: '#28A745',
    error: '#DC3545',
    warning: '#FFC107',
    info: '#17A2B8'
  },
  
  fonts: {
    primary: "'Montserrat', sans-serif", // Clean, modern font for most text
    secondary: "'Playfair Display', serif", // Elegant serif font for headings
    script: "'Great Vibes', cursive" // Script font similar to "Conversations" in logo
  },
  
  shadows: {
    small: '0 2px 4px rgba(0, 0, 0, 0.1)',
    medium: '0 4px 8px rgba(0, 0, 0, 0.12)',
    large: '0 8px 16px rgba(0, 0, 0, 0.14)'
  },
  
  // Responsive breakpoints
  breakpoints: {
    mobile: '576px',
    tablet: '768px',
    desktop: '1024px',
    wide: '1280px'
  },
  
  // Spacing system
  spacing: {
    xs: '0.25rem', // 4px
    sm: '0.5rem',  // 8px
    md: '1rem',    // 16px
    lg: '1.5rem',  // 24px
    xl: '2rem',    // 32px
    xxl: '3rem'    // 48px
  },
  
  // Border radius
  borderRadius: {
    small: '0.25rem', // 4px
    medium: '0.5rem', // 8px
    large: '1rem',    // 16px
    full: '9999px'    // Fully rounded
  }
};

export default theme;
