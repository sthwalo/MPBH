import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  transformResponse: [function (data) {
    try {
      return JSON.parse(data);
    } catch (e) {
      return data;
    }
  }]
});

// Request interceptor for auth token
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('mpbh_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => Promise.reject(error)
);

// Enhanced response interceptor
api.interceptors.response.use(
  response => {
    if (typeof response.data === 'string' && 
        (response.data.startsWith('<!DOCTYPE') || 
         response.data.startsWith('<br />') || 
         response.data.startsWith('<html'))) {
      throw {
        response: {
          status: 500,
          data: {
            message: 'Server returned HTML instead of JSON',
            htmlError: response.data
          }
        }
      };
    }
    return response.data;
  },
  error => {
    if (!error.response) {
      return Promise.reject({
        status: 0,
        message: 'Network Error - Unable to connect to server',
        originalError: error
      });
    }

    const { status, data } = error.response;
    
    if (status === 401) {
      localStorage.removeItem('mpbh_token');
      localStorage.removeItem('mpbh_user');
    }
    
    if (typeof data === 'string' && (data.startsWith('<') || data.includes('</html>'))) {
      return Promise.reject({
        status,
        message: 'Server returned an HTML error page',
        htmlError: data,
        originalError: error
      });
    }
    
    return Promise.reject({
      status,
      message: data?.message || error.message || `Request failed with status ${status}`,
      errors: data?.errors,
      originalError: error
    });
  }
);

// API functions
export const fetchPackageTiers = async () => {
  try {
    return await api.get('/packages/tiers');
  } catch (error) {
    if (error.htmlError) {
      console.error('Server Error:', error.htmlError);
      throw new Error('Server configuration error - received HTML response');
    }
    throw error;
  }
};

// Debugging in development
if (process.env.NODE_ENV === 'development') {
  api.interceptors.request.use(request => {
    console.log('Starting Request', request);
    return request;
  });

  api.interceptors.response.use(response => {
    console.log('Response:', response);
    return response;
  });
}

export default api;