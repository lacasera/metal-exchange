import { defineStore } from 'pinia'

const API_BASE = import.meta.env.VITE_API_BASE_URL

export const useAuth = defineStore('auth', {
  state: () => ({
    user: null as null | { id: number; name: string; email: string },
    token: null as string | null,
    loading: true,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token
  },

  persist: {
    key: 'auth',
    storage: localStorage
  },

  actions: {
    async fetchUser() {
      try {
        if (!this.token) {
          this.loading = false
          return
        }

        const res = await fetch(`${API_BASE}/user`, {
          credentials: 'include',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json'
          }
        })

        if (res.ok) {
          const json = await res.json()
          this.user = json.data || json.user || json
        } else {
          this.clearAuth()
        }
      } finally {
        this.loading = false
      }
    },

    async login(email: string, password: string) {
      // Get CSRF cookie first
      await fetch(`${API_BASE.replace('/api', '')}/sanctum/csrf-cookie`, {
        credentials: 'include'
      })

      // Attempt login
      const res = await fetch(`${API_BASE}/auth/login`, {
        method: 'POST',
        credentials: 'include',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
      })

      if (!res.ok) {
        const error = await res.json()
        throw new Error(error.message || 'Invalid credentials')
      }

      const json = await res.json()
      
      if (json.success && json.data) {
        this.user = json.data.user
        this.token = json.data.token
      }
      
      return json
    },

    async logout() {
      if (this.token) {
        await fetch(`${API_BASE}/auth/logout`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json'
          }
        })
      }

      this.clearAuth()
    },

    clearAuth() {
      this.user = null
      this.token = null
    },
  },
  
})