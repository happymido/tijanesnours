<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-gray-900 text-gray-300 flex flex-col shrink-0 shadow-2xl border-r border-gray-800">
      <div class="p-6 flex items-center gap-3 border-b border-gray-800">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-gold-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
          TN
        </div>
        <div>
          <h2 class="font-bold text-sm text-white">Tijanes Nours</h2>
          <span class="text-[10px] text-gold-400 font-semibold uppercase">Admin ERP</span>
        </div>
      </div>

      <!-- Navigation Menu Links -->
      <nav class="flex-grow p-4 space-y-1 text-xs font-semibold">
        <router-link
          to="/admin/dashboard"
          active-class="bg-brand-600 text-white shadow-lg shadow-brand-600/30"
          class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all"
        >
          <span class="text-base">📊</span> Tableau de Bord
        </router-link>

        <router-link
          to="/admin/users"
          active-class="bg-brand-600 text-white shadow-lg shadow-brand-600/30"
          class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all"
        >
          <span class="text-base">👥</span> Élèves, Parents & Profs
        </router-link>

        <router-link
          to="/admin/classes"
          active-class="bg-brand-600 text-white shadow-lg shadow-brand-600/30"
          class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all"
        >
          <span class="text-base">🏫</span> Structure & Classes
        </router-link>

        <router-link
          to="/admin/finance"
          active-class="bg-brand-600 text-white shadow-lg shadow-brand-600/30"
          class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all"
        >
          <span class="text-base">💶</span> Inscriptions & Règlements
        </router-link>

        <router-link
          to="/admin/settings"
          active-class="bg-brand-600 text-white shadow-lg shadow-brand-600/30"
          class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all"
        >
          <span class="text-base">⚙️</span> Paramètres École
        </router-link>
      </nav>

      <!-- User Info & Logout -->
      <div class="p-4 border-t border-gray-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
              AD
            </div>
            <div>
              <p class="text-xs font-bold text-white">Admin Syst.</p>
              <p class="text-[10px] text-gray-500">admin@tijanesnours.lu</p>
            </div>
          </div>
          <button @click="authStore.logout()" class="text-red-400 hover:text-red-300 text-xs font-bold">
            🚪
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-w-0">
      <!-- Top Navbar -->
      <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
          <h2 class="text-base font-bold text-gray-900 dark:text-white">Portail Administration Général</h2>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
          <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full font-bold">
            Année 2026 - 2027
          </span>
        </div>
      </header>

      <main class="p-8 flex-grow overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth.store'
const authStore = useAuthStore()
</script>
