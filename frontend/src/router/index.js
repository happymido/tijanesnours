import { createRouter, createWebHistory } from 'vue-router'
import PublicLayout from '../layouts/PublicLayout.vue'
import HomeView from '../views/public/HomeView.vue'
import { setupGuards } from './guards'

const routes = [
  {
    path: '/',
    component: PublicLayout,
    children: [
      { path: '', name: 'home', component: HomeView },
      { path: 'about', name: 'about', component: () => import('../views/public/AboutView.vue') },
      { path: 'courses', name: 'courses', component: () => import('../views/public/CoursesView.vue') },
      { path: 'register', name: 'register', component: () => import('../views/public/RegisterView.vue') },
      { path: 'login', name: 'login', component: () => import('../views/public/LoginView.vue') }
    ]
  },
  {
    path: '/parent',
    component: () => import('../layouts/ParentLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_PARENT' },
    children: [
      { path: 'dashboard', name: 'parent-dashboard', component: () => import('../views/parent/DashboardView.vue') }
    ]
  },
  {
    path: '/payment-agent',
    component: () => import('../layouts/PaymentAgentLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_PAYMENT_AGENT' },
    children: [
      { path: 'dashboard', name: 'payment-agent-dashboard', component: () => import('../views/payment_agent/DashboardView.vue') }
    ]
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
    children: [
      { path: 'dashboard', name: 'admin-dashboard', component: () => import('../views/admin/DashboardView.vue') }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

setupGuards(router)

export default router
