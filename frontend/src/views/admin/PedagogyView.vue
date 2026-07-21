<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion Pédagogique & Suivi Académique</h1>
        <p class="text-xs text-gray-500">Présences, Examens, Notes, Génération des Bulletins PDF et Historique Scolaire</p>
      </div>
      <div class="flex gap-3">
        <button @click="showGradeModal = true" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
          + Nouvelle Évaluation / Examen
        </button>
        <button @click="generateAllBulletins" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all">
          📄 Générer Bulletins Trimestriels PDF
        </button>
      </div>
    </div>

    <!-- Tabs Filter -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeSubTab = 'GRADES'" :class="activeSubTab === 'GRADES' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Examens & Notes</button>
      <button @click="activeSubTab = 'ATTENDANCE'" :class="activeSubTab === 'ATTENDANCE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Présences & Absences</button>
      <button @click="activeSubTab = 'BULLETINS'" :class="activeSubTab === 'BULLETINS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Bulletins Scolaires PDF</button>
      <button @click="activeSubTab = 'HISTORY'" :class="activeSubTab === 'HISTORY' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Historique Scolaire</button>
    </div>

    <!-- 1. Examens & Notes Section -->
    <div v-if="activeSubTab === 'GRADES'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex justify-between items-center border-b pb-4 dark:border-gray-700">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Liste des Évaluations & Relevé de Notes</h3>
        <select v-model="selectedClassFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 text-xs font-semibold">
          <option value="ALL">Toutes les classes</option>
          <option value="Classe Débutant 2A">Classe Débutant 2A</option>
          <option value="Classe Éveil 1">Classe Éveil 1</option>
          <option value="Classe Avancé Tajwid">Classe Avancé Tajwid</option>
        </select>
      </div>

      <div class="overflow-x-auto">
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
            <tr v-for="grade in gradesList" :key="grade.id">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ grade.student }}</td>
              <td class="py-3.5 px-4 font-medium">{{ grade.title }}</td>
              <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-brand-50 text-brand-700 font-bold text-[10px]">{{ grade.subject }}</span></td>
              <td class="py-3.5 px-4 text-gray-500">{{ grade.date }}</td>
              <td class="py-3.5 px-4 font-extrabold text-brand-600 text-sm">{{ grade.score }} / 20</td>
              <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-gray-100 font-bold text-[10px]">{{ grade.term }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 2. Présences & Absences Section -->
    <div v-if="activeSubTab === 'ATTENDANCE'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex justify-between items-center border-b pb-4 dark:border-gray-700">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Registre des Présences</h3>
        <button @click="markAttendance" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow">
          + Saisir Registre du Jour
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Élève</th>
              <th class="py-3 px-4">Classe</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Statut</th>
              <th class="py-3 px-4">Justificatif</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="att in attendanceList" :key="att.id">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ att.student }}</td>
              <td class="py-3.5 px-4">{{ att.classroom }}</td>
              <td class="py-3.5 px-4 text-gray-500">{{ att.date }}</td>
              <td class="py-3.5 px-4">
                <span
                  :class="att.status === 'PRÉSENT' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                >
                  {{ att.status }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-gray-500">{{ att.justification || 'N/A' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 3. Bulletins PDF Section -->
    <div v-if="activeSubTab === 'BULLETINS'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">Bulletins Scolaires Trimestriels PDF</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="b in bulletinsList" :key="b.id" class="p-4 rounded-xl border dark:border-gray-700 flex justify-between items-center">
          <div>
            <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ b.student }}</h4>
            <p class="text-xs text-gray-500">{{ b.term }} • Moyenne: <strong class="text-brand-600">{{ b.average }}/20</strong></p>
          </div>
          <button @click="downloadPdf(b)" class="px-3 py-1.5 bg-brand-50 text-brand-700 font-bold text-xs rounded-xl hover:bg-brand-100">
            📥 Télécharger PDF
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Examen / Note -->
    <div v-if="showGradeModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6 text-xs">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ajouter une Note d'Examen</h3>
        <div>
          <label class="block font-semibold mb-1">Nom de l'Élève</label>
          <input v-model="newGrade.student" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50" placeholder="ex: Youssef Benali" />
        </div>
        <div>
          <label class="block font-semibold mb-1">Intitulé de l'Examen</label>
          <input v-model="newGrade.title" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50" placeholder="ex: Examen Récitation Sourate Al-Mulk" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1">Matière</label>
            <select v-model="newGrade.subject" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 font-semibold">
              <option value="Coran & Tajwid">Coran & Tajwid</option>
              <option value="Langue Arabe">Langue Arabe</option>
              <option value="Éducation Éthique">Éducation Éthique</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold mb-1">Note sur 20</label>
            <input v-model="newGrade.score" type="number" step="0.5" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 font-bold" placeholder="18.5" />
          </div>
        </div>
        <div class="flex gap-3 pt-2">
          <button @click="saveGrade" class="flex-1 py-3 bg-brand-600 text-white font-bold rounded-xl">Enregistrer Note</button>
          <button @click="showGradeModal = false" class="py-3 px-4 border rounded-xl">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeSubTab = ref('GRADES')
const selectedClassFilter = ref('ALL')
const showGradeModal = ref(false)

const newGrade = ref({ student: '', title: '', subject: 'Coran & Tajwid', score: 18.0 })

const gradesList = ref([
  { id: 1, student: 'Youssef Benali', title: 'Récitation Sourate Al-Mulk', subject: 'Coran & Tajwid', date: '18/05/2026', score: '18.50', term: 'Trimestre 3' },
  { id: 2, student: 'Maryam El Amrani', title: 'Vocabulaire & Alphabet Arabe', subject: 'Langue Arabe', date: '20/05/2026', score: '19.00', term: 'Trimestre 3' },
  { id: 3, student: 'Adam Mansouri', title: 'Règles de Nun Sakina (Tajwid)', subject: 'Coran & Tajwid', date: '22/05/2026', score: '16.50', term: 'Trimestre 3' }
])

const attendanceList = ref([
  { id: 1, student: 'Youssef Benali', classroom: 'Classe Débutant 2A', date: '21/07/2026', status: 'PRÉSENT', justification: null },
  { id: 2, student: 'Adam Mansouri', classroom: 'Classe Intermédiaire 1', date: '21/07/2026', status: 'ABSENT', justification: 'Motif médical (Certificat)' }
])

const bulletinsList = ref([
  { id: 1, student: 'Youssef Benali', term: 'Trimestre 3 (2025-2026)', average: '18.5' },
  { id: 2, student: 'Maryam El Amrani', term: 'Trimestre 3 (2025-2026)', average: '19.0' }
])

function saveGrade() {
  gradesList.value.unshift({
    id: Date.now(),
    student: newGrade.value.student,
    title: newGrade.value.title,
    subject: newGrade.value.subject,
    date: '22/07/2026',
    score: numberFormat(newGrade.value.score),
    term: 'Trimestre 3'
  })
  showGradeModal.value = false
  alert('Note enregistrée avec succès !')
}

function numberFormat(val) {
  return parseFloat(val).toFixed(2)
}

function generateAllBulletins() {
  alert('Génération automatique des bulletins PDF pour l\'ensemble des 184 élèves terminée !')
}

function downloadPdf(bulletin) {
  alert(`Téléchargement du bulletin PDF officiel de ${bulletin.student}`)
}

function markAttendance() {
  alert('Registre des présences du jour ouvert pour saisie.')
}
</script>
