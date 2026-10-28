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
            :disabled="authStore.loading"
            class="default-btn w-100 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all mt-3 disabled:opacity-50"
          >
            <i class="ri-login-box-line me-1"></i>
            {{ authStore.loading ? 'Connexion en cours...' : 'Se connecter' }}
          </button>
        </form>

        <!-- Comptes de démonstration / Accès Rapide -->
        <div class="mt-5 pt-4 border-t border-gray-100">
          <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">
            Comptes de test (cliquez pour préremplir)
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              type="button"
              @click="fillCredentials('mahmoud@tijanesnours.lu', 'teacherpassword123')"
              class="px-2.5 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-semibold text-left transition-all flex items-center gap-1.5"
            >
              <span>👨‍🏫</span> <span>Enseignant</span>
            </button>
            <button
              type="button"
              @click="fillCredentials('youssef@student.lu', 'studentpassword123')"
              class="px-2.5 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 font-semibold text-left transition-all flex items-center gap-1.5"
            >
              <span>🎓</span> <span>Élève</span>
            </button>
            <button
              type="button"
              @click="fillCredentials('parent@tijanesnours.lu', 'parentpassword123')"
              class="px-2.5 py-1.5 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100 font-semibold text-left transition-all flex items-center gap-1.5"
            >
              <span>👨‍👩‍👧</span> <span>Parent</span>
            </button>
            <button
              type="button"
              @click="fillCredentials('admin@tijanesnours.lu', 'adminpassword123')"
              class="px-2.5 py-1.5 rounded-lg border border-purple-200 bg-purple-50 text-purple-800 hover:bg-purple-100 font-semibold text-left transition-all flex items-center gap-1.5"
            >
              <span>⚡</span> <span>Admin</span>
            </button>
          </div>
        </div>

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

function fillCredentials(testEmail, testPassword) {
  email.value = testEmail
  password.value = testPassword
}

async function handleLogin() {
  try {
    await authStore.login(email.value, password.value)
    if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else if (authStore.isPaymentAgent) {
      router.push('/payment-agent/dashboard')
    } else if (authStore.isTeacher) {
      router.push('/teacher/dashboard')
    } else if (authStore.isParent) {
      router.push('/parent/dashboard')
    } else if (authStore.isStudent) {
      router.push('/student/dashboard')
    } else {
      router.push('/')
    }
  } catch (err) {
    alert('Identifiants incorrects. Veuillez vérifier votre adresse email et votre mot de passe.')
  }
}
</script>
