import React from 'react';

const Logo = ({ size = 'default' }) => {
  // Size variants
  const sizes = {
    small: { width: '120px', height: 'auto' },
    default: { width: '180px', height: 'auto' },
    large: { width: '240px', height: 'auto' },
  };
  
  const style = sizes[size] || sizes.default;
  
  return (
    <div className="logo" style={{ ...style, textAlign: 'center' }}>
      <div style={{ fontSize: size === 'small' ? '0.75rem' : '1rem', fontWeight: 'bold', marginBottom: '0.2rem' }}>
        <span style={{ letterSpacing: '0.05em', fontSize: '0.9em' }}>MP</span>
      </div>
      <div 
        style={{
          fontFamily: '"Playfair Display", serif',
          fontWeight: 'bold',
          fontSize: size === 'small' ? '1.4rem' : size === 'large' ? '2.5rem' : '2rem',
          letterSpacing: '0.05em',
          position: 'relative',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}
      >
        <span style={{ position: 'relative', zIndex: 2 }}>B</span>
        <span 
          style={{
            position: 'absolute',
            left: '50%',
            transform: 'translateX(-25%)',
            fontSize: '1.2em',
            zIndex: 1
          }}
        >C</span>
      </div>
      <div style={{ 
        fontSize: size === 'small' ? '1.25rem' : size === 'large' ? '3rem' : '2rem',
        fontWeight: 'bold',
        letterSpacing: '0.1em',
        fontFamily: '"Playfair Display", serif',
      }}>
        BUSINESS
      </div>
      <div style={{ 
        fontFamily: '"Great Vibes", cursive, serif',
        fontSize: size === 'small' ? '1.2rem' : size === 'large' ? '2.8rem' : '2rem',
        marginTop: '-0.5rem',
      }}>
        Conversations
      </div>
      <div style={{ 
        fontFamily: '"Montserrat", sans-serif',
        fontSize: size === 'small' ? '0.6rem' : '0.8rem',
        letterSpacing: '0.2em',
        fontWeight: '500',
        marginTop: '0.2rem',
      }}>
        MPUMALANGA
      </div>
    </div>
  );
};

export default Logo;
