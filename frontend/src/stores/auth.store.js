import { defineStore } from 'pinia'
import apiClient from '../plugins/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    userRoles: (state) => state.user?.roles || [],
    isAdmin: (state) => state.user?.roles?.includes('ROLE_ADMIN'),
    isPaymentAgent: (state) => state.user?.roles?.includes('ROLE_PAYMENT_AGENT'),
    isTeacher: (state) => state.user?.roles?.includes('ROLE_TEACHER'),
    isParent: (state) => state.user?.roles?.includes('ROLE_PARENT'),
    isStudent: (state) => state.user?.roles?.includes('ROLE_STUDENT')
  },
  actions: {
    async login(email, password) {
      this.loading = true
      try {
        const response = await apiClient.post('/auth/login', { email, password })
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        return response.data
      } finally {
        this.loading = false
      }
    },
    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/'
    }
  }
})
