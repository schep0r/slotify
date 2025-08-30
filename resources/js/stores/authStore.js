import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout, getCurrentUser } from '../api/auth.js'
import api from '../utils/apiClient.js'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userBalance = computed(() => user.value?.balance || 0)

  // Actions
  const login = async (credentials) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await apiLogin(credentials)
      const { user: userData, token: authToken } = response.data.data
      
      // Store user and token
      user.value = userData
      token.value = authToken
      
      // Store token in localStorage
      localStorage.setItem('auth_token', authToken)
      
      // Set default authorization header for future requests
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      return response.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    isLoading.value = true
    error.value = null
    
    try {
      if (token.value) {
        await apiLogout()
      }
    } catch (err) {
      // Even if logout fails on server, we should clear local state
      console.warn('Logout request failed:', err.message)
    } finally {
      // Clear local state
      user.value = null
      token.value = null
      
      // Remove token from localStorage
      localStorage.removeItem('auth_token')
      
      // Remove authorization header
      delete api.defaults.headers.common['Authorization']
      
      isLoading.value = false
    }
  }

  const fetchUser = async () => {
    if (!token.value) return
    
    isLoading.value = true
    error.value = null
    
    try {
      const response = await getCurrentUser()
      user.value = response.data.data.user
      return response.data
    } catch (err) {
      error.value = err.message
      // If fetching user fails, likely token is invalid
      await logout()
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const updateBalance = (newBalance) => {
    if (user.value) {
      user.value.balance = newBalance
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Initialize auth state on store creation
  const initializeAuth = () => {
    if (token.value) {
      // Set authorization header if token exists
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      // Fetch user data to validate token
      fetchUser().catch(() => {
        // Token is invalid, clear it
        logout()
      })
    }
  }

  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    userBalance,
    
    // Actions
    login,
    logout,
    fetchUser,
    updateBalance,
    clearError,
    initializeAuth
  }
})