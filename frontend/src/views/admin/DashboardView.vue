<template>
  <div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-gold-300">
            Tableau de Bord Général
          </span>
          <h1 class="text-3xl font-extrabold mt-2">École Tijanes Nours Luxembourg</h1>
          <p class="text-brand-100 text-sm mt-1">Gestion administrative, pédagogique et financière (ASBL RCS F12999)</p>
        </div>
        <div class="flex gap-3">
          <button class="px-4 py-2.5 bg-gold-500 hover:bg-gold-400 text-gray-900 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
            <span>+</span> Inscrire un élève
          </button>
          <button class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl transition-all">
            Exporter Rapports
          </button>
        </div>
      </div>
    </div>

    <!-- Key KPI Statistics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Élèves</span>
          <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">🎓</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900 dark:text-white">184</span>
          <span class="text-xs font-bold text-emerald-600">+12% cette année</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Répartis sur 12 classes</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Enseignants</span>
          <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">👨‍🏫</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900 dark:text-white">14</span>
          <span class="text-xs font-bold text-gray-500">Arabe & Coran</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">100% qualifiés</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Taux de Présence</span>
          <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">📈</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900 dark:text-white">96.4%</span>
          <span class="text-xs font-bold text-emerald-600">Moyenne globale</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Suivi trimestriel</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Chiffre d'Affaires</span>
          <span class="w-10 h-10 rounded-xl bg-gold-50 text-gold-600 flex items-center justify-center text-lg">💶</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900 dark:text-white">82 800 €</span>
          <span class="text-xs font-bold text-emerald-600">88% encaissé</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Échéances 2026-2027</p>
      </div>
    </div>

    <!-- Main Content Tables & Activity Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Recent Students List (2 cols) -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Inscriptions Récents & Élèves</h3>
            <p class="text-xs text-gray-500">Derniers dossiers enregistrés</p>
          </div>
          <span class="text-xs font-semibold text-brand-600 cursor-pointer hover:underline">Voir tout (184)</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
              <tr>
                <th class="py-3 px-4">Élève</th>
                <th class="py-3 px-4">Âge / Niveau</th>
                <th class="py-3 px-4">Créneau</th>
                <th class="py-3 px-4">Statut Inscription</th>
                <th class="py-3 px-4">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="student in mockStudents" :key="student.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                    {{ student.firstName[0] }}{{ student.lastName[0] }}
                  </div>
                  <div>
                    <div>{{ student.firstName }} {{ student.lastName }}</div>
                    <div class="text-[10px] text-gray-400">Parent: {{ student.parentName }}</div>
                  </div>
                </td>
                <td class="py-3.5 px-4 font-medium text-gray-600 dark:text-gray-300">
                  {{ student.age }} ans • <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">{{ student.level }}</span>
                </td>
                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">{{ student.slot }}</td>
                <td class="py-3.5 px-4">
                  <span
                    :class="student.status === 'VALIDATED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                  >
                    {{ student.status === 'VALIDATED' ? 'Validé' : 'En attente' }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <button class="text-brand-600 font-bold hover:underline">Fiche</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Column: Quick Status & Financial Breakdown (1 col) -->
      <div class="space-y-6">
        <!-- Financial Widget -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
            Répartition des Paiements
          </h3>
          <div class="space-y-3">
            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span>Stripe / Cartes bancaires</span>
                <span class="text-brand-600 font-bold">52%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <div class="h-full bg-brand-600 w-[52%]"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span>Prélèvements SEPA</span>
                <span class="text-emerald-600 font-bold">28%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <div class="h-full bg-emerald-600 w-[28%]"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span>Virements bancaires (RF ISO 11649)</span>
                <span class="text-blue-600 font-bold">15%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <div class="h-full bg-blue-600 w-[15%]"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span>Espèces / Manuel</span>
                <span class="text-gold-500 font-bold">5%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <div class="h-full bg-gold-500 w-[5%]"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick System Status -->
        <div class="bg-emerald-900/90 text-white rounded-2xl p-6 shadow-md space-y-3">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Système Opérationnel</span>
          </div>
          <h4 class="font-bold text-base">Prochain Prélèvement SEPA Batch</h4>
          <p class="text-xs text-emerald-100">Génération du fichier ISO 20022 Pain.008 planifiée le 1er du mois prochain.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const mockStudents = ref([
  { id: 1, firstName: 'Youssef', lastName: 'Benali', age: 8, level: 'Débutant 2', slot: 'Samedi 09:00 - 12:00', parentName: 'Karim Benali', status: 'VALIDATED' },
  { id: 2, firstName: 'Maryam', lastName: 'El Amrani', age: 6, level: 'Éveil 2', slot: 'Dimanche 10:00 - 13:00', parentName: 'Fatima El Amrani', status: 'VALIDATED' },
  { id: 3, firstName: 'Adam', lastName: 'Mansouri', age: 11, level: 'Intermédiaire 1', slot: 'Samedi 14:00 - 17:00', parentName: 'Tariq Mansouri', status: 'PENDING' },
  { id: 4, firstName: 'Inès', lastName: 'Khadiri', age: 14, level: 'Avancé Tajwid', slot: 'Dimanche 14:00 - 17:00', parentName: 'Siham Khadiri', status: 'VALIDATED' },
  { id: 5, firstName: 'Rayane', lastName: 'Bennani', age: 5, level: 'Maternelle 1', slot: 'Samedi 09:00 - 12:00', parentName: 'Omar Bennani', status: 'PENDING' }
])
</script>
