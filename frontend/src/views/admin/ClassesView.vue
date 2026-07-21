<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Structure Scolaire & Classes</h1>
        <p class="text-xs text-gray-500">Années scolaires, Niveaux (4 à 16 ans), Classes, Matières et Salles</p>
      </div>
      <button class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
        + Créer une Classe
      </button>
    </div>

    <!-- Levels & Classrooms Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="cls in classrooms" :key="cls.id" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-3">
        <div class="flex justify-between items-start">
          <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
              {{ cls.level }}
            </span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ cls.name }}</h3>
          </div>
          <span class="text-xs font-bold text-emerald-600">{{ cls.studentsCount }} élèves</span>
        </div>

        <div class="text-xs text-gray-500 space-y-1">
          <p>👨‍🏫 <strong>Professeur Principal :</strong> {{ cls.teacher }}</p>
          <p>⏰ <strong>Horaire :</strong> {{ cls.schedule }}</p>
          <p>🚪 <strong>Salle :</strong> {{ cls.room }}</p>
        </div>

        <div class="pt-2 border-t flex justify-between items-center text-xs dark:border-gray-700">
          <span class="font-semibold text-gray-400">Année 2026-2027</span>
          <button class="text-brand-600 font-bold hover:underline">Gérer la Classe</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const classrooms = ref([
  { id: 1, name: 'Classe Éveil 1', level: 'Maternelle (4-5 ans)', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 1', studentsCount: 16 },
  { id: 2, name: 'Classe Débutant 2A', level: 'Primaire (6-8 ans)', teacher: 'Oustaz Hassan', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 2', studentsCount: 18 },
  { id: 3, name: 'Classe Intermédiaire 1', level: 'Moyen (9-12 ans)', teacher: 'Mme Souad', schedule: 'Dimanche 10:00 - 13:00', room: 'Salle Paladium 3', studentsCount: 15 },
  { id: 4, name: 'Classe Avancé Tajwid', level: 'Ados (13-16 ans)', teacher: 'Cheikh Mahmoud', schedule: 'Dimanche 14:00 - 17:00', room: 'Grand Amphithéâtre', studentsCount: 14 }
])
</script>
