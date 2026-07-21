<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <header class="bg-white dark:bg-gray-800 border-b px-6 py-4 flex justify-between items-center shadow-sm">
      <div class="flex items-center gap-3">
        <span class="w-8 h-8 rounded-lg bg-gray-900 text-white flex items-center justify-center font-bold text-sm">⚙️</span>
        <h2 class="font-bold text-lg text-gray-900 dark:text-white">Portail Administration</h2>
      </div>
      <button @click="authStore.logout()" class="text-xs font-semibold text-red-600 hover:underline">Déconnexion</button>
    </header>
    <main class="p-6">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth.store'
const authStore = useAuthStore()
</script>
