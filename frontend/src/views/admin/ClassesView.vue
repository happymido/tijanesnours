<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Classes & Affectations</h1>
        <p class="text-xs text-gray-500">Composition des classes, affectation des enseignants et plannings</p>
      </div>
      <button @click="showClassModal = true" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
        <span>+</span> Créer une Classe
      </button>
    </div>

    <!-- Classrooms Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="cls in classrooms" :key="cls.id" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex justify-between items-start border-b pb-3 dark:border-gray-700">
          <div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
              {{ cls.level }}
            </span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ cls.name }}</h3>
          </div>
          <div class="text-right">
            <span class="text-xs font-extrabold text-emerald-600">{{ cls.students.length }} / {{ cls.capacity }}</span>
            <p class="text-[10px] text-gray-400">Élèves inscrits</p>
          </div>
        </div>

        <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1.5">
          <p class="flex items-center gap-2">
            <span class="font-bold text-gray-400">👨‍🏫 Professeur :</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ cls.teacher }}</span>
          </p>
          <p class="flex items-center gap-2">
            <span class="font-bold text-gray-400">⏰ Horaire :</span>
            <span>{{ cls.schedule }}</span>
          </p>
          <p class="flex items-center gap-2">
            <span class="font-bold text-gray-400">🚪 Salle :</span>
            <span>{{ cls.room }}</span>
          </p>
        </div>

        <!-- Student Roster Quick Preview -->
        <div class="space-y-2 pt-2 border-t dark:border-gray-700">
          <div class="flex justify-between text-[11px] font-bold text-gray-500">
            <span>Liste des Élèves</span>
            <button @click="openRoster(cls)" class="text-brand-600 hover:underline">+ Gérer la liste</button>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="(st, idx) in cls.students.slice(0, 4)" :key="idx" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-semibold">
              {{ st }}
            </span>
            <span v-if="cls.students.length > 4" class="px-2 py-0.5 bg-brand-50 text-brand-700 rounded text-[10px] font-bold">
              +{{ cls.students.length - 4 }} autres
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Création Classe -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Créer une Nouvelle Classe</h3>
          <button @click="showClassModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="createClass" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Nom de la Classe</label>
            <input v-model="newClass.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: Classe Débutant 3B" />
          </div>
          <div>
            <label class="block font-semibold mb-1">Tranche d'âge / Niveau</label>
            <select v-model="newClass.level" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
              <option value="Maternelle (4-5 ans)">Maternelle (4-5 ans)</option>
              <option value="Débutant (6-8 ans)">Débutant (6-8 ans)</option>
              <option value="Intermédiaire (9-12 ans)">Intermédiaire (9-12 ans)</option>
              <option value="Avancé / Tajwid (13-16 ans)">Avancé / Tajwid (13-16 ans)</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold mb-1">Enseignant Référent</label>
            <select v-model="newClass.teacher" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
              <option value="Cheikh Mahmoud">Cheikh Mahmoud</option>
              <option value="Oustaz Hassan">Oustaz Hassan</option>
              <option value="Mme Souad">Mme Souad</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Créneau Horaire</label>
              <input v-model="newClass.schedule" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Samedi 09:00 - 12:00" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Salle de cours</label>
              <input v-model="newClass.room" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Salle 3" />
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow">
              Enregistrer la Classe
            </button>
            <button type="button" @click="showClassModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Liste & Composition des Élèves -->
    <div v-if="showRosterModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ activeClassroom?.name }}</h3>
            <p class="text-xs text-gray-500">Composition des élèves ({{ activeClassroom?.students.length }} élèves)</p>
          </div>
          <button @click="showRosterModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="flex gap-2">
            <input v-model="newStudentName" type="text" placeholder="Nom du nouvel élève à ajouter..." class="flex-1 px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" />
            <button @click="addStudentToClass" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl">+ Ajouter</button>
          </div>

          <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-60 overflow-y-auto">
            <div v-for="(student, idx) in activeClassroom?.students" :key="idx" class="py-2.5 flex justify-between items-center">
              <span class="font-bold text-gray-800 dark:text-gray-200">🎓 {{ student }}</span>
              <button @click="removeStudentFromClass(idx)" class="text-red-500 hover:underline font-bold text-[10px]">Retirer</button>
            </div>
          </div>
        </div>

        <div class="pt-2 border-t dark:border-gray-700">
          <button @click="showRosterModal = false" class="w-full py-3 bg-brand-600 text-white font-bold rounded-xl text-xs">
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showClassModal = ref(false)
const showRosterModal = ref(false)
const activeClassroom = ref(null)
const newStudentName = ref('')

const newClass = ref({
  name: '',
  level: 'Débutant (6-8 ans)',
  teacher: 'Cheikh Mahmoud',
  schedule: 'Samedi 09:00 - 12:00',
  room: 'Salle Maryam 1',
  capacity: 20
})

const classrooms = ref([
  { id: 1, name: 'Classe Éveil 1', level: 'Maternelle (4-5 ans)', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 1', capacity: 20, students: ['Maryam El Amrani', 'Rayane Bennani', 'Lina Hamdi', 'Sami Kassam'] },
  { id: 2, name: 'Classe Débutant 2A', level: 'Débutant (6-8 ans)', teacher: 'Oustaz Hassan', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 2', capacity: 20, students: ['Youssef Benali', 'Bilal Chraibi', 'Aya Zahaf', 'Omar Faraj', 'Sofia Alami'] },
  { id: 3, name: 'Classe Intermédiaire 1', level: 'Intermédiaire (9-12 ans)', teacher: 'Mme Souad', schedule: 'Dimanche 10:00 - 13:00', room: 'Salle Paladium 3', capacity: 18, students: ['Adam Mansouri', 'Zayd Tazi', 'Sarah Berrada'] },
  { id: 4, name: 'Classe Avancé Tajwid', level: 'Avancé / Tajwid (13-16 ans)', teacher: 'Cheikh Mahmoud', schedule: 'Dimanche 14:00 - 17:00', room: 'Grand Amphithéâtre', capacity: 25, students: ['Inès Khadiri', 'Hamza Naceri', 'Nour El Hoda'] }
])

function createClass() {
  const created = {
    id: Date.now(),
    name: newClass.value.name,
    level: newClass.value.level,
    teacher: newClass.value.teacher,
    schedule: newClass.value.schedule,
    room: newClass.value.room,
    capacity: 20,
    students: []
  }
  classrooms.value.unshift(created)
  showClassModal.value = false
  alert(`La classe ${created.name} a été créée avec succès !`)
}

function openRoster(cls) {
  activeClassroom.value = cls
  showRosterModal.value = true
}

function addStudentToClass() {
  if (!newStudentName.value) return
  activeClassroom.value.students.push(newStudentName.value)
  newStudentName.value = ''
}

function removeStudentFromClass(idx) {
  activeClassroom.value.students.splice(idx, 1)
}
</script>
