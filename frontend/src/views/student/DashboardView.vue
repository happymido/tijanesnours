<template>
  <div class="space-y-8">
    <!-- Student Hero Banner -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-800 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-amber-200">
          Espace Apprenant
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold mt-2 flex items-center gap-2">
          <span>Salam, {{ studentName }}</span> 🌟
        </h1>
        <p class="text-blue-100 text-sm mt-1">
          « وَقُل رَّبِّ زِدْنِي عِلْمًا » — Et dis : Ô mon Seigneur, accrois mes connaissances.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-amber-200 block">Classe</span>
          <strong class="text-sm sm:text-base font-extrabold">{{ className }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-amber-200 block">Enseignant</span>
          <strong class="text-sm sm:text-base font-extrabold">{{ teacherName }}</strong>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Assiduité</span>
          <span class="text-lg">📅</span>
        </div>
        <p class="text-2xl font-black text-emerald-600">98%</p>
        <span class="text-[11px] text-gray-400">Présent & Ponctuel</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Moyenne</span>
          <span class="text-lg">📝</span>
        </div>
        <p class="text-2xl font-black text-brand-600">18.5 / 20</p>
        <span class="text-[11px] text-emerald-600 font-semibold">Mention Très Bien</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Mémorisation</span>
          <span class="text-lg">📖</span>
        </div>
        <p class="text-2xl font-black text-amber-500">12 Sourates</p>
        <span class="text-[11px] text-gray-400">Juz' Amma validé à 35%</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Année Scolaire</span>
          <span class="text-lg">🏫</span>
        </div>
        <p class="text-2xl font-black text-indigo-600">2026-2027</p>
        <span class="text-[11px] text-gray-400">Semestre en cours</span>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button
        @click="activeTab = 'SCHEDULE'"
        :class="activeTab === 'SCHEDULE' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3' : 'text-gray-500 pb-3'"
      >
        🕒 Mon Emploi du Temps du Samedi
      </button>
      <button
        @click="activeTab = 'TAJWID'"
        :class="activeTab === 'TAJWID' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3' : 'text-gray-500 pb-3'"
      >
        📖 Suivi Sourates & Tajwid
      </button>
      <button
        @click="activeTab = 'DOCUMENTS'"
        :class="activeTab === 'DOCUMENTS' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3' : 'text-gray-500 pb-3'"
      >
        📄 Mes Bulletins & Attestations
      </button>
    </div>

    <!-- TAB 1: Emploi du Temps -->
    <div v-if="activeTab === 'SCHEDULE'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-700">
          <div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">Planning Hebdomadaire (Samedi)</h3>
            <p class="text-xs text-gray-500">Horaires des cours à l'école Tijanes Nours Luxembourg (32, Rue Dernier Sol)</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
            09:00 - 12:00
          </span>
        </div>

        <div class="space-y-3">
          <div
            v-for="(session, idx) in scheduleSessions"
            :key="idx"
            class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3"
            :class="session.isBreak ? 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-100 dark:border-gray-600'"
          >
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ session.icon }}</span>
              <div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ session.subject }}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ session.description }}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="font-black text-xs px-3 py-1 rounded-lg" :class="session.isBreak ? 'bg-amber-200 text-amber-900' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200'">
                {{ session.time }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: Suivi Sourates & Tajwid -->
    <div v-if="activeTab === 'TAJWID'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-700">
          <div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">Carnet de Mémorisation (Juz' Amma)</h3>
            <p class="text-xs text-gray-500">Sourates récitées et validées auprès de Cheikh Mahmoud</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
            12 Validées
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="surah in surahList"
            :key="surah.name"
            class="p-3.5 rounded-xl border flex items-center justify-between"
            :class="surah.status === 'DONE' ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-amber-50/50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-800'"
          >
            <div>
              <p class="font-bold text-xs text-gray-900 dark:text-white">{{ surah.name }}</p>
              <span class="text-[10px] text-gray-400">{{ surah.verses }} versets</span>
            </div>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="surah.status === 'DONE' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white'"
            >
              {{ surah.status === 'DONE' ? '✓ Validée' : 'En cours' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: Bulletins & Documents -->
    <div v-if="activeTab === 'DOCUMENTS'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <div class="border-b pb-4 mb-4 dark:border-gray-700">
          <h3 class="font-bold text-lg text-gray-900 dark:text-white">Documents Scolaires & Bulletins</h3>
          <p class="text-xs text-gray-500">Téléchargez vos attestations officielles et bulletins trimestriels</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-3 mb-3">
                <span class="text-3xl">📄</span>
                <div>
                  <h4 class="font-bold text-sm text-gray-900 dark:text-white">Bulletin Scolaire - 1er Trimestre</h4>
                  <p class="text-xs text-gray-500">Moyenne: 18.5/20 • Félicitations du corps professoral</p>
                </div>
              </div>
            </div>
            <button
              @click="downloadDocument('Bulletin du 1er Trimestre 2026/2027')"
              class="mt-3 w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 shadow"
            >
              <span>📥</span> Télécharger le Bulletin PDF
            </button>
          </div>

          <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-3 mb-3">
                <span class="text-3xl">🪪</span>
                <div>
                  <h4 class="font-bold text-sm text-gray-900 dark:text-white">Carte Scolaire d'Élève 2026/2027</h4>
                  <p class="text-xs text-gray-500">Matricule: TN-2026-004 • École Tijanes Nours</p>
                </div>
              </div>
            </div>
            <button
              @click="downloadDocument('Carte Scolaire Numérique')"
              class="mt-3 w-full py-2.5 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2"
            >
              <span>📥</span> Télécharger la Carte d'Élève
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '../../stores/auth.store'
import { showSuccessAlert } from '../../plugins/notify'

const authStore = useAuthStore()

const activeTab = ref('SCHEDULE')

const studentName = computed(() => {
  if (authStore.user?.email === 'youssef@student.lu') return 'Youssef Benali'
  if (authStore.user?.email === 'aya@student.lu') return 'Aya Benali'
  return authStore.user?.email?.split('@')[0] || 'Élève'
})

const className = ref('Classe Débutant 2A (6-8 ans)')
const teacherName = ref('Cheikh Mahmoud')

const scheduleSessions = ref([
  {
    time: '09:00 - 10:00',
    subject: 'Langue Arabe & Écriture',
    description: 'Alphabet arabe, calligraphie, vocabulaire et lecture de mots simples.',
    icon: '✏️',
    isBreak: false
  },
  {
    time: '10:00 - 10:30',
    subject: 'Pause Récréative & Collation',
    description: 'Détente dans la cour de l\'école sous la surveillance des éducateurs.',
    icon: '🍎',
    isBreak: true
  },
  {
    time: '10:30 - 11:30',
    subject: 'Noble Coran & Règles de Tajwid',
    description: 'Répétition collective, correction de prononciation et mémorisation.',
    icon: '📖',
    isBreak: false
  },
  {
    time: '11:30 - 12:00',
    subject: 'Éducation Islamique & Morale',
    description: 'Histoires des Prophètes, bonnes manières (Adab) et invocations du quotidien.',
    icon: '🕌',
    isBreak: false
  }
])

const surahList = ref([
  { name: '1. Al-Fatiha (L\'Ouverture)', verses: 7, status: 'DONE' },
  { name: '114. An-Nas (Les Hommes)', verses: 6, status: 'DONE' },
  { name: '113. Al-Falaq (L\'Aube Naissante)', verses: 5, status: 'DONE' },
  { name: '112. Al-Ikhlas (Le Monothéisme Pur)', verses: 4, status: 'DONE' },
  { name: '111. Al-Masad (Les Fibres)', verses: 5, status: 'DONE' },
  { name: '110. An-Nasr (Le Secours)', verses: 3, status: 'DONE' },
  { name: '109. Al-Kafirun (Les Infidèles)', verses: 6, status: 'DONE' },
  { name: '108. Al-Kawthar (L\'Abondance)', verses: 3, status: 'DONE' },
  { name: '107. Al-Ma\'un (L\'Entraide)', verses: 7, status: 'DONE' },
  { name: '106. Quraysh (Les Quraych)', verses: 4, status: 'DONE' },
  { name: '105. Al-Fil (L\'Éléphant)', verses: 5, status: 'DONE' },
  { name: '104. Al-Humazah (Les Calomniateurs)', verses: 9, status: 'DONE' },
  { name: '103. Al-Asr (Le Temps)', verses: 3, status: 'IN_PROGRESS' },
  { name: '102. At-Takathur (La Course aux Richesses)', verses: 8, status: 'IN_PROGRESS' }
])

function downloadDocument(docTitle) {
  showSuccessAlert(
    'Téléchargement lancé ! 📄',
    `Votre <strong>${docTitle}</strong> pour <strong>${studentName.value}</strong> a été généré et est en cours de téléchargement.`
  )
}
</script>
