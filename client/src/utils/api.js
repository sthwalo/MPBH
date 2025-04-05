import axios from 'axios';

/**
 * Configured Axios instance for API requests
 * Sets up base URL, request interceptors, response handling, and authentication
 */
const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Request interceptor for adding auth tokens
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('mpbh_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling common errors
api.interceptors.response.use(
  response => {
    // Successfully received response
    return response.data;
  },
  error => {
    const { response } = error;
    
    // Handle authentication errors
    if (response && response.status === 401) {
      localStorage.removeItem('mpbh_token');
      localStorage.removeItem('mpbh_user');
      // Redirect to login if needed
      // window.location.href = '/login';
    }
    
    // Format error message
    const errorMessage = 
      (response && response.data && response.data.message) ||
      error.message ||
      'Network error occurred';
    
    return Promise.reject({
      status: response ? response.status : null,
      message: errorMessage,
      originalError: error
    });
  }
);

export default api;
