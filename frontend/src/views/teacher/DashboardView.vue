<template>
  <div class="space-y-8">
    <!-- Header Banner Enseignant -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
          Portail Enseignant & Pédagogie
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold mt-2">Bienvenue, {{ teacherName }}</h1>
        <p class="text-emerald-100 text-sm mt-1">Feuille d'appel numérique, comptabilisation des absences et suivi Tajwid</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Classes Affectées</span>
          <strong class="text-xl font-extrabold">{{ assignedClasses.length }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Élèves En Classe</span>
          <strong class="text-xl font-extrabold">{{ studentsList.length }}</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Enseignant -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="switchTab('ATTENDANCE')" :class="activeTab === 'ATTENDANCE' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        📋 Feuille d'Appel Numérique & Absences
      </button>
      <button @click="switchTab('EVALUATIONS')" :class="activeTab === 'EVALUATIONS' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        ⭐ Évaluations Trimestrielles par Compétences (1 à 5)
      </button>
    </div>

    <!-- TAB 1: Feuille d'Appel Numérique -->
    <div v-if="activeTab === 'ATTENDANCE'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white">Validation de Présence par Classe</h3>
          <p class="text-xs text-gray-500">Sélectionnez la date et cochez le statut de chaque élève. Tout changement est sauvegardé en base de données et visible par l'élève et l'administration.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <select v-model="selectedClass" @change="loadAttendanceSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-emerald-700 dark:text-emerald-300">
            <option v-for="c in assignedClasses" :key="c.id" :value="c.name">{{ c.name }}</option>
          </select>

          <input type="date" v-model="attendanceDate" @change="loadAttendanceSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200" />

          <button @click="loadAttendanceSheet" class="p-2 rounded-xl border bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-bold" title="Rafraîchir">
            🔄
          </button>
        </div>
      </div>

      <!-- Quick Summary Badges for current sheet -->
      <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
          <span>🟢</span> Présents : {{ currentPresentsCount }}
        </span>
        <span class="px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 flex items-center gap-1.5">
          <span>🔴</span> Absents : {{ currentAbsentsCount }}
        </span>
        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
          <span>🟡</span> Retards : {{ currentLatesCount }}
        </span>
        <span class="text-gray-400 text-[11px] ml-auto">
          Séance du <strong>{{ formattedDate }}</strong>
        </span>
      </div>

      <div v-if="loadingAttendance" class="py-12 text-center text-xs font-bold text-gray-400">
        Chargement de la feuille d'appel depuis la base de données...
      </div>

      <!-- Liste des Élèves de la classe pour l'Appel -->
      <div v-else-if="studentsList.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
        Aucun élève trouvé pour cette classe.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="student in studentsList"
          :key="student.id"
          class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
          :class="{
            'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/40': student.attendance === 'PRESENT',
            'bg-red-50/50 border-red-200 dark:bg-red-950/20 dark:border-red-900/40': student.attendance === 'ABSENT',
            'bg-amber-50/50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/40': student.attendance === 'LATE'
          }"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-xl font-bold text-white text-xs flex items-center justify-center shadow"
              :class="{
                'bg-emerald-600': student.attendance === 'PRESENT',
                'bg-red-600': student.attendance === 'ABSENT',
                'bg-amber-500': student.attendance === 'LATE'
              }"
            >
              {{ student.name ? student.name[0] : 'É' }}
            </div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ student.name }}</h4>
              <p class="text-xs text-gray-400">Parent: {{ student.parentName }} ({{ student.phone }})</p>
            </div>
          </div>

          <!-- Boutons de Statut Présence + Justification -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="student.attendance = 'PRESENT'"
                :class="student.attendance === 'PRESENT' ? 'bg-emerald-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-emerald-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🟢</span> Présent
              </button>

              <button
                type="button"
                @click="student.attendance = 'ABSENT'"
                :class="student.attendance === 'ABSENT' ? 'bg-red-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-red-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🔴</span> Absent
              </button>

              <button
                type="button"
                @click="student.attendance = 'LATE'"
                :class="student.attendance === 'LATE' ? 'bg-amber-500 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-amber-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🟡</span> Retard
              </button>
            </div>

            <!-- Motif / Justification input for Absences or Lates -->
            <input
              v-if="student.attendance !== 'PRESENT'"
              v-model="student.justification"
              type="text"
              placeholder="Motif (ex: Maladie, Rendez-vous...)"
              class="px-3 py-1.5 rounded-xl border bg-white dark:bg-gray-800 text-xs w-full sm:w-48 text-gray-800 dark:text-gray-200"
            />
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center pt-4 border-t dark:border-gray-700">
        <p class="text-[11px] text-gray-500">
          Les absences et retards validés ici seront immédiatement comptabilisés dans le dossier élève et consultables par l'administrateur.
        </p>
        <button
          @click="submitAttendance"
          :disabled="saving || loadingAttendance"
          class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <span>💾</span> {{ saving ? 'Enregistrement en base...' : 'Valider & Enregistrer l\'Appel' }}
        </button>
      </div>
    </div>

    <!-- TAB 2: Saisie des Évaluations par Compétences (Étoiles 1 à 5 ⭐) -->
    <div v-if="activeTab === 'EVALUATIONS'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
            <span>⭐</span> Évaluations Trimestrielles par Compétences
          </h3>
          <p class="text-xs text-gray-500">
            Notez chaque compétence de 1 à 5 étoiles (⭐) et rédigez vos appréciations. Tout est persisté dans MySQL et consultable par l'élève, ses parents et l'administration.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <select v-model="selectedClass" @change="loadEvaluationSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-emerald-700 dark:text-emerald-300">
            <option v-for="c in assignedClasses" :key="c.id" :value="c.name">{{ c.name }}</option>
          </select>

          <select v-model="selectedEvaluationTerm" @change="loadEvaluationSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200">
            <option value="Trimestre 1">Trimestre 1</option>
            <option value="Trimestre 2">Trimestre 2</option>
            <option value="Trimestre 3">Trimestre 3</option>
          </select>

          <button @click="loadEvaluationSheet" class="p-2 rounded-xl border bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-bold" title="Rafraîchir les évaluations">
            🔄
          </button>
        </div>
      </div>

      <!-- Quick Info Bar -->
      <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 rounded-2xl text-xs">
        <div class="flex items-center gap-2">
          <span class="text-base">ℹ️</span>
          <span class="font-medium text-emerald-900 dark:text-emerald-200">
            Barème d'acquisition : <strong>1 ⭐ = Non acquis</strong> • <strong>2 ⭐ = À consolider</strong> • <strong>3 ⭐ = En cours</strong> • <strong>4 ⭐ = Bien acquis</strong> • <strong>5 ⭐ = Excellent</strong>
          </span>
        </div>
        <span class="font-extrabold text-emerald-700 dark:text-emerald-300">
          {{ evaluationStudentsList.length }} élève(s) dans cette classe
        </span>
      </div>

      <div v-if="loadingEvaluations" class="py-12 text-center text-xs font-bold text-gray-400">
        Chargement des évaluations et compétences depuis la base de données...
      </div>

      <div v-else-if="evaluationStudentsList.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
        Aucun élève trouvé pour cette classe.
      </div>

      <!-- Students Cards List -->
      <div v-else class="space-y-6">
        <div
          v-for="student in evaluationStudentsList"
          :key="student.id"
          class="p-6 bg-gray-50/70 dark:bg-gray-700/30 rounded-3xl border dark:border-gray-700 space-y-5 transition-all shadow-sm hover:shadow-md"
        >
          <!-- Student Card Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-3 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow">
                {{ student.name ? student.name[0] : 'É' }}
              </div>
              <div>
                <h4 class="font-extrabold text-base text-gray-900 dark:text-white">{{ student.name }}</h4>
                <p class="text-xs text-gray-400">Période : {{ selectedEvaluationTerm }} • Année 2026-2027</p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs font-black text-amber-700 dark:text-amber-300 flex items-center gap-1 shadow-sm">
                ⭐ Moyenne : {{ computeStudentAverage(student) }} / 5
              </span>
            </div>
          </div>

          <!-- Subjects and Competencies Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="subject in curriculumList"
              :key="subject.id"
              class="bg-white dark:bg-gray-800 p-4 rounded-2xl border dark:border-gray-700 shadow-sm space-y-3"
            >
              <div class="flex items-center gap-2 font-bold text-xs text-emerald-800 dark:text-emerald-300 border-b pb-2 dark:border-gray-700">
                <span class="text-base">{{ subject.icon || '📚' }}</span>
                <span>{{ subject.name }}</span>
              </div>

              <div class="space-y-3">
                <div v-for="skill in subject.skills" :key="skill.id" class="text-xs">
                  <div class="flex items-center justify-between mb-1 gap-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-[11px] leading-tight">
                      {{ skill.name }}
                    </span>
                    <span class="font-bold text-[10px] text-amber-600 dark:text-amber-400 shrink-0">
                      {{ getStarLabel(student.ratings[skill.id] || 5) }}
                    </span>
                  </div>

                  <!-- 5 Stars interactive bar -->
                  <div class="flex items-center gap-1">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      @click="setStudentRating(student, skill.id, star)"
                      class="w-7 h-7 rounded-lg text-base font-bold flex items-center justify-center transition-all cursor-pointer focus:outline-none"
                      :class="star <= (student.ratings[skill.id] || 5) ? 'text-amber-400 hover:scale-125' : 'text-gray-300 dark:text-gray-600 hover:text-amber-200'"
                      :title="`${star} étoile(s)`"
                    >
                      ★
                    </button>
                    <span class="text-[10px] text-gray-400 font-mono ml-2 font-bold">
                      {{ student.ratings[skill.id] || 5 }}/5
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Free text zone: Teacher Remarks -->
          <div class="pt-2">
            <label class="block font-bold text-xs text-gray-800 dark:text-gray-200 mb-1 flex items-center gap-1.5">
              <span>✍️</span> Remarques et appréciations de l'enseignant(e) :
            </label>
            <textarea
              v-model="student.teacherRemarks"
              rows="2"
              placeholder="Observations pédagogiques, progression, assiduité et conseils pour le trimestre..."
              class="w-full px-3.5 py-2.5 rounded-2xl border bg-white dark:bg-gray-800 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center pt-4 border-t dark:border-gray-700">
        <p class="text-[11px] text-gray-500">
          Les évaluations par compétences et remarques validées ici mettront à jour immédiatement les bulletins officiels dans la base de données MySQL.
        </p>
        <button
          @click="submitEvaluations"
          :disabled="savingEvaluations || loadingEvaluations"
          class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <span>💾</span> {{ savingEvaluations ? 'Enregistrement en base...' : 'Enregistrer le Carnet d\'Évaluations' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth.store'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const authStore = useAuthStore()
const activeTab = ref('ATTENDANCE')

const teacherName = computed(() => {
  if (authStore.user?.email === 'mahmoud@tijanesnours.lu') return 'Cheikh Mahmoud'
  return authStore.user?.email || 'Enseignant'
})

const selectedClass = ref('Classe Débutant 2A (6-8 ans)')
const attendanceDate = ref(new Date().toISOString().split('T')[0])
const saving = ref(false)
const loadingAttendance = ref(false)

const assignedClasses = ref([])
const studentsList = ref([])

// Evaluation Tab States
const selectedEvaluationTerm = ref('Trimestre 1')
const loadingEvaluations = ref(false)
const savingEvaluations = ref(false)
const curriculumList = ref([])
const evaluationStudentsList = ref([])

const formattedDate = computed(() => {
  if (!attendanceDate.value) return ''
  const parts = attendanceDate.value.split('-')
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`
  return attendanceDate.value
})

const currentPresentsCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'PRESENT').length
})

const currentAbsentsCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'ABSENT').length
})

const currentLatesCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'LATE').length
})

function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'EVALUATIONS') {
    loadEvaluationSheet()
  } else if (tab === 'ATTENDANCE') {
    loadAttendanceSheet()
  }
}

function getStarLabel(count) {
  const n = parseInt(count) || 5
  if (n <= 1) return 'Non acquis'
  if (n === 2) return 'À consolider'
  if (n === 3) return 'En cours'
  if (n === 4) return 'Bien acquis'
  return 'Excellent'
}

function setStudentRating(student, skillId, star) {
  if (!student.ratings) student.ratings = {}
  student.ratings[skillId] = star
}

function computeStudentAverage(student) {
  if (!student.ratings) return '5.0'
  const values = Object.values(student.ratings)
  if (values.length === 0) return '5.0'
  const sum = values.reduce((acc, curr) => acc + (parseInt(curr) || 5), 0)
  return (sum / values.length).toFixed(1)
}

async function fetchClasses() {
  try {
    const res = await apiClient.get('/teacher/classes')
    if (Array.isArray(res.data) && res.data.length > 0) {
      assignedClasses.value = res.data
      if (!assignedClasses.value.some(c => c.name === selectedClass.value)) {
        selectedClass.value = assignedClasses.value[0].name
      }
    }
  } catch (err) {
    console.error('Erreur chargement des classes enseignant:', err)
  }
}

async function loadAttendanceSheet() {
  loadingAttendance.value = true
  try {
    const res = await apiClient.get('/teacher/attendance', {
      params: {
        class_name: selectedClass.value,
        date: attendanceDate.value
      }
    })

    if (res.data && Array.isArray(res.data.students)) {
      studentsList.value = res.data.students
    }
  } catch (err) {
    console.error('Erreur chargement feuille appel:', err)
  } finally {
    loadingAttendance.value = false
  }
}

async function submitAttendance() {
  saving.value = true
  try {
    const payload = {
      className: selectedClass.value,
      date: attendanceDate.value,
      attendances: studentsList.value.map(st => ({
        id: st.id,
        attendance: st.attendance,
        justification: st.justification || ''
      }))
    }

    const res = await apiClient.post('/teacher/attendance', payload)
    const stats = res.data.stats || {}

    showSuccessAlert(
      'Feuille d\'Appel Enregistrée ! 🎉',
      `La feuille d'appel du <strong>${formattedDate.value}</strong> pour <strong>${selectedClass.value}</strong> a été enregistrée en base de données.<br>` +
      `<span class="badge bg-emerald-100 text-emerald-800 me-1">🟢 ${stats.presents || 0} Présent(s)</span> ` +
      `<span class="badge bg-red-100 text-red-800 me-1">🔴 ${stats.absents || 0} Absent(s)</span> ` +
      `<span class="badge bg-amber-100 text-amber-800">🟡 ${stats.lates || 0} Retard(s)</span>`
    )

    await loadAttendanceSheet()
  } catch (err) {
    console.error('Erreur enregistrement feuille appel:', err)
    showErrorAlert('Erreur', 'Erreur lors de la validation de la feuille d\'appel en base de données.')
  } finally {
    saving.value = false
  }
}

async function loadEvaluationSheet() {
  loadingEvaluations.value = true
  try {
    const res = await apiClient.get('/teacher/evaluations', {
      params: {
        class_name: selectedClass.value,
        term: selectedEvaluationTerm.value,
        school_year: '2026-2027'
      }
    })

    if (res.data) {
      curriculumList.value = res.data.curriculum || []
      evaluationStudentsList.value = (res.data.students || []).map(st => ({
        ...st,
        ratings: st.ratings || {},
        teacherRemarks: st.teacherRemarks || ''
      }))
    }
  } catch (err) {
    console.error('Erreur chargement des évaluations enseignant:', err)
  } finally {
    loadingEvaluations.value = false
  }
}

async function submitEvaluations() {
  savingEvaluations.value = true
  try {
    const payload = {
      className: selectedClass.value,
      term: selectedEvaluationTerm.value,
      schoolYear: '2026-2027',
      evaluations: evaluationStudentsList.value.map(st => ({
        id: st.id,
        studentId: st.id,
        ratings: st.ratings,
        teacherRemarks: st.teacherRemarks
      }))
    }

    const res = await apiClient.post('/teacher/evaluations', payload)

    showSuccessAlert(
      'Évaluations Trimestrielles Enregistrées ! 🌟',
      `Le carnet d'évaluations par compétences de la <strong>${selectedClass.value}</strong> (${selectedEvaluationTerm.value}) a été enregistré en base de données pour <strong>${res.data.savedCount || evaluationStudentsList.value.length}</strong> élève(s).`
    )

    await loadEvaluationSheet()
  } catch (err) {
    console.error('Erreur enregistrement évaluations:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement des évaluations en base de données.')
  } finally {
    savingEvaluations.value = false
  }
}

onMounted(async () => {
  await fetchClasses()
  await loadAttendanceSheet()
})
</script>
