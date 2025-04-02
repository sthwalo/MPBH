import React, { Component } from 'react';

/**
 * Error Boundary component for catching and handling API errors
 * This prevents the entire app from crashing when API calls fail
 */
class ApiErrorBoundary extends Component {
  constructor(props) {
    super(props);
    this.state = {
      hasError: false,
      error: null,
      errorInfo: null,
      isRetrying: false
    };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    // Log the error to an error reporting service
    console.error('API Error caught by boundary:', error, errorInfo);
    this.setState({ errorInfo });
    
    // You could also send to a logging service here
    // logErrorToService(error, errorInfo);
  }

  handleRetry = () => {
    this.setState({ isRetrying: true });
    
    // Give some time for the retry animation
    setTimeout(() => {
      this.setState({
        hasError: false,
        error: null,
        errorInfo: null,
        isRetrying: false
      });
    }, 1000);
  };

  render() {
    const { hasError, error, isRetrying } = this.state;
    const { fallback, children } = this.props;

    if (isRetrying) {
      return (
        <div className="api-error-retrying">
          <div className="spinner"></div>
          <p>Retrying...</p>
        </div>
      );
    }

    if (hasError) {
      // You can render any custom fallback UI
      if (fallback) {
        return fallback(error, this.handleRetry);
      }

      return (
        <div className="api-error-container">
          <h2>Something went wrong</h2>
          <p>{error?.message || 'An unexpected error occurred'}</p>
          <button 
            onClick={this.handleRetry}
            className="retry-button"
          >
            Try Again
          </button>
        </div>
      );
    }

    return children;
  }
}

export default ApiErrorBoundary;

/**
 * Default error message component that can be used with the ApiErrorBoundary
 */
export const DefaultErrorMessage = ({ error, onRetry }) => (
  <div className="error-message-container">
    <div className="error-icon">⚠️</div>
    <h3>Oops! Something went wrong</h3>
    <p className="error-details">{error?.message || 'Could not complete the request'}</p>
    <button onClick={onRetry} className="retry-button">
      Try Again
    </button>
  </div>
);
