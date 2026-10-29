<template>
  <div class="space-y-6">
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-emerald-900 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-gold-300">
            Pédagogie & Évaluations Officielles
          </span>
          <span class="px-2.5 py-0.5 bg-emerald-500/30 text-emerald-300 rounded-full text-[11px] font-bold">
            Modèle Bilingue FR / AR
          </span>
        </div>
        <h1 class="text-3xl font-extrabold mt-2 flex items-center gap-3">
          <span>📑</span> Bulletins Scolaires Bilingues
        </h1>
        <p class="text-brand-100 text-sm mt-1 max-w-3xl">
          Génération automatique des bulletins scolaires individuels et groupés, conformes au document officiel de référence avec tableaux bilingues, 4 niveaux d'objectifs (⭐), remarques personnalisées et signatures.
        </p>
      </div>

      <div class="flex flex-wrap gap-3">
        <button
          v-if="selectedStudentIds.length > 0"
          @click="downloadBatchZip"
          :disabled="isGeneratingZip"
          class="px-5 py-3 bg-gold-500 hover:bg-gold-400 text-gray-900 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <span v-if="isGeneratingZip" class="animate-spin">⏳</span>
          <span v-else>📦</span>
          Télécharger Sélection en ZIP ({{ selectedStudentIds.length }})
        </button>
        <button
          @click="fetchStudents"
          class="px-4 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2"
        >
          <span>🔄</span> Actualiser
        </button>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-5 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Search input -->
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Recherche Élève</label>
          <div class="relative">
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Nom, prénom (FR ou AR)..."
              class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"
            />
            <span class="absolute left-3 top-2.5 text-gray-400 text-xs">🔍</span>
          </div>
        </div>

        <!-- 2. Class Filter -->
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Classe / Groupe</label>
          <select
            v-model="filters.classId"
            @change="fetchStudents"
            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"
          >
            <option value="">Toutes les classes</option>
            <option v-for="c in classesList" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <!-- 3. School Year -->
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Année Scolaire</label>
          <select
            v-model="filters.schoolYear"
            @change="fetchStudents"
            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"
          >
            <option v-for="y in availableYears" :key="y" :value="y">
              Année {{ y }}
            </option>
          </select>
        </div>

        <!-- 4. Term / Semester -->
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Période du Bulletin</label>
          <select
            v-model="filters.term"
            @change="fetchStudents"
            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"
          >
            <option v-for="t in availableTerms" :key="t" :value="t">
              {{ t }}
            </option>
          </select>
        </div>
      </div>

      <!-- Quick Summary and Selection Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
        <div class="flex items-center gap-4">
          <label class="flex items-center gap-2 cursor-pointer font-bold select-none text-gray-800 dark:text-gray-200">
            <input
              type="checkbox"
              :checked="isAllSelected"
              @change="toggleSelectAll"
              class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300"
            />
            <span>Tout sélectionner ({{ students.length }} élèves)</span>
          </label>
          <span v-if="selectedStudentIds.length > 0" class="text-brand-600 font-bold bg-brand-50 dark:bg-brand-950/30 px-2 py-0.5 rounded-md">
            {{ selectedStudentIds.length }} élève(s) sélectionné(s)
          </span>
        </div>

        <div class="flex items-center gap-2 font-medium">
          <span>Période active :</span>
          <span class="font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-md">
            {{ filters.term }} ({{ filters.schoolYear }})
          </span>
        </div>
      </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-gray-500">
        <div class="inline-block animate-spin text-3xl mb-3">🔄</div>
        <p class="font-semibold text-sm">Chargement des données scolaires des élèves...</p>
      </div>

      <div v-else-if="students.length === 0" class="p-12 text-center text-gray-500 space-y-3">
        <div class="text-4xl">👨‍🎓</div>
        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base">Aucun élève trouvé</h3>
        <p class="text-xs text-gray-500 max-w-sm mx-auto">
          Aucun élève ne correspond aux critères de recherche pour cette classe ou période.
        </p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
          <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase text-[10px] font-bold text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
            <tr>
              <th class="p-4 w-12 text-center">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  @change="toggleSelectAll"
                  class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300"
                />
              </th>
              <th class="p-4">Élève (Français / Arabe)</th>
              <th class="p-4">Classe Inscrite</th>
              <th class="p-4">Enseignant(e)</th>
              <th class="p-4 text-center">Absences</th>
              <th class="p-4 text-center">Statut Évaluation</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
              v-for="student in students"
              :key="student.id"
              class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors"
              :class="selectedStudentIds.includes(student.id) ? 'bg-indigo-50/30 dark:bg-indigo-950/20' : ''"
            >
              <td class="p-4 text-center">
                <input
                  type="checkbox"
                  :value="student.id"
                  v-model="selectedStudentIds"
                  class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300"
                />
              </td>
              <td class="p-4 font-bold text-gray-900 dark:text-white">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300 font-extrabold flex items-center justify-center text-xs">
                    {{ student.firstName?.[0] }}{{ student.lastName?.[0] }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span>{{ student.fullName }}</span>
                      <span class="text-gray-400">|</span>
                      <span class="font-arabic font-bold text-emerald-800 dark:text-emerald-400" style="direction: rtl;">
                        {{ student.fullNameAr }}
                      </span>
                    </div>
                    <span class="text-[10px] text-gray-400 font-normal">ID: #{{ student.id }}</span>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-[11px] font-semibold">
                  {{ student.className }}
                </span>
              </td>
              <td class="p-4">
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ student.teacherName }}
                </div>
                <div class="text-[10px] font-arabic text-gray-500">
                  {{ student.teacherNameAr }}
                </div>
              </td>
              <td class="p-4 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-extrabold"
                  :class="student.absencesCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'"
                >
                  {{ student.absencesCount }}
                </span>
              </td>
              <td class="p-4 text-center">
                <div v-if="student.hasBulletin" class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-full text-amber-700 dark:text-amber-300 font-bold text-xs">
                  <span>⭐</span>
                  <span>{{ student.averageStars }} / 5</span>
                </div>
                <span v-else class="text-xs text-gray-400 italic">
                  Non évalué
                </span>
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openPreviewModal(student)"
                    class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 font-bold text-xs rounded-xl transition-all flex items-center gap-1 shadow-sm"
                    title="Aperçu & Impression du bulletin"
                  >
                    <span>👁️</span> Aperçu
                  </button>
                  <button
                    @click="downloadSinglePdf(student)"
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1 shadow-sm"
                    title="Télécharger le PDF individuel"
                  >
                    <span>📄</span> PDF
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Live Preview & Edition Modal -->
    <div
      v-if="showModal && activeStudentData"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-5xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 my-8 space-y-6">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-lg">
              📑
            </div>
            <div>
              <h3 class="font-extrabold text-lg text-gray-900 dark:text-white">
                Bulletin Scolaire Bilingue : {{ activeStudentData.student?.fullName }}
              </h3>
              <p class="text-xs text-gray-500">
                {{ activeStudentData.period?.titleFr }} • {{ activeStudentData.student?.className }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <!-- Print button -->
            <button
              @click="printCurrentBulletin"
              class="px-3.5 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all"
            >
              <span>🖨️</span> Imprimer
            </button>
            <!-- Download PDF button -->
            <button
              @click="downloadActiveBulletinPdf"
              :disabled="isGeneratingPdf"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow disabled:opacity-50"
            >
              <span v-if="isGeneratingPdf" class="animate-spin">⏳</span>
              <span v-else>📥</span>
              Télécharger PDF Officiel
            </button>
            <!-- Close Modal -->
            <button
              @click="showModal = false"
              class="text-gray-400 hover:text-gray-600 text-2xl font-bold ml-2 p-1"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Mode switcher: Preview vs Edit Remarks -->
        <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-4">
          <button
            @click="modalTab = 'PREVIEW'"
            :class="modalTab === 'PREVIEW' ? 'border-b-2 border-brand-600 text-brand-600 pb-2 font-extrabold' : 'text-gray-500 pb-2'"
          >
            👁️ Aperçu Document (Format A4 Officiel)
          </button>
          <button
            @click="modalTab = 'EDIT'"
            :class="modalTab === 'EDIT' ? 'border-b-2 border-brand-600 text-brand-600 pb-2 font-extrabold' : 'text-gray-500 pb-2'"
          >
            ✏️ Éditer Appréciations & Étoiles
          </button>
        </div>

        <!-- TAB 1: Live A4 Preview -->
        <div v-show="modalTab === 'PREVIEW'" class="overflow-y-auto max-h-[72vh] p-4 bg-gray-100 dark:bg-gray-900 rounded-2xl flex justify-center">
          <OfficialBilingualBulletin
            elementId="modal-official-bulletin"
            :data="activeStudentData"
          />
        </div>

        <!-- TAB 2: Edit Remarks & Ratings Form -->
        <div v-show="modalTab === 'EDIT'" class="space-y-6 max-h-[70vh] overflow-y-auto pr-2">
          <!-- Remarks inputs -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                Remarques de l'enseignant (Français - LTR)
              </label>
              <textarea
                v-model="editForm.teacherRemarksFr"
                rows="4"
                class="w-full p-3 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"
                placeholder="Excellente participation, travail soigné..."
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 text-right">
                (Arabe - RTL) ملاحظات المعلم(ة)
              </label>
              <textarea
                v-model="editForm.teacherRemarksAr"
                rows="4"
                dir="rtl"
                class="w-full p-3 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-arabic"
                placeholder="تلميذ(ة) مجتهد(ة)..."
              ></textarea>
            </div>
          </div>

          <!-- Skills 1 to 4 Stars Editor -->
          <div class="space-y-4">
            <h4 class="font-bold text-xs uppercase text-gray-500 border-b pb-2">
              Niveau d'Acquisition par Compétence (1 à 4 étoiles)
            </h4>

            <div
              v-for="sub in activeStudentData.curriculum"
              :key="sub.id"
              class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 bg-gray-50/50 dark:bg-gray-700/30 space-y-3"
            >
              <div class="flex items-center justify-between font-bold text-xs text-gray-900 dark:text-white border-b pb-2">
                <span>{{ sub.name }}</span>
                <span class="font-arabic font-semibold text-emerald-800 dark:text-emerald-400">{{ sub.nameAr }}</span>
              </div>

              <div class="space-y-2">
                <div
                  v-for="sk in sub.skills"
                  :key="sk.id"
                  class="flex items-center justify-between text-xs py-1"
                >
                  <span class="text-gray-700 dark:text-gray-300">{{ sk.name }}</span>
                  <div class="flex items-center gap-1">
                    <button
                      v-for="star in 4"
                      :key="star"
                      type="button"
                      @click="editForm.ratings[sk.id] = star"
                      class="text-lg leading-none transition-transform hover:scale-125"
                      :class="star <= (editForm.ratings[sk.id] || 3) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                    >
                      ★
                    </button>
                    <span class="text-[11px] font-bold text-gray-500 ml-1">
                      ({{ editForm.ratings[sk.id] || 3 }}/4)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              @click="saveBulletinEdits"
              :disabled="isSaving"
              class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2 disabled:opacity-50"
            >
              <span v-if="isSaving" class="animate-spin">⏳</span>
              <span v-else>💾</span>
              Enregistrer les Modifications
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden Offscreen Container for Batch Rendering / Direct PDF Downloads -->
    <div
      v-if="offscreenStudentData"
      id="offscreen-bulletin-container"
      style="position: fixed; left: -9999px; top: 0; width: 210mm; background: white;"
    >
      <OfficialBilingualBulletin
        elementId="offscreen-official-bulletin"
        :data="offscreenStudentData"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import JSZip from 'jszip'
import apiClient from '../../plugins/axios'
import OfficialBilingualBulletin from '../../components/OfficialBilingualBulletin.vue'
import { generateBilingualBulletinPdf, getBilingualBulletinPdfBlob } from '../../plugins/pdfGenerator'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const loading = ref(false)
const students = ref([])
const classesList = ref([])
const availableYears = ref(['2024-2025', '2025-2026', '2026-2027'])
const availableTerms = ref([
  'Premier Semestre 2024/2025',
  'Deuxième Semestre 2024/2025',
  'Trimestre 1 (2026-2027)',
  'Trimestre 2 (2026-2027)',
  'Trimestre 3 (2025-2026)'
])

const filters = ref({
  search: '',
  classId: '',
  schoolYear: '2024-2025',
  term: 'Premier Semestre 2024/2025'
})

const selectedStudentIds = ref([])
const showModal = ref(false)
const modalTab = ref('PREVIEW')
const activeStudentData = ref(null)
const offscreenStudentData = ref(null)
const isGeneratingPdf = ref(false)
const isGeneratingZip = ref(false)
const isSaving = ref(false)

const editForm = ref({
  teacherRemarksFr: '',
  teacherRemarksAr: '',
  ratings: {}
})

let searchTimeout = null
function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchStudents()
  }, 350)
}

