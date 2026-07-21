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
        const response = await apiClient.post('/auth/login', {
          username: email,
          email: email,
          password: password
        })
        
        this.token = response.data.token
        
        // Decode JWT payload or use returned user
        if (response.data.user) {
          this.user = response.data.user
        } else {
          // Fallback parsing roles from JWT payload
          const base64Url = this.token.split('.')[1]
          const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
          const payload = JSON.parse(window.atob(base64))
          this.user = {
            email: payload.username || email,
            roles: payload.roles || ['ROLE_USER'],
            locale: 'fr'
          }
        }
        
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
