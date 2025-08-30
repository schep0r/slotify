import { ref, computed } from 'vue'

export function useFormValidation() {
  const errors = ref({})

  const clearError = (field) => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }

  const clearAllErrors = () => {
    errors.value = {}
  }

  const setError = (field, message) => {
    errors.value[field] = message
  }

  const setErrors = (errorObject) => {
    errors.value = { ...errorObject }
  }

  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  const validateEmail = (email) => {
    if (!email) {
      return 'Email is required'
    }
    if (!/\S+@\S+\.\S+/.test(email)) {
      return 'Please enter a valid email address'
    }
    return null
  }

  const validatePassword = (password, minLength = 6) => {
    if (!password) {
      return 'Password is required'
    }
    if (password.length < minLength) {
      return `Password must be at least ${minLength} characters`
    }
    return null
  }

  const validateRequired = (value, fieldName) => {
    if (!value || (typeof value === 'string' && !value.trim())) {
      return `${fieldName} is required`
    }
    return null
  }

  return {
    errors,
    clearError,
    clearAllErrors,
    setError,
    setErrors,
    hasErrors,
    validateEmail,
    validatePassword,
    validateRequired
  }
}