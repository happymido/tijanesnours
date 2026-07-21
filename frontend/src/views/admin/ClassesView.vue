<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion & Configuration Scolaire</h1>
        <p class="text-xs text-gray-500">Catégories de cours, Niveaux, Tableau détaillé des classes et Affectations des enseignants</p>
      </div>
      <div class="flex gap-3">
        <button @click="openClassModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Nouvelle Classe
        </button>
        <button @click="openConfigModal('CATEGORY')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Nouvelle Catégorie
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="currentSubTab = 'TABLE'" :class="currentSubTab === 'TABLE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        📋 Tableau Général des Classes ({{ classrooms.length }})
      </button>
      <button @click="currentSubTab = 'TEACHER_ASSIGNMENTS'" :class="currentSubTab === 'TEACHER_ASSIGNMENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        👨‍🏫 Affectations Enseignants ({{ teachersList.length }})
      </button>
      <button @click="currentSubTab = 'CATEGORIES'" :class="currentSubTab === 'CATEGORIES' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        📚 Catégories de Cours ({{ categories.length }})
      </button>
      <button @click="currentSubTab = 'LEVELS'" :class="currentSubTab === 'LEVELS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        📊 Niveaux de Cours ({{ levels.length }})
      </button>
    </div>

    <!-- TAB 1: Tableau Général des Classes (Datatable) -->
    <div v-if="currentSubTab === 'TABLE'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Liste Détaillée des Classes</h3>
        <input
          v-model="classSearch"
          type="text"
          placeholder="Filtrer par nom, enseignant, salle..."
          class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 border text-xs w-64"
        />
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3.5 px-4">Classe</th>
              <th class="py-3.5 px-4">Niveau / Tranche d'âge</th>
              <th class="py-3.5 px-4">Catégories Enseignées</th>
              <th class="py-3.5 px-4">Enseignant Affecté</th>
              <th class="py-3.5 px-4">Salle & Créneau</th>
              <th class="py-3.5 px-4">Effectifs</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="cls in filteredClassrooms" :key="cls.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ cls.name }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                  {{ cls.level }}
                </span>
              </td>
              <td class="py-3.5 px-4 font-semibold text-gray-600 dark:text-gray-300">
                <span v-for="(cat, i) in cls.categories" :key="i" class="inline-block mr-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">
                  {{ cat }}
                </span>
              </td>
              <td class="py-3.5 px-4 font-bold text-emerald-600 flex items-center gap-2">
                <span>👨‍🏫</span> {{ cls.teacher }}
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                <div>🚪 {{ cls.room }}</div>
                <div class="text-[10px] text-gray-400">⏰ {{ cls.schedule }}</div>
              </td>
              <td class="py-3.5 px-4">
                <span class="font-extrabold text-gray-900 dark:text-white">{{ cls.students.length }}</span> / {{ cls.capacity }}
                <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                  <div :style="{ width: (cls.students.length / cls.capacity * 100) + '%' }" class="h-full bg-emerald-500"></div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button @click="openRoster(cls)" class="text-brand-600 font-bold hover:underline">Élèves ({{ cls.students.length }})</button>
                <button @click="openClassModal(cls)" class="text-gray-600 font-bold hover:underline">Éditer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: Affectations Enseignants <-> Classes -->
    <div v-if="currentSubTab === 'TEACHER_ASSIGNMENTS'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
          Matrice d'Affectation des Enseignants aux Classes
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="teacher in teachersList" :key="teacher.id" class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 border space-y-4">
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm">
                  {{ teacher.name[0] }}
                </div>
                <div>
                  <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ teacher.name }}</h4>
                  <span class="text-[10px] text-gray-500 font-medium">{{ teacher.speciality }}</span>
                </div>
              </div>
              <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold text-[10px] rounded-full">
                {{ getClassesForTeacher(teacher.name).length }} Classe(s)
              </span>
            </div>

            <!-- List of assigned classes for this teacher -->
            <div class="space-y-2">
              <span class="text-[10px] uppercase font-bold text-gray-400">Classes Enseignées :</span>
              <div v-if="getClassesForTeacher(teacher.name).length > 0" class="space-y-1.5">
                <div v-for="c in getClassesForTeacher(teacher.name)" :key="c.id" class="p-2 bg-white dark:bg-gray-800 rounded-xl text-xs flex justify-between items-center shadow-sm">
                  <div>
                    <strong class="text-gray-900 dark:text-white">{{ c.name }}</strong>
                    <div class="text-[10px] text-gray-400">{{ c.schedule }} • {{ c.room }}</div>
                  </div>
                  <span class="text-[10px] font-bold text-brand-600">{{ c.students.length }} élèves</span>
                </div>
              </div>
              <div v-else class="text-xs text-gray-400 italic">Aucune classe affectée.</div>
            </div>

            <button @click="openAssignModal(teacher)" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl transition-all">
              Gérer les Affectations
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: Configuration des Catégories de Cours -->
    <div v-if="currentSubTab === 'CATEGORIES'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Catégories de Cours Configurées</h3>
        <button @click="openConfigModal('CATEGORY')" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">+ Ajouter Catégorie</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="cat in categories" :key="cat.id" class="p-4 rounded-xl border dark:border-gray-700 space-y-2">
          <div class="flex justify-between items-center">
            <span class="text-2xl">{{ cat.icon }}</span>
            <span class="text-xs font-bold text-brand-600">{{ cat.classesCount }} classes</span>
          </div>
          <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ cat.name }}</h4>
          <p class="text-xs text-gray-500">{{ cat.description }}</p>
        </div>
      </div>
    </div>

    <!-- TAB 4: Configuration des Niveaux de Cours -->
    <div v-if="currentSubTab === 'LEVELS'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Niveaux de Cours Configurés (4 à 16 ans)</h3>
        <button @click="openConfigModal('LEVEL')" class="px-3 py-1.5 bg-brand-600 text-white font-bold text-xs rounded-xl">+ Ajouter Niveau</button>
      </div>

      <div class="space-y-3">
        <div v-for="lvl in levels" :key="lvl.id" class="p-4 rounded-xl border dark:border-gray-700 flex justify-between items-center">
          <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700">{{ lvl.ageGroup }}</span>
            <h4 class="font-bold text-sm text-gray-900 dark:text-white mt-1">{{ lvl.title }}</h4>
            <p class="text-xs text-gray-500">{{ lvl.description }}</p>
          </div>
          <button class="text-brand-600 font-bold text-xs hover:underline">Modifier</button>
        </div>
      </div>
    </div>

    <!-- Modal Form Création / Édition Classe -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Configuration de la Classe</h3>
          <button @click="showClassModal = false" class="text-gray-400 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveClass" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Nom de la Classe</label>
            <input v-model="classForm.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: Classe Débutant 2A" />
          </div>
          <div>
            <label class="block font-semibold mb-1">Niveau de cours</label>
            <select v-model="classForm.level" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
              <option v-for="l in levels" :key="l.id" :value="l.title">{{ l.title }} ({{ l.ageGroup }})</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold mb-1">Enseignant Référent Affecté</label>
            <select v-model="classForm.teacher" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold text-emerald-600">
              <option v-for="t in teachersList" :key="t.id" :value="t.name">{{ t.name }} ({{ t.speciality }})</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Créneau Horaire</label>
              <input v-model="classForm.schedule" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Samedi 09:00 - 12:00" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Salle de cours</label>
              <input v-model="classForm.room" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Salle Maryam 1" />
            </div>
          </div>
          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-brand-600 text-white font-bold rounded-xl shadow">Enregistrer Classe</button>
            <button type="button" @click="showClassModal = false" class="py-3 px-4 border rounded-xl">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const currentSubTab = ref('TABLE')
