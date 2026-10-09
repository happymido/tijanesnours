<template>
  <div class="space-y-8">
    <!-- Header Banner Enseignant -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-600 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
          Portail Enseignant & Pédagogie
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Bienvenue, {{ teacherName }}</h1>
        <p class="text-emerald-100 text-sm mt-1">Feuille d'appel numérique, saisie des notes et suivi de mémorisation du Tajwid</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Classes Affectées</span>
          <strong class="text-xl font-extrabold">{{ assignedClasses.length }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Total Élèves</span>
          <strong class="text-xl font-extrabold">24</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Enseignant -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeTab = 'ATTENDANCE'" :class="activeTab === 'ATTENDANCE' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-gray-500 pb-3'">
        📋 Feuille d'Appel du Week-end
      </button>
      <button @click="activeTab = 'GRADES'" :class="activeTab === 'GRADES' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-gray-500 pb-3'">
        📝 Saisie des Évaluations & Tajwid
      </button>
    </div>

    <!-- TAB 1: Feuille d'Appel Numérique -->
    <div v-if="activeTab === 'ATTENDANCE'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white">Validation de Présence par Classe</h3>
          <p class="text-xs text-gray-500">Sélectionnez la classe et cochez le statut de chaque élève pour ce cours</p>
        </div>

        <div class="flex items-center gap-3">
          <select v-model="selectedClass" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-emerald-700">
            <option v-for="c in assignedClasses" :key="c.id" :value="c.name">{{ c.name }} ({{ c.schedule }})</option>
          </select>

          <input type="date" v-model="attendanceDate" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold" />
        </div>
      </div>

      <!-- Liste des Élèves de la classe pour l'Appel -->
      <div class="space-y-3">
        <div v-for="student in studentsList" :key="student.id" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 font-bold text-white text-xs flex items-center justify-center shadow">
              {{ student.name[0] }}
            </div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ student.name }}</h4>
              <p class="text-xs text-gray-400">Parent: {{ student.parentName }} ({{ student.phone }})</p>
            </div>
          </div>

          <!-- Boutons de Statut Présence -->
          <div class="flex items-center gap-2">
            <button
              @click="student.attendance = 'PRESENT'"
              :class="student.attendance === 'PRESENT' ? 'bg-emerald-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-emerald-50'"
              class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
            >
              <span>🟢</span> Présent
            </button>

            <button
              @click="student.attendance = 'ABSENT'"
              :class="student.attendance === 'ABSENT' ? 'bg-red-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-red-50'"
              class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
            >
              <span>🔴</span> Absent
            </button>

            <button
              @click="student.attendance = 'LATE'"
              :class="student.attendance === 'LATE' ? 'bg-amber-500 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-amber-50'"
              class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
            >
              <span>🟡</span> Retard
            </button>
          </div>
        </div>
      </div>

      <div class="flex justify-end pt-4 border-t dark:border-gray-700">
        <button @click="submitAttendance" :disabled="saving" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50">
          <span>💾</span> {{ saving ? 'Enregistrement...' : 'Valider & Enregistrer l\'Appel' }}
        </button>
      </div>
    </div>

    <!-- TAB 2: Saisie des Évaluations & Tajwid -->
    <div v-if="activeTab === 'GRADES'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white">Évaluations Trimestrielles & Progression Tajwid</h3>
          <p class="text-xs text-gray-500">Saisissez les notes et appréciations individuelles</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Nom de l'Élève</th>
              <th class="py-3 px-4">Note / 20</th>
              <th class="py-3 px-4">Sourates Mémorisées</th>
              <th class="py-3 px-4">Appréciation Pédagogique</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="student in studentsList" :key="student.id">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ student.name }}</td>
              <td class="py-3.5 px-4">
                <input v-model="student.grade" type="text" class="w-20 px-2 py-1 rounded-lg border bg-gray-50 dark:bg-gray-700 font-bold text-emerald-600" />
              </td>
              <td class="py-3.5 px-4">
                <input v-model="student.sourates" type="text" class="w-32 px-2 py-1 rounded-lg border bg-gray-50 dark:bg-gray-700" />
              </td>
              <td class="py-3.5 px-4">
                <input v-model="student.comment" type="text" class="w-full px-2 py-1 rounded-lg border bg-gray-50 dark:bg-gray-700" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end pt-4 border-t dark:border-gray-700">
        <button @click="submitGrades" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
          <span>📝</span> Enregistrer le Carnet d'Évaluations
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const activeTab = ref('ATTENDANCE')
const teacherName = ref('Cheikh Mahmoud')
const selectedClass = ref('Classe Débutant 2A')
const attendanceDate = ref(new Date().toISOString().split('T')[0])
const saving = ref(false)

const assignedClasses = ref([
  { id: 1, name: 'Classe Débutant 2A', schedule: 'Samedi 09:00 - 12:00' },
  { id: 2, name: 'Classe Éveil 1', schedule: 'Samedi 09:00 - 12:00' }
])

const studentsList = ref([
  { id: 1, name: 'Youssef Benali', parentName: 'Karim Benali', phone: '+352 691 123 456', attendance: 'PRESENT', grade: '18.5/20', sourates: '12 Sourates', comment: 'Excellente assiduité et récitation très fluide.' },
  { id: 2, name: 'Aya Benali', parentName: 'Karim Benali', phone: '+352 691 123 456', attendance: 'PRESENT', grade: '19/20', sourates: '4 Sourates', comment: 'Très bonne écoute et motivation.' },
  { id: 3, name: 'Rayane Bennani', parentName: 'Mehdi Bennani', phone: '+352 691 888 777', attendance: 'PRESENT', grade: '17/20', sourates: '8 Sourates', comment: 'Bonne participation en classe.' }
])

async function submitAttendance() {
  saving.value = true
  try {
    const presentsCount = studentsList.value.filter(s => s.attendance === 'PRESENT').length
    showSuccessAlert(
      'Feuille d\'Appel Enregistrée ! 🎉',
      `La feuille d'appel du <strong>${attendanceDate.value}</strong> pour la <strong>${selectedClass.value}</strong> a été enregistrée avec succès. (<strong>${presentsCount}/${studentsList.value.length} Présents</strong>)`
    )
  } catch (err) {
    showErrorAlert('Erreur', 'Erreur lors de la validation de la feuille d\'appel.')
  } finally {
    saving.value = false
  }
}

function submitGrades() {
  showSuccessAlert(
    'Évaluations Enregistrées ! 📝',
    `Les notes et appréciations pédagogiques de la <strong>${selectedClass.value}</strong> ont été enregistrées avec succès.`
  )
}
</script>