const isAllSelected = computed(() => {
  return students.value.length > 0 && selectedStudentIds.value.length === students.value.length
})

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedStudentIds.value = []
  } else {
    selectedStudentIds.value = students.value.map(s => s.id)
  }
}

async function fetchStudents() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      term: filters.value.term,
      school_year: filters.value.schoolYear
    })
    if (filters.value.classId) params.append('class_id', filters.value.classId)
    if (filters.value.search) params.append('search', filters.value.search)

    const res = await apiClient.get(`/admin/pedagogy/bulletins/students?${params.toString()}`)
    if (res.data) {
      students.value = res.data.students || []
      if (Array.isArray(res.data.classes)) classesList.value = res.data.classes
      if (Array.isArray(res.data.availableYears)) availableYears.value = res.data.availableYears
      if (Array.isArray(res.data.availableTerms)) availableTerms.value = res.data.availableTerms
    }
  } catch (err) {
    console.error('Erreur chargement élèves pour bulletins:', err)
  } finally {
    loading.value = false
  }
}

async function openPreviewModal(student) {
  try {
    const res = await apiClient.get(
      `/admin/pedagogy/bulletins/detail/${student.id}?term=${encodeURIComponent(filters.value.term)}&school_year=${encodeURIComponent(filters.value.schoolYear)}`
    )
    if (res.data) {
      activeStudentData.value = res.data
      modalTab.value = 'PREVIEW'
      editForm.value = {
        teacherRemarksFr: res.data.teacherRemarks?.fr || '',
        teacherRemarksAr: res.data.teacherRemarks?.ar || '',
        ratings: { ...(res.data.ratings || {}) }
      }
      showModal.value = true
    }
  } catch (err) {
    console.error('Erreur détail bulletin:', err)
    showErrorAlert('Erreur', 'Impossible de charger les données détaillées du bulletin.')
  }
}

