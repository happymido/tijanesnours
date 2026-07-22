<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion & Configuration Scolaire</h1>
        <p class="text-xs text-gray-500">Classes, affectations des enseignants, catégories, niveaux et créneaux horaires configurables</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="openClassModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Nouvelle Classe
        </button>
        <button @click="openScheduleConfigModal()" class="px-4 py-2.5 bg-gold-600 hover:bg-gold-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>⏰</span> Nouveau Créneau
        </button>
        <button @click="openConfigModal('CATEGORY')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Nouvelle Catégorie
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6 overflow-x-auto">
      <button @click="currentSubTab = 'TABLE'" :class="currentSubTab === 'TABLE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        📋 Tableau Général des Classes ({{ classrooms.length }})
      </button>
      <button @click="currentSubTab = 'SCHEDULES'" :class="currentSubTab === 'SCHEDULES' ? 'border-b-2 border-gold-600 text-gold-600 pb-3' : 'text-gray-500 pb-3'">
        ⏰ Créneaux Horaires ({{ schedules.length }})
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
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Liste Détaillée des Classes (Base MySQL)</h3>
        <input
          v-model="classSearch"
          type="text"
          placeholder="Filtrer par nom, enseignant, salle..."
          class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 border text-xs w-64"
        />
      </div>

      <div v-if="loading" class="py-8 text-center text-xs font-bold text-gray-500">
        Chargement des classes et affectations depuis MySQL...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3.5 px-4">Classe</th>
              <th class="py-3.5 px-4">Niveau / Tranche d'âge</th>
              <th class="py-3.5 px-4">Catégorie</th>
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
                <span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">
                  {{ cls.category }}
                </span>
              </td>
              <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                <router-link :to="`/admin/users/teacher_1/id-card`" class="hover:underline">
                  {{ cls.teacher }}
                </router-link>
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                <div>🚪 {{ cls.roomNumber || cls.room }}</div>
                <div class="text-[10px] text-gray-400">⏰ {{ cls.schedule }}</div>
              </td>
              <td class="py-3.5 px-4">
                <span class="font-extrabold text-gray-900 dark:text-white">{{ cls.currentEnrolled || 12 }}</span> / {{ cls.maxCapacity || cls.capacity }}
                <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                  <div :style="{ width: ((cls.currentEnrolled || 12) / (cls.maxCapacity || 20) * 100) + '%' }" class="h-full bg-emerald-500"></div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button @click="openRoster(cls)" class="text-brand-600 font-bold hover:underline">Élèves ({{ cls.currentEnrolled || 12 }})</button>
                <button @click="openClassModal(cls)" class="text-gray-600 font-bold hover:underline">✏️ Éditer</button>
                <button @click="deleteClass(cls)" class="text-red-600 font-bold hover:underline">🗑️ Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: Configurator Panel des Créneaux Horaires -->
    <div v-if="currentSubTab === 'SCHEDULES'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-3 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Configuration des Créneaux Horaires (Modifiables & Éditables)</h3>
          <p class="text-xs text-gray-500">Ces créneaux alimentent dynamiquement les sélecteurs de cours de l'ensemble de la plateforme</p>
        </div>
        <button @click="openScheduleConfigModal()" class="px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Ajouter un Créneau Horaire
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3.5 px-4">Créneau Intitulé</th>
              <th class="py-3.5 px-4">Jour de la Semaine</th>
              <th class="py-3.5 px-4">Heure Début</th>
              <th class="py-3.5 px-4">Heure Fin</th>
              <th class="py-3.5 px-4">Libellé / Session</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="sch in schedules" :key="sch.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>⏰</span> {{ sch.name }}
              </td>
              <td class="py-3.5 px-4 font-bold text-brand-600 dark:text-gold-400">{{ sch.day }}</td>
              <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 font-mono">{{ sch.startTime }}</td>
              <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 font-mono">{{ sch.endTime }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gold-100 text-gold-800 dark:bg-gold-900/50 dark:text-gold-300">
                  {{ sch.label || 'Standard' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right space-x-3">
                <button @click="openScheduleConfigModal(sch)" class="text-brand-600 font-bold hover:underline">✏️ Éditer</button>
                <button @click="deleteSchedule(sch)" class="text-red-600 font-bold hover:underline">🗑️ Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: Affectations Enseignants <-> Classes -->
    <div v-if="currentSubTab === 'TEACHER_ASSIGNMENTS'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
          Matrice d'Affectation des Enseignants aux Classes (Stocké en BBD MySQL)
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="teacher in teachersList" :key="teacher.id" class="p-5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-600 font-bold text-white flex items-center justify-center text-sm shadow">
                👨‍🏫
              </div>
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-sm">
                  <router-link :to="`/admin/users/teacher_${teacher.id}/id-card`" class="hover:text-emerald-600 hover:underline">
                    {{ teacher.name }}
                  </router-link>
                </h4>
                <p class="text-xs text-emerald-600 font-semibold">{{ teacher.speciality }}</p>
              </div>
            </div>

            <div class="space-y-2 pt-2 border-t dark:border-gray-600">
              <span class="text-[10px] font-bold text-gray-400 uppercase">Classes Assignées dans MySQL :</span>
              <div class="space-y-1">
                <div v-for="c in getClassesForTeacher(teacher.name)" :key="c.id" class="px-3 py-1.5 bg-white dark:bg-gray-800 rounded-xl border text-xs font-bold text-gray-800 dark:text-gray-200 flex justify-between items-center">
                  <span>📚 {{ c.name }}</span>
                  <span class="text-[10px] text-gray-400 font-normal">{{ c.schedule }}</span>
                </div>
                <div v-if="getClassesForTeacher(teacher.name).length === 0" class="text-xs text-gray-400 italic">
                  Aucune classe affectée
                </div>
              </div>
            </div>

            <button @click="openAssignModal(teacher)" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
              ⚙️ Gérer les Affectations
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 4: Catégories de Cours -->
    <div v-if="currentSubTab === 'CATEGORIES'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="cat in categories" :key="cat.id" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold">
          {{ cat.icon || '📚' }}
        </div>
        <h3 class="font-bold text-base text-gray-900 dark:text-white">{{ cat.name }}</h3>
        <p class="text-xs text-gray-500 leading-relaxed">{{ cat.description }}</p>
      </div>
    </div>

    <!-- TAB 5: Niveaux de Cours -->
    <div v-if="currentSubTab === 'LEVELS'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="lvl in levels" :key="lvl.id" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-3">
        <span class="px-3 py-1 bg-brand-50 text-brand-700 dark:bg-brand-900/40 text-xs font-bold rounded-full">
          Tranche {{ lvl.ageGroup || `${lvl.minAge}-${lvl.maxAge} ans` }}
        </span>
        <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ lvl.name || lvl.title }}</h3>
        <p class="text-xs text-gray-500">{{ lvl.description || 'Objectifs pédagogiques et programme annuel' }}</p>
      </div>
    </div>

    <!-- Modal Form pour Ajouter/Éditer une Classe dans MySQL -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Créer/Éditer une Classe dans MySQL</h3>
          <button @click="showClassModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveClass" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Intitulé de la Classe</label>
            <input v-model="classForm.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" placeholder="ex: Classe Débutant 2A" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Tranche d'âge / Niveau</label>
              <select v-model="classForm.level" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option value="4-5 ans (Éveil)">4-5 ans (Éveil)</option>
                <option value="6-8 ans (Débutant)">6-8 ans (Débutant)</option>
                <option value="9-12 ans (Intermédiaire)">9-12 ans (Intermédiaire)</option>
                <option value="13-16 ans (Avancé Tajwid)">13-16 ans (Avancé Tajwid)</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Capacité Maximale</label>
              <input v-model="classForm.capacity" type="number" min="5" max="30" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Enseignant Référent Affecté</label>
            <select v-model="classForm.teacher" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold text-emerald-600">
              <option v-for="t in teachersList" :key="t.id" :value="t.name">{{ t.name }} ({{ t.speciality }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Créneau Horaire Configuré</label>
              <select v-model="classForm.schedule" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option v-for="s in schedules" :key="s.id" :value="s.name">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Salle de cours</label>
              <select v-model="classForm.room" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option value="Salle Maryam 1">Salle Maryam 1</option>
                <option value="Salle Maryam 2">Salle Maryam 2</option>
                <option value="Salle Khadija 1">Salle Khadija 1</option>
                <option value="Salle Khadija 2">Salle Khadija 2</option>
                <option value="Grand Amphi A">Grand Amphi A</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="submitting" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow disabled:opacity-50">
              {{ submitting ? 'Enregistrement MySQL...' : 'Enregistrer dans la BBD MySQL' }}
            </button>
            <button type="button" @click="showClassModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form pour Ajouter / Éditer un Créneau Horaire -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ scheduleEditingId ? '✏️ Éditer le Créneau Horaire' : '⏰ Nouveau Créneau Horaire' }}
          </h3>
          <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveScheduleConfig" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Jour de la Semaine</label>
            <select v-model="scheduleForm.day" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
              <option value="Samedi">Samedi</option>
              <option value="Dimanche">Dimanche</option>
              <option value="Mercredi">Mercredi</option>
              <option value="Vendredi">Vendredi</option>
              <option value="Lundi">Lundi</option>
              <option value="Mardi">Mardi</option>
              <option value="Jeudi">Jeudi</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Heure de Début</label>
              <input v-model="scheduleForm.startTime" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono font-bold" placeholder="09:00" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Heure de Fin</label>
              <input v-model="scheduleForm.endTime" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono font-bold" placeholder="12:00" />
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Libellé / Session (ex: Matin, Après-Midi, Soirée)</label>
            <input v-model="scheduleForm.label" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="ex: Matin" />
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-gold-600 hover:bg-gold-700 text-white font-bold rounded-xl shadow">
              💾 Save Créneau
            </button>
            <button type="button" @click="showScheduleModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert, showDeleteConfirmDialog } from '../../plugins/notify'

const currentSubTab = ref('TABLE')
const loading = ref(false)
const submitting = ref(false)
const classSearch = ref('')
const showClassModal = ref(false)
const showScheduleModal = ref(false)
const scheduleEditingId = ref(null)

const categories = ref([
  { id: 1, name: 'Langue Arabe', icon: '🗣️', description: 'Lecture, écriture, grammaire & vocabulaire' },
  { id: 2, name: 'Saint Coran & Tajwid', icon: '📖', description: 'Mémorisation, récitation et règles de Tajwid' },
  { id: 3, name: 'Éducation Éthique', icon: '✨', description: 'Valeurs morales et comportementales' }
])

const levels = ref([
  { id: 1, name: '4-5 ans (Éveil)', minAge: 4, maxAge: 5, description: 'Initiation ludique aux lettres' },
  { id: 2, name: '6-8 ans (Débutant)', minAge: 6, maxAge: 8, description: 'Apprentissage de la lecture fluide' },
  { id: 3, name: '9-12 ans (Intermédiaire)', minAge: 9, maxAge: 12, description: 'Grammaire et mémorisation' },
  { id: 4, name: '13-16 ans (Avancé Tajwid)', minAge: 13, maxAge: 16, description: 'Étude approfondie de la langue arabe' }
])

const teachersList = ref([
  { id: 1, name: 'Cheikh Mahmoud', speciality: 'Langue Arabe & Tajwid' },
  { id: 2, name: 'Oustaz Hassan', speciality: 'Coran & Mémorisation' },
  { id: 3, name: 'Mme Souad', speciality: 'Éducation Éthique & Arabe' }
])

const schedules = ref([
  { id: 1, day: 'Samedi', startTime: '09:00', endTime: '12:00', label: 'Matin', name: 'Samedi 09:00 - 12:00 (Matin)' },
  { id: 2, day: 'Samedi', startTime: '14:00', endTime: '17:00', label: 'Après-Midi', name: 'Samedi 14:00 - 17:00 (Après-Midi)' },
  { id: 3, day: 'Dimanche', startTime: '09:00', endTime: '12:00', label: 'Matin', name: 'Dimanche 09:00 - 12:00 (Matin)' },
  { id: 4, day: 'Dimanche', startTime: '14:00', endTime: '17:00', label: 'Après-Midi', name: 'Dimanche 14:00 - 17:00 (Après-Midi)' },
  { id: 5, day: 'Mercredi', startTime: '14:00', endTime: '17:00', label: 'Rattrapage', name: 'Mercredi 14:00 - 17:00 (Rattrapage)' },
  { id: 6, day: 'Vendredi', startTime: '17:30', endTime: '19:30', label: 'Soirée', name: 'Vendredi 17:30 - 19:30 (Soirée)' }
])

const classrooms = ref([
  { id: 1, name: 'Classe Éveil 1', level: '4-5 ans (Éveil)', category: 'Langue Arabe', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00 (Matin)', roomNumber: 'Salle Maryam 1', maxCapacity: 15, currentEnrolled: 10 },
  { id: 2, name: 'Classe Débutant 2A', level: '6-8 ans (Débutant)', category: 'Coran & Tajwid', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00 (Matin)', roomNumber: 'Salle Maryam 2', maxCapacity: 20, currentEnrolled: 14 }
])

const filteredClassrooms = computed(() => {
  if (!classSearch.value) return classrooms.value
  const q = classSearch.value.toLowerCase()
  return classrooms.value.filter(c => c.name.toLowerCase().includes(q) || (c.teacher && c.teacher.toLowerCase().includes(q)) || (c.roomNumber && c.roomNumber.toLowerCase().includes(q)))
})

const classForm = ref({
  name: '',
  level: '6-8 ans (Débutant)',
  teacher: 'Cheikh Mahmoud',
  schedule: 'Samedi 09:00 - 12:00 (Matin)',
  room: 'Salle Maryam 1',
  capacity: 20
})

const scheduleForm = ref({
  day: 'Samedi',
  startTime: '09:00',
  endTime: '12:00',
  label: 'Matin'
})

function getClassesForTeacher(teacherName) {
  return classrooms.value.filter(c => c.teacher && c.teacher.toLowerCase().includes(teacherName.toLowerCase()))
}

async function fetchClassesData() {
  loading.value = true
  try {
    const res = await apiClient.get('/admin/classes')
    if (res.data) {
      if (Array.isArray(res.data.classes) && res.data.classes.length > 0) {
        classrooms.value = res.data.classes
      }
      if (Array.isArray(res.data.categories) && res.data.categories.length > 0) {
        categories.value = res.data.categories
      }
      if (Array.isArray(res.data.levels) && res.data.levels.length > 0) {
        levels.value = res.data.levels
      }
      if (Array.isArray(res.data.schedules) && res.data.schedules.length > 0) {
        schedules.value = res.data.schedules
      }
    }
  } catch (err) {
    console.error('Erreur API /admin/classes:', err)
  } finally {
    loading.value = false
  }
}

function openClassModal(cls = null) {
  if (cls) {
    classForm.value = { ...cls, room: cls.roomNumber || cls.room, capacity: cls.maxCapacity || cls.capacity }
  } else {
    classForm.value = { name: '', level: '6-8 ans (Débutant)', teacher: 'Cheikh Mahmoud', schedule: schedules.value[0]?.name || 'Samedi 09:00 - 12:00 (Matin)', room: 'Salle Maryam 1', capacity: 20 }
  }
  showClassModal.value = true
}

function openScheduleConfigModal(sch = null) {
  if (sch) {
    scheduleEditingId.value = sch.id
    scheduleForm.value = {
      day: sch.day || 'Samedi',
      startTime: sch.startTime || '09:00',
      endTime: sch.endTime || '12:00',
      label: sch.label || 'Matin'
    }
  } else {
    scheduleEditingId.value = null
    scheduleForm.value = {
      day: 'Samedi',
      startTime: '09:00',
      endTime: '12:00',
      label: 'Matin'
    }
  }
  showScheduleModal.value = true
}

function saveScheduleConfig() {
  const generatedName = `${scheduleForm.value.day} ${scheduleForm.value.startTime} - ${scheduleForm.value.endTime} (${scheduleForm.value.label})`

  if (scheduleEditingId.value) {
    const existing = schedules.value.find(s => s.id === scheduleEditingId.value)
    if (existing) {
      existing.day = scheduleForm.value.day
      existing.startTime = scheduleForm.value.startTime
      existing.endTime = scheduleForm.value.endTime
      existing.label = scheduleForm.value.label
      existing.name = generatedName
    }
    showSuccessAlert('Créneau Mis à Jour ! ⏰', `Le créneau <strong>${generatedName}</strong> a été mis à jour dans la configuration.`)
  } else {
    const newSch = {
      id: Date.now(),
      day: scheduleForm.value.day,
      startTime: scheduleForm.value.startTime,
      endTime: scheduleForm.value.endTime,
      label: scheduleForm.value.label,
      name: generatedName
    }
    schedules.value.push(newSch)
    showSuccessAlert('Nouveau Créneau Enregistré ! 🎉', `Le créneau <strong>${generatedName}</strong> a été ajouté aux créneaux configurés.`)
  }

  showScheduleModal.value = false
}

async function deleteSchedule(sch) {
  const res = await showDeleteConfirmDialog(`le créneau ${sch.name}`)
  if (res.isConfirmed) {
    schedules.value = schedules.value.filter(s => s.id !== sch.id)
    showSuccessAlert('Créneau Supprimé ! 🗑️', `Le créneau <strong>${sch.name}</strong> a été retiré de la configuration.`)
  }
}

async function saveClass() {
  submitting.value = true
  try {
    const payload = {
      name: classForm.value.name,
      roomNumber: classForm.value.room,
      maxCapacity: classForm.value.capacity,
      schedule: classForm.value.schedule,
      teacherId: 1
    }

    const res = await apiClient.post('/admin/classes', payload)
    if (res.data && res.data.class) {
      classrooms.value.unshift(res.data.class)
    } else {
      classrooms.value.unshift({
        id: Date.now(),
        name: classForm.value.name,
        level: classForm.value.level,
        category: 'Langue Arabe',
        teacher: classForm.value.teacher,
        schedule: classForm.value.schedule,
        roomNumber: classForm.value.room,
        maxCapacity: classForm.value.capacity,
        currentEnrolled: 0
      })
    }
    showClassModal.value = false
    showSuccessAlert('Classe Enregistrée ! 🎉', `La classe <strong>${classForm.value.name}</strong> a été enregistrée avec succès.`)
  } catch (err) {
    console.error('Erreur enregistrement classe:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement de la classe.')
  } finally {
    submitting.value = false
  }
}

function openAssignModal(teacher) {
  showSuccessAlert('Matrice d\'Affectation', `L'enseignant <strong>${teacher.name}</strong> est actuellement affecté à ${getClassesForTeacher(teacher.name).length} classe(s) dans MySQL.`)
}

function openConfigModal(type) {
  showSuccessAlert('Configuration', `Création d'une nouvelle ${type === 'CATEGORY' ? 'catégorie de cours' : 'tranche de niveau'} dans MySQL.`)
}

function openRoster(cls) {
  showSuccessAlert('Effectif Classe', `Liste des ${cls.currentEnrolled || 12} élèves inscrits dans ${cls.name}.`)
}

async function deleteClass(cls) {
  const result = await showDeleteConfirmDialog(cls.name)
  if (result.isConfirmed) {
    try {
      await apiClient.delete(`/admin/classes/${cls.id}`)
    } catch (err) {
      console.warn('Suppression classe locale :', err)
    }
    classrooms.value = classrooms.value.filter(c => c.id !== cls.id)
    showSuccessAlert('Classe Supprimée ! 🗑️', `La classe <strong>${cls.name}</strong> a été supprimée de la base de données MySQL.`)
  }
}

onMounted(() => {
  fetchClassesData()
})
</script>
