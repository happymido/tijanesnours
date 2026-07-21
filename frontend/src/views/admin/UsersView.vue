<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Utilisateurs</h1>
        <p class="text-xs text-gray-500">Élèves, Parents, Enseignants et Agents Administratifs</p>
      </div>
      <button @click="showAddModal = true" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
        + Nouvel Utilisateur
      </button>
    </div>

    <!-- Tabs Filter -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeTab = 'ALL'" :class="activeTab === 'ALL' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Tous (201)</button>
      <button @click="activeTab = 'STUDENTS'" :class="activeTab === 'STUDENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Élèves (184)</button>
      <button @click="activeTab = 'PARENTS'" :class="activeTab === 'PARENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Parents (120)</button>
      <button @click="activeTab = 'TEACHERS'" :class="activeTab === 'TEACHERS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Enseignants (14)</button>
      <button @click="activeTab = 'AGENTS'" :class="activeTab === 'AGENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Agents Comptables (3)</button>
    </div>

    <!-- Users Datatable -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Utilisateur</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Rôle RBAC</th>
              <th class="py-3 px-4">Langue</th>
              <th class="py-3 px-4">Statut</th>
              <th class="py-3 px-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs">
                  {{ user.name[0] }}
                </div>
                <span>{{ user.name }}</span>
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                  {{ user.role }}
                </span>
              </td>
              <td class="py-3.5 px-4 uppercase font-semibold text-gray-500">{{ user.locale }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Actif</span>
              </td>
              <td class="py-3.5 px-4 flex gap-2">
                <button class="text-brand-600 font-bold hover:underline">Éditer</button>
                <button class="text-red-600 font-bold hover:underline">Suspendre</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('ALL')
const showAddModal = ref(false)

const users = ref([
  { id: 1, name: 'Admin Général', email: 'admin@tijanesnours.lu', role: 'ROLE_ADMIN', locale: 'fr' },
  { id: 2, name: 'Agent Comptable', email: 'comptable@tijanesnours.lu', role: 'ROLE_PAYMENT_AGENT', locale: 'fr' },
  { id: 3, name: 'Karim Benali', email: 'parent@tijanesnours.lu', role: 'ROLE_PARENT', locale: 'fr' },
  { id: 4, name: 'Cheikh Mahmoud', email: 'mahmoud@tijanesnours.lu', role: 'ROLE_TEACHER', locale: 'ar' },
  { id: 5, name: 'Youssef Benali', email: 'youssef@student.lu', role: 'ROLE_STUDENT', locale: 'fr' }
])
</script>
