<template>
  <div class="login-view py-5 bg-gray-50 min-h-screen flex align-items-center">
    <div class="container max-w-md mx-auto">
      <div class="bg-white rounded-3xl p-5 p-sm-8 shadow-xl border border-gray-100">
        <div class="text-center mb-4">
          <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-tr from-brand-700 to-gold-500 flex items-center justify-center text-white font-bold text-2xl shadow-md">
            TN
          </div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Connexion à votre espace</h1>
          <p class="text-xs text-gray-500">Portail École Tijanes Nours Luxembourg</p>
        </div>
        
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="form-label text-xs font-bold text-gray-700 mb-1">Adresse Email</label>
            <input
              v-model="email"
              type="email"
              required
              class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm focus:ring-2 focus:ring-brand-500"
              placeholder="votre@email.com"
            />
          </div>
          <div>
            <label class="form-label text-xs font-bold text-gray-700 mb-1">Mot de passe</label>
            <input
              v-model="password"
              type="password"
              required
              class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm focus:ring-2 focus:ring-brand-500"
              placeholder="••••••••"
            />
          </div>
          <button
            type="submit"
            class="default-btn w-100 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all mt-3"
          >
            <i class="ri-login-box-line me-1"></i> Se connecter
          </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-xs text-gray-500">
          Vous n'avez pas de compte ? 
          <router-link to="/register" class="text-brand-600 font-bold hover:underline">S'inscrire</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.store'

const email = ref('')
const password = ref('')
const router = useRouter()
const authStore = useAuthStore()

async function handleLogin() {
  try {
    await authStore.login(email.value, password.value)
    if (authStore.isAdmin) router.push('/admin/dashboard')
    else if (authStore.isPaymentAgent) router.push('/payment-agent/dashboard')
    else if (authStore.isParent) router.push('/parent/dashboard')
    else router.push('/')
  } catch (err) {
    alert('Identifiants incorrects')
  }
}
</script>
