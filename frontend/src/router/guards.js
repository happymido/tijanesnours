import { useAuthStore } from '../stores/auth.store'

export function setupGuards(router) {
  router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    if (to.meta.role) {
      const userRoles = authStore.userRoles
      if (!userRoles.includes(to.meta.role) && !userRoles.includes('ROLE_ADMIN')) {
        return next({ name: 'home' })
      }
    }

    next()
  })
}
