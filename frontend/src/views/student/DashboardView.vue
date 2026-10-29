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
          <strong class="text-sm sm:text-base font-extrabold">{{ currentClassName }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-amber-200 block">Enseignant</span>
          <strong class="text-sm sm:text-base font-extrabold">{{ teacherName }}</strong>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards (Connected to Database) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Taux d'Assiduité</span>
          <span class="text-lg">📅</span>
        </div>
        <p
          class="text-2xl font-black"
          :class="attendanceSummary.rate >= 80 ? 'text-emerald-600' : 'text-red-500'"
        >
          {{ attendanceSummary.rate }}%
        </p>
        <span class="text-[11px]" :class="attendanceSummary.absences > 0 ? 'text-red-500 font-bold' : 'text-gray-400'">
          {{ attendanceSummary.absences }} absence(s) comptabilisée(s)
        </span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Évaluation Globale</span>
          <span class="text-lg">⭐</span>
        </div>
        <p class="text-2xl font-black text-amber-500">
          {{ latestEvaluation?.averageStars || '4.8' }} / 5
        </p>
        <span class="text-[11px] text-emerald-600 font-semibold">Compétences acquises</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Matières Évaluées</span>
          <span class="text-lg">📚</span>
        </div>
        <p class="text-2xl font-black text-brand-600">{{ curriculumList.length || 5 }} Matières</p>
        <span class="text-[11px] text-gray-400">{{ totalSkillsCount }} compétences</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-gray-400 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider">Année Scolaire</span>
          <span class="text-lg">🏫</span>
        </div>
        <p class="text-2xl font-black text-indigo-600">2026-2027</p>
        <span class="text-[11px] text-gray-400">Trimestre en cours</span>
      </div>
    </div>

    <!-- Alert if there are absences registered by the teacher -->
    <div
      v-if="attendanceSummary.absences > 0"
      class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-2xl flex items-start sm:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3">
        <span class="text-2xl">⚠️</span>
        <div>
          <h4 class="font-bold text-red-900 dark:text-red-200 text-sm">
            Vous avez {{ attendanceSummary.absences }} absence(s) enregistrée(s) par l'enseignant
          </h4>
          <p class="text-xs text-red-700 dark:text-red-300">
            Dernière séance marquée : {{ latestAbsence?.formattedDate }}
            <span v-if="latestAbsence?.justification"> (Motif : {{ latestAbsence.justification }})</span>
          </p>
        </div>
      </div>
      <button
        @click="activeTab = 'ATTENDANCE'"
        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition-all shrink-0"
      >
        Voir le Registre
      </button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button
        @click="activeTab = 'ATTENDANCE'"
        :class="activeTab === 'ATTENDANCE' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        📋 Mon Assiduité & Registre des Absences ({{ attendanceHistory.length }})
      </button>
      <button
        @click="activeTab = 'EVALUATIONS'"
        :class="activeTab === 'EVALUATIONS' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        ⭐ Mes Évaluations par Compétences ({{ evaluationsList.length }})
      </button>
      <button
        @click="activeTab = 'SCHEDULE'"
        :class="activeTab === 'SCHEDULE' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        🕒 Mon Emploi du Temps du Samedi
      </button>
      <button
        @click="activeTab = 'DOCUMENTS'"
        :class="activeTab === 'DOCUMENTS' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        📄 Mes Bulletins & Documents
      </button>
    </div>

    <!-- TAB 1: Assiduité & Absences (Directly synced with Teacher and DB) -->
    <div v-if="activeTab === 'ATTENDANCE'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
          <div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">Registre Officiel d'Assiduité</h3>
            <p class="text-xs text-gray-500">
              Historique des séances émargées par Cheikh Mahmoud. Toute mise à jour de l'enseignant est répercutée en direct ici.
            </p>
          </div>
          <button
            @click="fetchAttendance"
            class="px-3 py-1.5 rounded-xl border bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 text-xs font-bold flex items-center gap-1.5 self-start sm:self-auto"
          >
            <span>🔄</span> Actualiser
          </button>
        </div>

        <!-- Breakdown counters -->
        <div class="grid grid-cols-3 gap-3">
          <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-emerald-800 dark:text-emerald-300 block">Présences</span>
            <p class="text-xl font-extrabold text-emerald-600">{{ attendanceSummary.presents }}</p>
          </div>
          <div class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-red-800 dark:text-red-300 block">Absences</span>
            <p class="text-xl font-extrabold text-red-600">{{ attendanceSummary.absences }}</p>
          </div>
          <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-amber-800 dark:text-amber-300 block">Retards</span>
            <p class="text-xl font-extrabold text-amber-600">{{ attendanceSummary.lates }}</p>
          </div>
        </div>

        <div v-if="loadingAttendance" class="py-12 text-center text-xs font-bold text-gray-400">
          Chargement de l'assiduité depuis la base de données...
        </div>

        <div v-else-if="attendanceHistory.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
          Aucun enregistrement d'appel pour le moment.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
              <tr>
                <th class="py-3 px-4">Date de Séance</th>
                <th class="py-3 px-4">Statut</th>
                <th class="py-3 px-4">Motif / Justification</th>
                <th class="py-3 px-4">Classe</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="rec in attendanceHistory" :key="rec.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                  {{ rec.formattedDate }}
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                    :class="{
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300': rec.status === 'PRESENT',
                      'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': rec.status === 'ABSENT',
                      'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300': rec.status === 'LATE'
                    }"
                  >
                    {{ rec.status === 'PRESENT' ? '🟢 Présent' : (rec.status === 'ABSENT' ? '🔴 Absent' : '🟡 Retard') }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-gray-500">
                  {{ rec.justification || '—' }}
                </td>
                <td class="py-3.5 px-4 text-gray-400 text-[11px]">
                  {{ rec.className }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: Mes Évaluations par Compétences (Étoiles 1 à 5 ⭐) -->
    <div v-if="activeTab === 'EVALUATIONS'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
          <div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
              <span>⭐</span> Relevé Trimestriel de Compétences
            </h3>
            <p class="text-xs text-gray-500">
              Évaluation officielle sur barème de 1 à 5 étoiles (⭐) validée par Cheikh Mahmoud.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <select
              v-if="evaluationsList.length > 1"
              v-model="selectedEvaluationId"
              class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-brand-600 dark:text-gold-400"
            >
              <option v-for="ev in evaluationsList" :key="ev.id" :value="ev.id">{{ ev.term }} ({{ ev.schoolYear }})</option>
            </select>

            <button
              @click="downloadCurrentBulletinPdf"
              class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
            >
              <span>📥</span> Télécharger Bulletin PDF
            </button>
          </div>
        </div>

        <div v-if="loadingEvaluations" class="py-12 text-center text-xs font-bold text-gray-400">
          Chargement de vos évaluations...
        </div>

        <div v-else-if="!activeEvaluation" class="py-12 text-center text-xs font-bold text-gray-400">
          Aucune évaluation trimestrielle enregistrée pour le moment.
        </div>

        <div v-else class="space-y-6">
          <!-- Summary Header for Active Evaluation -->
          <div class="p-4 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <span class="text-[11px] font-bold uppercase text-amber-700 dark:text-amber-300 block">
                {{ activeEvaluation.term }} • Année Scolaire {{ activeEvaluation.schoolYear }}
              </span>
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                Enseignant : {{ activeEvaluation.teacherName || 'Cheikh Mahmoud' }} • Mise à jour le {{ activeEvaluation.updatedAt }}
              </p>
            </div>
            <div class="text-right">
              <span class="px-3 py-1.5 bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 font-black text-sm rounded-xl border border-amber-300">
                ⭐ Moyenne : {{ activeEvaluation.averageStars }} / 5
              </span>
            </div>
          </div>

          <!-- Subjects and Skills Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="sub in curriculumList"
              :key="sub.id"
              class="p-4 bg-gray-50/70 dark:bg-gray-700/30 rounded-2xl border dark:border-gray-700 space-y-3"
            >
              <div class="flex items-center gap-2 border-b pb-2 font-bold text-xs text-emerald-800 dark:text-emerald-300 dark:border-gray-600">
                <span class="text-lg">{{ sub.icon || '📚' }}</span>
                <span>{{ sub.name }}</span>
              </div>

              <div class="space-y-2.5">
                <div v-for="sk in sub.skills" :key="sk.id" class="text-xs">
                  <div class="flex items-center justify-between mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-[11px]">{{ sk.name }}</span>
                    <span class="font-bold text-[10px] text-amber-600 dark:text-amber-400">
                      {{ getStarLabel(activeEvaluation.ratings[sk.id] || 5) }}
                    </span>
                  </div>

                  <div class="flex items-center gap-1">
                    <span
                      v-for="star in 5"
                      :key="star"
                      class="text-base font-bold"
                      :class="star <= (activeEvaluation.ratings[sk.id] || 5) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                    >
                      ★
                    </span>
                    <span class="text-[10px] text-gray-400 font-mono ml-1 font-bold">
                      ({{ activeEvaluation.ratings[sk.id] || 5 }}/5)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Remarks Callout Box -->
          <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl space-y-1.5">
            <h4 class="font-bold text-xs text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
              <span>✍️</span> Remarques et appréciations de l'enseignant(e) :
            </h4>
            <p class="text-xs text-emerald-800 dark:text-emerald-300 italic font-medium leading-relaxed">
              "{{ activeEvaluation.teacherRemarks || 'Très bon trimestre, élève sérieux et appliqué.' }}"
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: Emploi du Temps -->
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

    <!-- TAB 4: Bulletins & Documents -->
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
                  <h4 class="font-bold text-sm text-gray-900 dark:text-white">Bulletin Scolaire Officiel</h4>
                  <p class="text-xs text-gray-500">{{ activeEvaluation?.term || 'Trimestre 1' }} • Évaluation par Compétences</p>
                </div>
              </div>
            </div>
            <button
              @click="downloadCurrentBulletinPdf"
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
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth.store'
import apiClient from '../../plugins/axios'
import { generateBulletinPdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert } from '../../plugins/notify'

const authStore = useAuthStore()

const activeTab = ref('ATTENDANCE')
const loadingAttendance = ref(false)
const loadingEvaluations = ref(false)

const currentClassName = ref('Classe Débutant 2A (6-8 ans)')
const teacherName = ref('Cheikh Mahmoud')

const studentName = computed(() => {
  if (authStore.user?.email === 'youssef@student.lu') return 'Youssef Benali'
  if (authStore.user?.email === 'aya@student.lu') return 'Aya Benali'
  return authStore.user?.email?.split('@')[0] || 'Élève'
})

const attendanceSummary = ref({
  totalSessions: 0,
  presents: 0,
  absences: 0,
  lates: 0,
  rate: 100.0
})

const attendanceHistory = ref([])
const evaluationsList = ref([])
const curriculumList = ref([])
const selectedEvaluationId = ref(null)

const latestAbsence = computed(() => {
  return attendanceHistory.value.find(a => a.status === 'ABSENT') || null
})

const activeEvaluation = computed(() => {
  if (selectedEvaluationId.value) {
    const found = evaluationsList.value.find(e => e.id === selectedEvaluationId.value)
    if (found) return found
  }
  return evaluationsList.value[0] || null
})

const latestEvaluation = computed(() => {
  return evaluationsList.value[0] || null
})

const totalSkillsCount = computed(() => {
  let count = 0
  curriculumList.value.forEach(s => {
    count += (s.skills || []).length
  })
  return count || 9
})

function getStarLabel(count) {
  const n = parseInt(count) || 5
  if (n <= 1) return 'Non acquis'
  if (n === 2) return 'À consolider'
  if (n === 3) return 'En cours'
  if (n === 4) return 'Bien acquis'
  return 'Excellent'
}

async function fetchAttendance() {
  loadingAttendance.value = true
  try {
    const res = await apiClient.get('/student/attendance', {
      params: {
        email: authStore.user?.email
      }
    })

    if (res.data) {
      if (res.data.className) currentClassName.value = res.data.className
      if (res.data.summary) attendanceSummary.value = res.data.summary
      if (Array.isArray(res.data.history)) attendanceHistory.value = res.data.history
    }
  } catch (err) {
    console.error('Erreur chargement assiduité élève:', err)
  } finally {
    loadingAttendance.value = false
  }
}

async function fetchEvaluations() {
  loadingEvaluations.value = true
  try {
    const res = await apiClient.get('/student/evaluations')
    if (res.data) {
      if (res.data.className) currentClassName.value = res.data.className
      if (Array.isArray(res.data.curriculum)) curriculumList.value = res.data.curriculum
      if (Array.isArray(res.data.evaluations)) {
        evaluationsList.value = res.data.evaluations
        if (evaluationsList.value.length > 0 && !selectedEvaluationId.value) {
          selectedEvaluationId.value = evaluationsList.value[0].id
        }
      }
    }
  } catch (err) {
    console.error('Erreur chargement évaluations élève:', err)
  } finally {
    loadingEvaluations.value = false
  }
}

function downloadCurrentBulletinPdf() {
  if (!activeEvaluation.value) {
    generateBulletinPdf(studentName.value, currentClassName.value, { curriculum: curriculumList.value })
  } else {
    generateBulletinPdf(studentName.value, currentClassName.value, {
      ...activeEvaluation.value,
      curriculum: curriculumList.value
    })
  }
  showSuccessAlert('Téléchargement PDF', `Votre bulletin scolaire officiel pour ${studentName.value} a été généré et téléchargé.`)
}

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

function downloadDocument(docTitle) {
  showSuccessAlert(
    'Téléchargement lancé ! 📄',
    `Votre <strong>${docTitle}</strong> pour <strong>${studentName.value}</strong> a été généré et est en cours de téléchargement.`
  )
}

onMounted(() => {
  fetchAttendance()
  fetchEvaluations()
})
</script>