async function saveBulletinEdits() {
  if (!activeStudentData.value?.student?.id) return
  isSaving.value = true
  try {
    await apiClient.post('/admin/pedagogy/bulletins/save', {
      studentId: activeStudentData.value.student.id,
      term: filters.value.term,
      schoolYear: filters.value.schoolYear,
      ratings: editForm.value.ratings,
      teacherRemarks: editForm.value.teacherRemarksFr,
      teacherRemarksAr: editForm.value.teacherRemarksAr
    })

    // Update active view
    activeStudentData.value.teacherRemarks.fr = editForm.value.teacherRemarksFr
    activeStudentData.value.teacherRemarks.ar = editForm.value.teacherRemarksAr
    activeStudentData.value.ratings = { ...editForm.value.ratings }

    // Recompute star symbols
    const symbols = {}
    for (const [skId, val] of Object.entries(editForm.value.ratings)) {
      symbols[skId] = '*'.repeat(Math.min(4, Math.max(1, parseInt(val, 10) || 3)))
    }
    activeStudentData.value.starSymbols = symbols

    showSuccessAlert('Enregistré !', 'Les appréciations et les évaluations ont été mises à jour avec succès.')
    modalTab.value = 'PREVIEW'
    fetchStudents()
  } catch (err) {
    console.error('Erreur sauvegarde bulletin:', err)
    showErrorAlert('Erreur', 'Une erreur est survenue lors de la sauvegarde.')
  } finally {
    isSaving.value = false
  }
}

