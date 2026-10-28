<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion Pédagogique & Suivi Académique</h1>
        <p class="text-xs text-gray-500">Suivi complet des présences & absences, examens, notes et bulletins scolaires</p>
      </div>
      <div class="flex gap-3">
        <button
          v-if="activeSubTab === 'ATTENDANCE'"
          @click="openAddAttendanceModal"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>+</span> Saisir / Modifier Émargement
        </button>
        <button
          v-if="activeSubTab === 'GRADES'"
          @click="showGradeModal = true"
          class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>+</span> Nouvelle Évaluation / Note
        </button>
        <button
          @click="generateAllBulletins"
          class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>📄</span> Générer Bulletins PDF
        </button>
      </div>
    </div>

    <!-- Tabs Filter -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeSubTab = 'ATTENDANCE'" :class="activeSubTab === 'ATTENDANCE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        📋 Suivi des Présences & Absences ({{ attendanceList.length }})
      </button>
      <button @click="activeSubTab = 'GRADES'" :class="activeSubTab === 'GRADES' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        📝 Examens & Notes ({{ gradesList.length }})
      </button>
      <button @click="activeSubTab = 'BULLETINS'" :class="activeSubTab === 'BULLETINS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        📄 Bulletins Scolaires PDF ({{ bulletinsList.length }})
      </button>
      <button @click="activeSubTab = 'HISTORY'" :class="activeSubTab === 'HISTORY' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        ⏳ Historique Scolaire
      </button>
    </div>

    <div v-if="loading" class="p-8 text-center text-xs font-bold text-gray-500">
      Chargement des données pédagogiques...
    </div>

    <template v-else>
      <!-- 1. Suivi des Présences & Absences Section (Admin Full Access) -->
      <div v-if="activeSubTab === 'ATTENDANCE'" class="space-y-6">
        <!-- KPI Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Émargements</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ attendanceStats.total }}</p>
            <span class="text-[10px] text-gray-400">Séances saisies en BBD</span>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Taux d'Assiduité</span>
            <p class="text-2xl font-black text-emerald-600">{{ attendanceStats.rate }}%</p>
            <span class="text-[10px] text-emerald-600 font-semibold">{{ attendanceStats.presents }} Présence(s) validée(s)</span>
          </div>

          <div
            @click="attendanceStatusFilter = 'ABSENT'"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-red-200 dark:border-red-900/50 cursor-pointer hover:bg-red-50/30 transition-all"
            title="Cliquer pour filtrer les absents"
          >
            <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider block mb-1">Total Absences</span>
            <p class="text-2xl font-black text-red-600">{{ attendanceStats.absents }}</p>
            <span class="text-[10px] text-red-500 font-semibold">Comptabilisées en base</span>
          </div>

          <div
            @click="attendanceStatusFilter = 'LATE'"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-amber-200 dark:border-amber-900/50 cursor-pointer hover:bg-amber-50/30 transition-all"
            title="Cliquer pour filtrer les retards"
          >
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Total Retards</span>
            <p class="text-2xl font-black text-amber-600">{{ attendanceStats.lates }}</p>
            <span class="text-[10px] text-amber-500 font-semibold">Ponctualité à surveiller</span>
          </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
            <div>
              <h3 class="font-bold text-base text-gray-900 dark:text-white">Registre Général des Émargements</h3>
              <p class="text-xs text-gray-500">Supervision en direct de l'appel saisi par les enseignants ou régularisé par l'administration</p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">
              <input
                v-model="attendanceSearchQuery"
                type="text"
                placeholder="Rechercher élève..."
                class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs text-gray-800 dark:text-gray-200"
              />

              <select v-model="attendanceClassFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Toutes les classes</option>
                <option v-for="c in classFilterOptions" :key="c" :value="c">{{ c }}</option>
              </select>

              <select v-model="attendanceStatusFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Tous les statuts</option>
                <option value="PRESENT">🟢 Présents uniquement</option>
                <option value="ABSENT">🔴 Absents uniquement</option>
                <option value="LATE">🟡 Retards uniquement</option>
              </select>

              <button
                @click="openAddAttendanceModal"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1"
              >
                <span>+</span> Ajouter / Modifier
              </button>
            </div>
          </div>

          <!-- Attendance Table -->
          <div v-if="filteredAttendances.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
            Aucun enregistrement d'émargement ne correspond aux filtres sélectionnés.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th class="py-3 px-4">Élève</th>
                  <th class="py-3 px-4">Classe</th>
                  <th class="py-3 px-4">Date Séance</th>
                  <th class="py-3 px-4">Statut</th>
                  <th class="py-3 px-4">Motif / Justification</th>
                  <th class="py-3 px-4 text-right">Actions Admin</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="att in filteredAttendances" :key="att.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                    {{ att.student }}
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-gray-600 dark:text-gray-300">
                    {{ att.classroom }}
                  </td>
                  <td class="py-3.5 px-4 text-gray-500 font-medium">
                    {{ att.date }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                      :class="{
                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300': att.status === 'PRÉSENT',
                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': att.status === 'ABSENT',
                        'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300': att.status === 'RETARD'
                      }"
                    >
                      {{ att.status === 'PRÉSENT' ? '🟢 Présent' : (att.status === 'ABSENT' ? '🔴 Absent' : '🟡 Retard') }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-500">
                    {{ att.justification || '—' }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="editAttendance(att)"
                        class="p-1.5 rounded-lg border hover:bg-gray-100 text-gray-600 font-bold text-[11px]"
                        title="Modifier le statut ou la justification"
                      >
                        ✏️
                      </button>
                      <button
                        @click="deleteAttendanceRecord(att.id)"
                        class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-bold text-[11px]"
                        title="Supprimer cet enregistrement"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 2. Examens & Notes Section -->
      <div v-if="activeSubTab === 'GRADES'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-4 dark:border-gray-700">
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Liste des Évaluations & Relevé de Notes</h3>
          <select v-model="selectedClassFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
            <option value="ALL">Toutes les classes</option>
            <option v-for="c in classFilterOptions" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>

        <div v-if="filteredGrades.length === 0" class="py-8 text-center text-xs font-bold text-gray-400">
          Aucune évaluation enregistrée pour ce filtre.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
              <tr>
                <th class="py-3 px-4">Élève</th>
                <th class="py-3 px-4">Examen / Évaluation</th>
                <th class="py-3 px-4">Matière</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Note / 20</th>
                <th class="py-3 px-4">Trimestre</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="grade in filteredGrades" :key="grade.id">
                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ grade.student }}</td>
                <td class="py-3.5 px-4 font-medium text-gray-800 dark:text-gray-200">{{ grade.title }}</td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-brand-50 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 font-bold text-[10px]">{{ grade.subject }}</span></td>
                <td class="py-3.5 px-4 text-gray-500">{{ grade.date }}</td>
                <td class="py-3.5 px-4 font-extrabold text-brand-600 dark:text-gold-400 text-sm">{{ grade.score }} / 20</td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-[10px]">{{ grade.term }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3. Bulletins PDF Section -->
      <div v-if="activeSubTab === 'BULLETINS'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">Bulletins Scolaires Trimestriels PDF</h3>
        <div v-if="bulletinsList.length === 0" class="py-8 text-center text-xs font-bold text-gray-400">
          Aucun bulletin généré pour le moment.
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="b in bulletinsList" :key="b.id" class="p-4 rounded-xl border dark:border-gray-700 flex justify-between items-center">
            <div>
              <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ b.student }}</h4>
              <p class="text-xs text-gray-500">{{ b.term }} • Moyenne: <strong class="text-brand-600 dark:text-gold-400">{{ b.average }}/20</strong></p>
              <span class="text-[10px] text-gray-400 block mt-1">{{ b.appreciation }}</span>
            </div>
            <button @click="downloadPdf(b)" class="px-3 py-1.5 bg-brand-50 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 font-bold text-xs rounded-xl hover:bg-brand-100">
              📥 Télécharger PDF
            </button>
          </div>
        </div>
      </div>

      <!-- 4. Historique Scolaire -->
      <div v-if="activeSubTab === 'HISTORY'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">Historique des Évaluations & Années Antérieures</h3>
        <p class="text-xs text-gray-500">Archives des trimestres et progressions pédagogiques enregistrées</p>
        <div class="space-y-3 text-xs">
          <div v-for="grade in gradesList" :key="grade.id" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border flex justify-between items-center">
            <div>
              <span class="font-bold text-gray-900 dark:text-white">{{ grade.student }}</span> — <span class="font-semibold text-brand-600">{{ grade.title }}</span>
              <span class="block text-[10px] text-gray-400">{{ grade.date }} • {{ grade.term }}</span>
            </div>
            <strong class="text-sm text-emerald-600 font-extrabold">{{ grade.score }} / 20</strong>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal Admin Saisie / Modification Émargement -->
    <div v-if="showAttendanceModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-5 text-xs border dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ attendanceModalForm.id ? 'Modifier un Émargement' : 'Enregistrer une Présence / Absence' }}
        </h3>
        <p class="text-xs text-gray-500">
          Ce changement mettra directement à jour la base de données et l'espace de l'élève.
        </p>

        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Sélectionner l'Élève *</label>
          <select
            v-model="attendanceModalForm.studentId"
            required
            :disabled="!!attendanceModalForm.id"
            class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold"
          >
            <option value="">Choisir un élève...</option>
            <option v-for="st in studentsOptions" :key="st.id" :value="st.dbId || st.id">
              {{ st.name }} ({{ st.assignedGroup }})
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Date de la séance *</label>
            <input
              v-model="attendanceModalForm.date"
              type="date"
              required
              class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold"
            />
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Statut *</label>
            <select
              v-model="attendanceModalForm.status"
              class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold"
              :class="{
                'text-emerald-600': attendanceModalForm.status === 'PRESENT',
                'text-red-600': attendanceModalForm.status === 'ABSENT',
                'text-amber-600': attendanceModalForm.status === 'LATE'
              }"
            >
              <option value="PRESENT">🟢 Présent</option>
              <option value="ABSENT">🔴 Absent</option>
              <option value="LATE">🟡 Retard</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Motif ou Justification (optionnel)</label>
          <input
            v-model="attendanceModalForm.justification"
            type="text"
            placeholder="ex: Certificat médical, motif familial, etc."
            class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-medium"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            @click="saveAdminAttendance"
            :disabled="savingAttendance"
            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition-all disabled:opacity-50"
          >
            {{ savingAttendance ? 'Enregistrement...' : 'Enregistrer en BBD' }}
          </button>
          <button
            type="button"
            @click="showAttendanceModal = false"
            class="py-3 px-4 border rounded-xl font-semibold text-gray-600 dark:text-gray-300"
          >
            Annuler
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Examen / Note -->
    <div v-if="showGradeModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6 text-xs border dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ajouter une Note d'Examen</h3>
        
        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Sélectionner l'Élève *</label>
          <select v-model="newGrade.studentId" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
            <option value="">Sélectionner un élève...</option>
            <option v-for="st in studentsOptions" :key="st.id" :value="st.id">
              {{ st.name }} ({{ st.assignedGroup }})
            </option>
          </select>
        </div>

        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Intitulé de l'Examen / Évaluation *</label>
          <input v-model="newGrade.title" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-medium" placeholder="ex: Examen Récitation Sourate Al-Mulk" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Matière *</label>
            <select v-model="newGrade.subject" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
              <option value="Coran & Tajwid">Coran & Tajwid</option>
              <option value="Langue Arabe">Langue Arabe</option>
              <option value="Éducation Éthique">Éducation Éthique</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Note sur 20 *</label>
            <input v-model.number="newGrade.score" type="number" step="0.25" min="0" max="20" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-extrabold text-brand-600" placeholder="18.5" />
          </div>
        </div>

        <div class="flex gap-3 pt-2">
          <button @click="saveGrade" :disabled="savingGrade" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow transition-all disabled:opacity-50">
            {{ savingGrade ? 'Enregistrement...' : 'Enregistrer Note' }}
          </button>
          <button type="button" @click="showGradeModal = false" class="py-3 px-4 border rounded-xl font-semibold text-gray-600 dark:text-gray-300">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { generateBulletinPdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const activeSubTab = ref('ATTENDANCE')
const selectedClassFilter = ref('ALL')
const showGradeModal = ref(false)
const showAttendanceModal = ref(false)
const loading = ref(false)
const savingGrade = ref(false)
const savingAttendance = ref(false)

const studentsOptions = ref([])
const gradesList = ref([])
const attendanceList = ref([])
const bulletinsList = ref([])

const attendanceStats = ref({
  total: 0,
  presents: 0,
  absents: 0,
  lates: 0,
  rate: 100.0
})

const attendanceSearchQuery = ref('')
const attendanceClassFilter = ref('ALL')
const attendanceStatusFilter = ref('ALL')

const attendanceModalForm = ref({
  id: null,
  studentId: '',
  date: new Date().toISOString().split('T')[0],
  status: 'PRESENT',
  justification: ''
})

const newGrade = ref({
  studentId: '',
  title: '',
  subject: 'Coran & Tajwid',
  score: 18.0,
  term: 'Trimestre 3'
})

const classFilterOptions = computed(() => {
  const set = new Set()
  studentsOptions.value.forEach(s => {
    if (s.assignedGroup) set.add(s.assignedGroup)
  })
  if (set.size === 0) {
    set.add('Classe Débutant 2A (6-8 ans)')
    set.add('Classe Éveil 1 (4-5 ans)')
  }
  return Array.from(set)
})

const filteredGrades = computed(() => {
  if (selectedClassFilter.value === 'ALL') {
    return gradesList.value
  }
  return gradesList.value.filter(g => g.classroom === selectedClassFilter.value || g.subject.includes(selectedClassFilter.value))
})

const filteredAttendances = computed(() => {
  return attendanceList.value.filter(att => {
    if (attendanceSearchQuery.value) {
      const q = attendanceSearchQuery.value.toLowerCase()
      if (!att.student.toLowerCase().includes(q)) return false
    }

    if (attendanceClassFilter.value !== 'ALL') {
      if (!att.classroom.toLowerCase().includes(attendanceClassFilter.value.toLowerCase())) {
        return false
      }
    }

    if (attendanceStatusFilter.value !== 'ALL') {
      if (attendanceStatusFilter.value === 'PRESENT' && att.status !== 'PRÉSENT') return false
      if (attendanceStatusFilter.value === 'ABSENT' && att.status !== 'ABSENT') return false
      if (attendanceStatusFilter.value === 'LATE' && att.status !== 'RETARD') return false
    }

    return true
  })
})

async function fetchPedagogyData() {
  loading.value = true
  try {
    const [studentsRes, gradesRes, bulletinsRes, attRes] = await Promise.all([
      apiClient.get('/admin/students').catch(() => ({ data: [] })),
      apiClient.get('/admin/pedagogy/grades').catch(() => ({ data: [] })),
      apiClient.get('/admin/pedagogy/bulletins').catch(() => ({ data: [] })),
      apiClient.get('/admin/pedagogy/attendance').catch(() => ({ data: {} }))
    ])

    if (Array.isArray(studentsRes.data)) {
      studentsOptions.value = studentsRes.data.filter(u => u.role === 'ROLE_STUDENT' || !u.role)
    }

    if (Array.isArray(gradesRes.data)) {
      gradesList.value = gradesRes.data
    }

    if (Array.isArray(bulletinsRes.data)) {
      bulletinsList.value = bulletinsRes.data
    }

    if (attRes.data) {
      if (Array.isArray(attRes.data.items)) {
        attendanceList.value = attRes.data.items
      } else if (Array.isArray(attRes.data)) {
        attendanceList.value = attRes.data
      }

      if (attRes.data.stats) {
        attendanceStats.value = attRes.data.stats
      } else {
        const total = attendanceList.value.length
        const absents = attendanceList.value.filter(a => a.status === 'ABSENT').length
        const lates = attendanceList.value.filter(a => a.status === 'RETARD').length
        const presents = total - absents - lates
        attendanceStats.value = {
          total,
          presents,
          absents,
          lates,
          rate: total > 0 ? Math.round((presents / total) * 100) : 100.0
        }
      }
    }
  } catch (err) {
    console.error('Erreur chargement données pédagogiques:', err)
  } finally {
    loading.value = false
  }
}

function openAddAttendanceModal() {
  attendanceModalForm.value = {
    id: null,
    studentId: studentsOptions.value[0]?.dbId || studentsOptions.value[0]?.id || '',
    date: new Date().toISOString().split('T')[0],
    status: 'PRESENT',
    justification: ''
  }
  showAttendanceModal.value = true
}

function editAttendance(att) {
  attendanceModalForm.value = {
    id: att.id,
    studentId: att.studentId,
    date: att.dateRaw || new Date().toISOString().split('T')[0],
    status: att.statusCode || (att.status === 'PRÉSENT' ? 'PRESENT' : (att.status === 'ABSENT' ? 'ABSENT' : 'LATE')),
    justification: att.justification || ''
  }
  showAttendanceModal.value = true
}

async function saveAdminAttendance() {
  if (!attendanceModalForm.value.studentId) return
  savingAttendance.value = true

  try {
    const payload = {
      studentId: attendanceModalForm.value.studentId,
      date: attendanceModalForm.value.date,
      status: attendanceModalForm.value.status,
      justification: attendanceModalForm.value.justification
    }

    const res = await apiClient.post('/admin/pedagogy/attendance', payload)
    showAttendanceModal.value = false
    showSuccessAlert('Émargement Enregistré ! 🎉', res.data.message || 'L\'émargement a été mis à jour en base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement émargement:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement de l\'émargement en base de données.')
  } finally {
    savingAttendance.value = false
  }
}

async function deleteAttendanceRecord(id) {
  if (!confirm('Voulez-vous vraiment supprimer cet émargement de la base de données ?')) {
    return
  }

  try {
    await apiClient.delete(`/admin/pedagogy/attendance/${id}`)
    showSuccessAlert('Supprimé', 'L\'émargement a été supprimé de la base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur suppression émargement:', err)
    showErrorAlert('Erreur', 'Impossible de supprimer cet enregistrement.')
  }
}

async function saveGrade() {
  if (!newGrade.value.title) return
  savingGrade.value = true

  try {
    const selectedStudent = studentsOptions.value.find(s => s.id === newGrade.value.studentId)
    const payload = {
      studentId: newGrade.value.studentId ? (selectedStudent?.dbId || selectedStudent?.id) : null,
      student: selectedStudent ? selectedStudent.name : '',
      title: newGrade.value.title,
      subject: newGrade.value.subject,
      score: newGrade.value.score,
      term: newGrade.value.term
    }

    const res = await apiClient.post('/admin/pedagogy/grades', payload)

    showGradeModal.value = false
    showSuccessAlert('Note Enregistrée ! 🎉', res.data.message || 'La note d\'évaluation a été enregistrée avec succès.')
    
    newGrade.value.title = ''
    newGrade.value.score = 18.0
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement note:', err)
    showErrorAlert('Erreur', err.response?.data?.error || 'Erreur lors de l\'enregistrement de la note.')
  } finally {
    savingGrade.value = false
  }
}

function generateAllBulletins() {
  if (studentsOptions.value.length === 0) {
    generateBulletinPdf('Youssef Benali', 'Classe Débutant 2A')
  } else {
    studentsOptions.value.forEach(s => {
      generateBulletinPdf(s.name, s.assignedGroup || 'Classe Débutant 2A')
    })
  }
  showSuccessAlert('Bulletins Générés ! 📄', `Les bulletins trimestriels PDF ont été générés pour ${studentsOptions.value.length || 1} élève(s).`)
}

function downloadPdf(bulletin) {
  generateBulletinPdf(bulletin.student, 'Classe Débutant 2A')
  showSuccessAlert('Téléchargement PDF', `Le bulletin scolaire de ${bulletin.student} a été téléchargé.`)
}

onMounted(() => {
  fetchPedagogyData()
})
</script>
