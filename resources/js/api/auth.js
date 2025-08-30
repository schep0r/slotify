import api from '../utils/apiClient.js'

/**
 * Handles API errors in a consistent way
 * @param {Error} error - The error object from axios
 * @param {string} action - The action being performed
 * @throws {Error} A formatted error message
 */
const handleAuthError = (error, action) => {
  if (error.response) {
    const status = error.response.status;
    const message = error.response.data?.message || error.response.statusText;

    if (status === 401) {
      throw new Error('Invalid credentials. Please check your email and password.');
    } else if (status === 422) {
      // Validation errors
      const errors = error.response.data?.errors;
      if (errors) {
        const firstError = Object.values(errors)[0];
        throw new Error(Array.isArray(firstError) ? firstError[0] : firstError);
      }
      throw new Error(message);
    } else {
      throw new Error(`Error during ${action}: ${message}`);
    }
  } else if (error.request) {
    throw new Error('No response from server. Please try again later.');
  } else {
    throw new Error(`Error: ${error.message}`);
  }
};

/**
 * Login user with email and password
 * @param {Object} credentials - Login credentials
 * @param {string} credentials.email - User email
 * @param {string} credentials.password - User password
 * @returns {Promise} Promise that resolves to the login response
 */
export const login = (credentials) => {
  return api.post('/auth/login', credentials)
    .catch(error => {
      handleAuthError(error, 'login');
      throw error;
    });
};

/**
 * Logout current user
 * @returns {Promise} Promise that resolves to the logout response
 */
export const logout = () => {
  return api.post('/auth/logout')
    .catch(error => {
      handleAuthError(error, 'logout');
      throw error;
    });
};

/**
 * Get current authenticated user information
 * @returns {Promise} Promise that resolves to the user data
 */
export const getCurrentUser = () => {
  return api.get('/auth/me')
    .catch(error => {
      handleAuthError(error, 'fetching user data');
      throw error;
    });
};