async function downloadActiveBulletinPdf() {
  if (!activeStudentData.value) return
  isGeneratingPdf.value = true
  try {
    const student = activeStudentData.value.student
    const period = activeStudentData.value.period
    const cleanLastName = (student.lastName || 'Nom').replace(/\s+/g, '_')
    const cleanFirstName = (student.firstName || 'Prenom').replace(/\s+/g, '_')
    const cleanYear = (period.schoolYear || '2024-2025').replace(/\s+/g, '')
    const cleanTerm = (period.term || 'S1').replace(/\s+/g, '_').replace(/[\/\\:]/g, '-')
    const filename = `Bulletin_${cleanLastName}_${cleanFirstName}_${cleanYear}_${cleanTerm}.pdf`

    await generateBilingualBulletinPdf('modal-official-bulletin', filename)
    showSuccessAlert('Téléchargement terminé', `Le bulletin scolaire de ${student.fullName} a été téléchargé avec succès.`)
  } catch (err) {
    console.error('Erreur génération PDF:', err)
    showErrorAlert('Erreur PDF', 'Impossible de générer le fichier PDF.')
  } finally {
    isGeneratingPdf.value = false
  }
}

function printCurrentBulletin() {
  window.print()
}

async function downloadSinglePdf(student) {
  try {
    const res = await apiClient.get(
      `/admin/pedagogy/bulletins/detail/${student.id}?term=${encodeURIComponent(filters.value.term)}&school_year=${encodeURIComponent(filters.value.schoolYear)}`
    )
    if (res.data) {
      offscreenStudentData.value = res.data
      await new Promise(resolve => setTimeout(resolve, 300))

      const s = res.data.student
      const p = res.data.period
      const cleanLastName = (s.lastName || 'Nom').replace(/\s+/g, '_')
      const cleanFirstName = (s.firstName || 'Prenom').replace(/\s+/g, '_')
      const cleanYear = (p.schoolYear || '2024-2025').replace(/\s+/g, '')
      const cleanTerm = (p.term || 'S1').replace(/\s+/g, '_').replace(/[\/\\:]/g, '-')
      const filename = `Bulletin_${cleanLastName}_${cleanFirstName}_${cleanYear}_${cleanTerm}.pdf`

      await generateBilingualBulletinPdf('offscreen-official-bulletin', filename)
      showSuccessAlert('Téléchargement', `Bulletin officiel généré pour ${s.fullName}.`)
    }
  } catch (err) {
    console.error('Erreur téléchargement direct PDF:', err)
    showErrorAlert('Erreur', 'Impossible de générer le bulletin pour cet élève.')
  } finally {
    offscreenStudentData.value = null
  }
}

