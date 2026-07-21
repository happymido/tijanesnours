<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion du Contenu CMS Public</h1>
        <p class="text-xs text-gray-500">Actualités, Galerie Photos, Événements, Blog et FAQ (3 langues FR/AR/EN)</p>
      </div>
      <button class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
        + Publier un Article / Actualité
      </button>
    </div>

    <!-- CMS Posts Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
          <tr>
            <th class="py-3 px-4">Titre (FR / AR / EN)</th>
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4">Date Publication</th>
            <th class="py-3 px-4">Statut</th>
            <th class="py-3 px-4">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="article in articles" :key="article.id">
            <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ article.title }}</td>
            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold text-[10px]">{{ article.type }}</span></td>
            <td class="py-3.5 px-4 text-gray-500">{{ article.date }}</td>
            <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px]">Publié</span></td>
            <td class="py-3.5 px-4 flex gap-2">
              <button class="text-brand-600 font-bold hover:underline">Éditer</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const articles = ref([
  { id: 1, title: 'Inscriptions scolaires 2026-2027 ouvertes !', type: 'ACTUALITÉ', date: '15/06/2026' },
  { id: 2, title: 'Célébration de la fin d\'année scolaire au Centre Maryam', type: 'ÉVÉNEMENT', date: '20/05/2026' },
  { id: 3, title: 'Conseils pour l\'apprentissage ludique du Coran à la maison', type: 'BLOG', date: '10/04/2026' }
])
</script>
