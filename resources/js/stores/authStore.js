import { defineStore } from 'pinia'
import { login as apiLogin, logout as apiLogout, getCurrentUser } from '../api/auth.js'
import api from '../utils/apiClient.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    isLoading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    userBalance: (state) => state.user?.balance || 0,
  },
  actions: {
    async login(credentials) {
      this.isLoading = true
      this.error = null

      try {
        const response = await apiLogin(credentials)
        const { user: userData, token: authToken } = response.data.data

        // Store user and token
        this.user = userData
        this.token = authToken

        // Store token in localStorage
        localStorage.setItem('auth_token', authToken)

        // Set default authorization header for future requests
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`

        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      this.isLoading = true
      this.error = null

      try {
        if (this.token) {
          await apiLogout()
        }
      } catch (err) {
        // Even if logout fails on server, we should clear local state
        console.warn('Logout request failed:', err.message)
      } finally {
        // Clear local state
        this.user = null
        this.token = null

        // Remove token from localStorage
        localStorage.removeItem('auth_token')

        // Remove authorization header
        delete api.defaults.headers.common['Authorization']

        this.isLoading = false
      }
    },

    async fetchUser() {
      if (!this.token) return

      this.isLoading = true
      this.error = null

      try {
        const response = await getCurrentUser()
        this.user = response.data.data.user
        return response.data
      } catch (err) {
        this.error = err.message
        // If fetching user fails, likely token is invalid
        await this.logout()
        throw err
      } finally {
        this.isLoading = false
      }
    },

    updateBalance(newBalance) {
      if (this.user) {
        this.user.balance = newBalance
      }
    },

    clearError() {
      this.error = null
    },

    // Initialize auth state on store creation
    initializeAuth() {
        if (this.token) {
        // Set authorization header if token exists
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        // Fetch user data to validate token
        this.fetchUser().catch(() => {
          // Token is invalid, clear it
          this.logout()
        })
      }
    }
  }
})