async function downloadBatchZip() {
  if (selectedStudentIds.value.length === 0) return
  isGeneratingZip.value = true

  const zip = new JSZip()
  const total = selectedStudentIds.value.length
  let successCount = 0

  try {
    for (let i = 0; i < total; i++) {
      const studentId = selectedStudentIds.value[i]
      try {
        const res = await apiClient.get(
          `/admin/pedagogy/bulletins/detail/${studentId}?term=${encodeURIComponent(filters.value.term)}&school_year=${encodeURIComponent(filters.value.schoolYear)}`
        )
        if (res.data) {
          offscreenStudentData.value = res.data
          await new Promise(resolve => setTimeout(resolve, 250))

          const s = res.data.student
          const p = res.data.period
          const cleanLastName = (s.lastName || 'Nom').replace(/\s+/g, '_')
          const cleanFirstName = (s.firstName || 'Prenom').replace(/\s+/g, '_')
          const cleanYear = (p.schoolYear || '2024-2025').replace(/\s+/g, '')
          const cleanTerm = (p.term || 'S1').replace(/\s+/g, '_').replace(/[\/\\:]/g, '-')
          const filename = `Bulletin_${cleanLastName}_${cleanFirstName}_${cleanYear}_${cleanTerm}.pdf`

          const pdfBlob = await getBilingualBulletinPdfBlob('offscreen-official-bulletin')
          zip.file(filename, pdfBlob)
          successCount++
        }
      } catch (e) {
        console.warn(`Erreur génération pour élève #${studentId}:`, e)
      }
    }

    if (successCount > 0) {
      const zipBlob = await zip.generateAsync({ type: 'blob' })
      const url = URL.createObjectURL(zipBlob)
      const a = document.createElement('a')
      a.href = url
      a.download = `Bulletins_TijanesNours_${filters.value.schoolYear}_${filters.value.term.replace(/\s+/g, '_')}.zip`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)

      showSuccessAlert(
        'Archive ZIP Prête',
        `L'archive contenant les <strong>${successCount}</strong> bulletins PDF a été générée et téléchargée.`
      )
    } else {
      showErrorAlert('Erreur ZIP', 'Aucun bulletin n\'a pu être généré.')
    }
  } catch (err) {
    console.error('Erreur génération ZIP:', err)
    showErrorAlert('Erreur ZIP', 'Une erreur est survenue lors de la création du fichier ZIP.')
  } finally {
    isGeneratingZip.value = false
    offscreenStudentData.value = null
  }
}

onMounted(() => {
  fetchStudents()
})
</script>

<style scoped>
.font-arabic {
  font-family: 'Amiri', 'Noto Naskh Arabic', serif;
}
</style>
