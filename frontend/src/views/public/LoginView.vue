<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-700">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Connexion à votre espace</h1>
      
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Adresse Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500"
            placeholder="votre@email.com"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Mot de passe</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500"
            placeholder="••••••••"
          />
        </div>
        <button
          type="submit"
          class="w-full py-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2"
        >
          Se connecter
        </button>
      </form>
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
