// stores/auth.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref(null)
  const isLoggedIn = ref(false)
  const config = useRuntimeConfig();
  const apiBase = config.public.apiBaseUrl;
  const fetchUser = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      isLoggedIn.value = false
      currentUser.value = null
      return
    }

    try {
      const res = await axios.get(`${apiBase}/me`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
      currentUser.value = res.data.data
      isLoggedIn.value = true
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } catch (err) {
      isLoggedIn.value = false
      currentUser.value = null
      localStorage.removeItem('access_token')
      delete axios.defaults.headers.common['Authorization']
    }
  }

  const logout = () => {
    localStorage.removeItem('access_token')
    delete axios.defaults.headers.common['Authorization']
    isLoggedIn.value = false
    currentUser.value = null
  }

  return {
    currentUser,
    isLoggedIn,
    fetchUser,
    logout,
  }
})
