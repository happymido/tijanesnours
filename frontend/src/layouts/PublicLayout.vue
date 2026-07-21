<template>
  <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-700 to-gold-500 flex items-center justify-center text-white font-bold text-xl shadow-md">
            TN
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">École Tijanes Nours</h1>
            <p class="text-xs text-brand-600 dark:text-gold-400 font-medium">Langue Arabe & Coran • Luxembourg</p>
          </div>
        </router-link>

        <!-- Navigation Links -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-700 dark:text-gray-200">
          <router-link to="/" class="hover:text-brand-600 dark:hover:text-gold-400 transition-colors">{{ t('nav.home') }}</router-link>
          <router-link to="/courses" class="hover:text-brand-600 dark:hover:text-gold-400 transition-colors">{{ t('nav.courses') }}</router-link>
          <router-link to="/about" class="hover:text-brand-600 dark:hover:text-gold-400 transition-colors">{{ t('nav.about') }}</router-link>
        </nav>

        <!-- Controls: Language Selector + Auth CTA -->
        <div class="flex items-center gap-4">
          <!-- Language Selector -->
          <div class="relative">
            <select
              v-model="currentLang"
              @change="changeLang"
              class="bg-gray-100 dark:bg-gray-700 border-none text-xs font-semibold rounded-lg px-3 py-2 text-gray-800 dark:text-gray-200 cursor-pointer focus:ring-2 focus:ring-brand-500"
            >
              <option value="fr">🇫🇷 FR</option>
              <option value="ar">🇱🇺/🇦🇪 العربية (RTL)</option>
              <option value="en">🇬🇧 EN</option>
            </select>
          </div>

          <!-- Register / Login Button -->
          <template v-if="!authStore.isAuthenticated">
            <router-link
              to="/register"
              class="hidden sm:inline-flex px-4 py-2 text-xs font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-500/20 transition-all"
            >
              {{ t('nav.register') }}
            </router-link>
            <router-link
              to="/login"
              class="px-4 py-2 text-xs font-bold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
            >
              {{ t('nav.login') }}
            </router-link>
          </template>
          <template v-else>
            <button
              @click="goToDashboard"
              class="px-4 py-2 text-xs font-bold rounded-lg text-white bg-gold-600 hover:bg-gold-500 transition-all shadow-md"
            >
              {{ t('nav.dashboard') }}
            </button>
          </template>
        </div>
      </div>
    </header>

    <!-- Main View Outlet -->
    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 text-sm border-t border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-white font-bold text-lg mb-3">École Tijanes Nours ASBL</h3>
          <p class="text-xs text-gray-400 leading-relaxed">
            Association à but non lucratif enregistrée au Luxembourg (RCS F12999).
            Enseignement de la langue arabe et du Coran pour enfants de 4 à 16 ans.
          </p>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-3">Coordonnées</h4>
          <p class="text-xs text-gray-400">Centre Maryam / LJM Luxembourg</p>
          <p class="text-xs text-gray-400 mt-1">Email: contact@tijanesnours.lu</p>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-3">Langues</h4>
          <div class="flex gap-2">
            <button @click="setLang('fr')" class="px-2 py-1 bg-gray-800 rounded text-xs text-gray-300 hover:text-white">Français</button>
            <button @click="setLang('ar')" class="px-2 py-1 bg-gray-800 rounded text-xs text-gray-300 hover:text-white">العربية</button>
            <button @click="setLang('en')" class="px-2 py-1 bg-gray-800 rounded text-xs text-gray-300 hover:text-white">English</button>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-800 text-center py-4 text-xs text-gray-500">
        &copy; 2026 École Tijanes Nours. Tous droits réservés.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { setLanguage } from '../plugins/i18n'

const { t, locale } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const currentLang = ref(locale.value)

function changeLang() {
  setLanguage(currentLang.value)
}

function setLang(lang) {
  currentLang.value = lang
  setLanguage(lang)
}

function goToDashboard() {
  if (authStore.isAdmin) router.push('/admin/dashboard')
  else if (authStore.isPaymentAgent) router.push('/payment-agent/dashboard')
  else if (authStore.isParent) router.push('/parent/dashboard')
  else router.push('/')
}
</script>
