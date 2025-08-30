<template>
  <div class="relative" v-if="authStore.isAuthenticated">
    <!-- User Menu Button -->
    <button
      @click="isOpen = !isOpen"
      class="flex items-center space-x-2 text-white hover:text-gray-300 transition-colors"
    >
      <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
        <span class="text-sm font-medium">
          {{ userInitials }}
        </span>
      </div>
      <span class="hidden md:block">{{ authStore.user?.name }}</span>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      @click.away="isOpen = false"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50"
    >
      <!-- User Info -->
      <div class="px-4 py-2 border-b border-gray-200">
        <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
        <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
        <p class="text-sm text-green-600 font-medium">
          Balance: ${{ formatBalance(authStore.userBalance) }}
        </p>
      </div>

      <!-- Menu Items -->
      <router-link
        to="/profile"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
        @click="isOpen = false"
      >
        Profile
      </router-link>
      
      <router-link
        to="/transactions"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
        @click="isOpen = false"
      >
        Transaction History
      </router-link>

      <hr class="my-1">

      <!-- Logout Button -->
      <button
        @click="handleLogout"
        :disabled="authStore.isLoading"
        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
      >
        <span v-if="authStore.isLoading">Logging out...</span>
        <span v-else>Logout</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()
const isOpen = ref(false)

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const formatBalance = (balance) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(balance || 0)
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    isOpen.value = false
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

// Close menu when clicking outside
const clickAway = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>