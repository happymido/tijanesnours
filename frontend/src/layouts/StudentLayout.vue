<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col">
    <!-- Top Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-3.5 flex justify-between items-center shadow-sm sticky top-0 z-30">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-blue-600 to-amber-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
          🎓
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-extrabold text-sm sm:text-base text-gray-900 dark:text-white leading-tight">École Tijanes Nours</h1>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
              Espace Élève
            </span>
          </div>
          <p class="text-[11px] text-gray-500 hidden sm:block">Notes, assiduité, récitation et emploi du temps</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
          <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block leading-tight">
            {{ studentDisplayName }}
          </span>
          <span class="text-[10px] text-brand-600 font-semibold">Élève Inscrit</span>
        </div>

        <button
          @click="authStore.logout()"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 font-semibold text-xs transition-all border border-red-200 dark:border-red-900/50"
          title="Se déconnecter"
        >
          <i class="ri-logout-box-r-line text-sm"></i>
          <span class="hidden sm:inline">Déconnexion</span>
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow p-4 sm:p-8 max-w-7xl w-full mx-auto">
      <router-view />
    </main>

    <!-- Simple Footer -->
    <footer class="py-4 border-t border-gray-200 dark:border-gray-800 text-center text-xs text-gray-400">
      École Tijanes Nours Luxembourg • Espace Élève & Apprentissage
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth.store'

const authStore = useAuthStore()

const studentDisplayName = computed(() => {
  if (authStore.user?.email === 'youssef@student.lu') {
    return 'Youssef Benali'
  }
  if (authStore.user?.email === 'aya@student.lu') {
    return 'Aya Benali'
  }
  return authStore.user?.email || 'Élève'
})
</script>
