import api from './../utils/apiClient.js'

// Export auth functions
export * from './auth.js'

/**
 * Handles API errors in a consistent way
 * @param {Error} error - The error object from axios
 * @param {string} resourceName - The name of the resource being accessed
 * @param {string|number} [resourceId] - Optional ID of the specific resource
 * @throws {Error} A formatted error message
 */
const handleApiError = (error, resourceName, resourceId = null) => {
  if (error.response) {
    // The request was made and the server responded with a status code
    // that falls out of the range of 2xx
    if (error.response.status === 404 && resourceId) {
      throw new Error(`${resourceName} with ID ${resourceId} not found`);
    } else {
      throw new Error(`Error fetching ${resourceName.toLowerCase()}: ${error.response.data.message || error.response.statusText}`);
    }
  } else if (error.request) {
    // The request was made but no response was received
    throw new Error('No response from server. Please try again later.');
  } else {
    // Something happened in setting up the request that triggered an Error
    throw new Error(`Error: ${error.message}`);
  }
};

/**
 * Fetches all games from the backend
 * @returns {Promise} Promise that resolves to the games data
 * @throws {Error} If an error occurs during the API request
 */
export const getGames = () => {
  return api.get('/games')
    .catch(error => {
      // This will throw an error which will cause the promise to be rejected
      handleApiError(error, 'Games');
      // In case handleApiError doesn't throw (which shouldn't happen)
      throw error;
    });
};

/**
 * Fetches a specific game by ID from the backend
 * @param {string|number} gameId - The ID of the game to fetch
 * @returns {Promise} Promise that resolves to the game data
 * @throws {Error} If the game is not found or another error occurs
 */
export const getGame = (gameId) => {
  if (!gameId) {
    return Promise.reject(new Error('Game ID is required'));
  }

  return api.get(`/games/${gameId}`)
    .catch(error => {
      // This will throw an error which will cause the promise to be rejected
      handleApiError(error, 'Game', gameId);
      // In case handleApiError doesn't throw (which shouldn't happen)
      throw error;
    });
};

export const getGameSettings = (gameId) => {
    if (!gameId) {
        return Promise.reject(new Error('Game ID is required'));
    }

    return api.get(`/games/${gameId}/settings`)
        .catch(error => {
            // This will throw an error which will cause the promise to be rejected
            handleApiError(error, 'Game', gameId);
            // In case handleApiError doesn't throw (which shouldn't happen)
            throw error;
        });
};
