import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref(null)
  const isLoggedIn = ref(false)

  const fetchUser = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      isLoggedIn.value = false
      currentUser.value = null
      delete axios.defaults.headers.common['Authorization']
      return
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    try {
      const res = await axios.get('http://localhost:8000/api/me')
      currentUser.value = res.data.data
      isLoggedIn.value = true
    } catch {
      isLoggedIn.value = false
      currentUser.value = null
    }
  }

  const logout = () => {
    localStorage.removeItem('access_token')
    delete axios.defaults.headers.common['Authorization']
    isLoggedIn.value = false
    currentUser.value = null
  }

  return { currentUser, isLoggedIn, fetchUser, logout }
})
