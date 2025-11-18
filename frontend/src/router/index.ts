import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { 
      path: '/login', 
      name: 'login',
      component: () => import('../pages/Login.vue'),
      meta: { requiresGuest: true }
    },

    {
      path: '/markets',
      name: 'markets',
      component: () => import('../pages/Markets.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/savings-plans',
      name: 'savings-plans',
      component: () => import('../pages/SavingsPlans.vue'),
      meta: { requiresAuth: true }
    },

    { 
      path: '/', 
      redirect: '/markets' 
    }
  ]
})

router.beforeEach(async (to) => {
  const auth = useAuth()

  // Wait for initial auth check
  if (auth.loading) {
    await auth.fetchUser()
  }

  // Redirect to login if not authenticated
  if (to.meta.requiresAuth && !auth.user) {
    return { name: 'login' }
  }

  // Redirect to markets if already authenticated
  if (to.meta.requiresGuest && auth.user) {
    return { name: 'markets' }
  }
})

export default router