import { useState, useEffect } from 'react';
import PropTypes from 'prop-types';

/**
 * LazyLoadImage component that only loads images when they enter the viewport
 * Improves page performance by reducing initial load time
 */
function LazyLoadImage({ src, alt, placeholderSrc, width, height, className, effect }) {
  const [imageSrc, setImageSrc] = useState(placeholderSrc || '');
  const [imageRef, setImageRef] = useState(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    // Skip if image is already loaded or there's no ref yet
    if (isLoaded || !imageRef) return;
    
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          // When image enters viewport, load the actual image
          if (entry.isIntersecting) {
            setImageSrc(src);
            observer.disconnect();
          }
        });
      },
      { rootMargin: '100px' } // Start loading a bit before it enters viewport
    );

    observer.observe(imageRef);

    return () => {
      if (imageRef) observer.disconnect();
    };
  }, [src, imageRef, isLoaded]);

  const handleError = () => {
    // If image fails to load, try to use placeholder
    if (imageSrc !== placeholderSrc && placeholderSrc) {
      setImageSrc(placeholderSrc);
    }
  };

  const handleLoad = () => {
    setIsLoaded(true);
  };

  // Determine classes based on effect
  const effectClass = effect === 'blur' ? 'transition-opacity duration-500' : '';
  const loadingClass = isLoaded ? 'opacity-100' : 'opacity-60';

  return (
    <img
      ref={setImageRef}
      src={imageSrc}
      alt={alt}
      width={width}
      height={height}
      className={`${className} ${effectClass} ${loadingClass}`}
      style={{ width, height, objectFit: 'cover' }}
      onLoad={handleLoad}
      onError={handleError}
    />
  );
}

LazyLoadImage.propTypes = {
  src: PropTypes.string.isRequired,
  alt: PropTypes.string.isRequired,
  placeholderSrc: PropTypes.string,
  width: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  height: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  className: PropTypes.string,
  effect: PropTypes.oneOf(['blur', 'fade', 'none'])
};

LazyLoadImage.defaultProps = {
  placeholderSrc: '',
  width: '100%',
  height: 'auto',
  className: '',
  effect: 'none'
};

export default LazyLoadImage;
