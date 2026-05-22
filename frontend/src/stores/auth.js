import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../utils/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || '')

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || '')

  async function login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    const data = response.data.data
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
    return data
  }

  async function register(form) {
    const response = await api.post('/auth/register', form)
    const data = response.data.data
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
    return data
  }

  async function logout() {
    await api.post('/auth/logout')
    user.value = null
    token.value = ''
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, userRole, login, register, logout }
})