const classSearch = ref('')
const showClassModal = ref(false)

const classForm = ref({
  name: '',
  level: 'Débutant 2 (6-8 ans)',
  teacher: 'Cheikh Mahmoud',
  schedule: 'Samedi 09:00 - 12:00',
  room: 'Salle Maryam 1',
  capacity: 20
})

const categories = ref([
  { id: 1, name: 'Langue Arabe', icon: '🗣️', description: 'Lecture, écriture, grammaire & vocabulaire', classesCount: 6 },
  { id: 2, name: 'Saint Coran & Tajwid', icon: '📖', description: 'Mémorisation, récitation et règles de Tajwid', classesCount: 4 },
  { id: 3, name: 'Éducation Éthique', icon: '✨', description: 'Valeurs morales et comportementales', classesCount: 2 }
])

const levels = ref([
  { id: 1, title: 'Maternelle / Éveil', ageGroup: '4 - 5 ans', description: 'Initiation ludique aux lettres et à l\'apprentissage oral' },
  { id: 2, title: 'Débutant 1 à 3', ageGroup: '6 - 8 ans', description: 'Apprentissage de la lecture fluide et récitation des courtes sourates' },
  { id: 3, title: 'Intermédiaire 1 à 3', ageGroup: '9 - 12 ans', description: 'Grammaire, expression orale et mémorisation suivie du Coran' },
  { id: 4, title: 'Avancé & Tajwid', ageGroup: '13 - 16 ans', description: 'Étude approfondie de la langue arabe et règles complexes du Tajwid' }
])

const teachersList = ref([
  { id: 1, name: 'Cheikh Mahmoud', speciality: 'Langue Arabe & Tajwid' },
  { id: 2, name: 'Oustaz Hassan', speciality: 'Coran & Mémorisation' },
  { id: 3, name: 'Mme Souad', speciality: 'Éducation Éthique & Arabe' }
])

const classrooms = ref([
  { id: 1, name: 'Classe Éveil 1', level: 'Maternelle / Éveil', categories: ['Langue Arabe', 'Coran & Tajwid'], teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 1', capacity: 20, students: ['Maryam El Amrani', 'Rayane Bennani', 'Lina Hamdi'] },
  { id: 2, name: 'Classe Débutant 2A', level: 'Débutant 1 à 3', categories: ['Langue Arabe', 'Coran & Tajwid'], teacher: 'Oustaz Hassan', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 2', capacity: 20, students: ['Youssef Benali', 'Bilal Chraibi', 'Aya Zahaf'] },
  { id: 3, name: 'Classe Intermédiaire 1', level: 'Intermédiaire 1 à 3', categories: ['Langue Arabe', 'Éducation Éthique'], teacher: 'Mme Souad', schedule: 'Dimanche 10:00 - 13:00', room: 'Salle Paladium 3', capacity: 18, students: ['Adam Mansouri', 'Zayd Tazi'] },
  { id: 4, name: 'Classe Avancé Tajwid', level: 'Avancé & Tajwid', categories: ['Saint Coran & Tajwid'], teacher: 'Cheikh Mahmoud', schedule: 'Dimanche 14:00 - 17:00', room: 'Grand Amphithéâtre', capacity: 25, students: ['Inès Khadiri', 'Hamza Naceri'] }
])

const filteredClassrooms = computed(() => {
  if (!classSearch.value) return classrooms.value
  const q = classSearch.value.toLowerCase()
  return classrooms.value.filter(c => c.name.toLowerCase().includes(q) || c.teacher.toLowerCase().includes(q) || c.room.toLowerCase().includes(q))
})

function getClassesForTeacher(teacherName) {
  return classrooms.value.filter(c => c.teacher === teacherName)
}

function openClassModal(cls = null) {
  if (cls) {
    classForm.value = { ...cls }
  } else {
    classForm.value = { name: '', level: 'Débutant 1 à 3', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00', room: 'Salle Maryam 1', capacity: 20 }
  }
  showClassModal.value = true
}

function saveClass() {
  const existing = classrooms.value.find(c => c.id === classForm.value.id)
  if (existing) {
    Object.assign(existing, classForm.value)
  } else {
    classrooms.value.unshift({
      id: Date.now(),
      ...classForm.value,
      categories: ['Langue Arabe', 'Coran & Tajwid'],
      students: []
    })
  }
  showClassModal.value = false
  alert('Configuration de la classe enregistrée avec succès !')
}

function openAssignModal(teacher) {
  alert(`Gestion des affectations pour ${teacher.name}. Classes actuelles: ${getClassesForTeacher(teacher.name).map(c => c.name).join(', ')}`)
}

function openConfigModal(type) {
  alert(`Ajout d'une nouvelle configuration ${type === 'CATEGORY' ? 'Catégorie de cours' : 'Niveau de cours'}`)
}

function openRoster(cls) {
  alert(`Gestion des effectifs pour ${cls.name} (${cls.students.length} élèves)`)
}
</script>
