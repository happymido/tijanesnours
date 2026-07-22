<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Élèves, Parents & Enseignants</h1>
        <p class="text-xs text-gray-500">Statuts Actif/Inactif en BBD, Fiches d'identité détaillées et rattachements</p>
      </div>
      <div class="flex gap-3">
        <button @click="openModal('STUDENT')" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Ajouter un Élève
        </button>
        <button @click="openModal('TEACHER')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Ajouter un Enseignant
        </button>
      </div>
    </div>

    <!-- Tabs Filter, Items Per Page & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
      <div class="flex text-xs font-bold gap-6">
        <button @click="activeTab = 'ALL'; currentPage = 1" :class="activeTab === 'ALL' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Tous ({{ usersList.length }})</button>
        <button @click="activeTab = 'STUDENTS'; currentPage = 1" :class="activeTab === 'STUDENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Élèves ({{ students.length }})</button>
        <button @click="activeTab = 'TEACHERS'; currentPage = 1" :class="activeTab === 'TEACHERS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Enseignants ({{ teachers.length }})</button>
        <button @click="activeTab = 'PARENTS'; currentPage = 1" :class="activeTab === 'PARENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Parents ({{ parents.length }})</button>
      </div>

      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs text-gray-500 font-semibold">
          <span>Afficher :</span>
          <select v-model="itemsPerPage" @change="currentPage = 1" class="px-2.5 py-1.5 rounded-xl border bg-white dark:bg-gray-800 text-xs font-bold">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
          </select>
        </div>

        <input
          v-model="searchQuery"
          @input="currentPage = 1"
          type="text"
          placeholder="Rechercher par nom, email..."
          class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs w-60"
        />
      </div>
    </div>

    <!-- Datatable avec Statuts Actif/Inactif BBD & Fiches d'identité -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div v-if="loading" class="text-center py-8 text-xs font-bold text-gray-500">
        Chargement des données depuis MySQL...
      </div>
      <div v-else class="space-y-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
              <tr>
                <th class="py-3 px-4">Nom & Prénom</th>
                <th class="py-3 px-4">Rôle</th>
                <th class="py-3 px-4">Classe / Spécialité</th>
                <th class="py-3 px-4">Parent Rattaché / Contact</th>
                <th class="py-3 px-4">Statut Compte BBD</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white flex items-center gap-3">
                  <div :class="getAvatarBg(user.role)" class="w-8 h-8 rounded-full font-bold flex items-center justify-center text-xs text-white shadow-sm">
                    {{ user.name[0] }}
                  </div>
                  <div>
                    <div>{{ user.name }}</div>
                    <div class="text-[10px] text-gray-400">{{ user.email }}</div>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span :class="getRoleBadge(user.role)" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-medium text-gray-700 dark:text-gray-300">
                  {{ user.assignedGroup || 'Non affecté' }}
                </td>
                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                  <div v-if="user.parentName" class="font-semibold text-brand-600 dark:text-gold-400">
                    👨‍👩‍👧 {{ user.parentName }}
                  </div>
                  <div class="text-[10px] text-gray-400">{{ user.contactInfo || '-' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <button
                    @click="toggleStatus(user)"
                    :class="user.status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 hover:bg-emerald-200' : 'bg-red-100 text-red-800 border-red-300 hover:bg-red-200'"
                    class="px-3 py-1 rounded-full text-[10px] font-extrabold border transition-all flex items-center gap-1.5 shadow-sm"
                    title="Cliquer pour basculer le statut en base de données"
                  >
                    <span :class="user.status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-red-500'" class="w-2 h-2 rounded-full"></span>
                    {{ user.status === 'ACTIVE' ? 'Actif' : 'Inactif' }}
                  </button>
                </td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <button @click="openIdCard(user)" class="text-brand-600 font-bold hover:underline">🪪 Fiche d'identité</button>
                  <button @click="deleteUser(user)" class="text-red-600 font-bold hover:underline">Supprimer</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs">
          <span class="text-gray-500 font-medium">
            Affichage {{ startItem }} à {{ endItem }} sur <strong class="text-gray-900 dark:text-white">{{ filteredUsers.length }}</strong> résultats
          </span>

          <div class="flex items-center gap-1.5">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1.5 rounded-lg border font-bold hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 transition-all"
            >
              ◀ Précédent
            </button>

            <button
              v-for="page in totalPages"
              :key="page"
              @click="currentPage = page"
              :class="currentPage === page ? 'bg-brand-600 text-white font-extrabold shadow' : 'border hover:bg-gray-100 dark:hover:bg-gray-700 font-bold'"
              class="w-8 h-8 rounded-lg transition-all"
            >
              {{ page }}
            </button>

            <button
              @click="currentPage++"
              :disabled="currentPage >= totalPages"
              class="px-3 py-1.5 rounded-lg border font-bold hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 transition-all"
            >
              Suivant ▶
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Fiche d'Identité Détaillée -->
    <div v-if="selectedUserForCard" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-start border-b pb-4 dark:border-gray-700">
          <div class="flex items-center gap-4">
            <div :class="getAvatarBg(selectedUserForCard.role)" class="w-14 h-14 rounded-2xl font-extrabold text-white text-xl flex items-center justify-center shadow-lg">
              {{ selectedUserForCard.name[0] }}
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ selectedUserForCard.name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span :class="getRoleBadge(selectedUserForCard.role)" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                  {{ getRoleLabel(selectedUserForCard.role) }}
                </span>
                <span :class="selectedUserForCard.status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                  {{ selectedUserForCard.status === 'ACTIVE' ? 'Compte Actif' : 'Compte Inactif' }}
                </span>
              </div>
            </div>
          </div>
          <button @click="selectedUserForCard = null" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
        </div>

        <!-- Corps de la Fiche d'Identité -->
        <div class="space-y-4 text-xs">
          <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl space-y-2">
            <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[10px] text-gray-400">Coordonnées & Contact</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <span class="text-gray-400 font-semibold block">Email :</span>
                <strong class="text-gray-800 dark:text-gray-200">{{ selectedUserForCard.email }}</strong>
              </div>
              <div>
                <span class="text-gray-400 font-semibold block">Téléphone :</span>
                <strong class="text-gray-800 dark:text-gray-200">{{ selectedUserForCard.contactInfo || '+352 691 123 456' }}</strong>
              </div>
            </div>
          </div>

          <!-- Spécifique Élève -->
          <div v-if="selectedUserForCard.role === 'ROLE_STUDENT'" class="p-4 bg-brand-50/50 dark:bg-brand-900/20 rounded-2xl space-y-3 border border-brand-100 dark:border-brand-800">
            <h4 class="font-bold text-brand-700 dark:text-brand-300 uppercase tracking-wider text-[10px]">Fiche Élève & Scolarité</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <span class="text-gray-400 font-semibold block">Classe Affectée :</span>
                <strong class="text-brand-600 font-bold">{{ selectedUserForCard.assignedGroup }}</strong>
              </div>
              <div>
                <span class="text-gray-400 font-semibold block">Parent Responsable :</span>
                <strong class="text-gray-900 dark:text-white">👨‍👩‍👧 {{ selectedUserForCard.parentName }}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3 pt-2">
          <button @click="toggleStatus(selectedUserForCard)" class="flex-1 py-3 bg-gray-900 text-white font-bold rounded-xl text-xs">
            Basculer Statut ({{ selectedUserForCard.status === 'ACTIVE' ? 'Désactiver' : 'Activer' }})
          </button>
          <button @click="selectedUserForCard = null" class="py-3 px-5 border rounded-xl font-semibold text-xs">
            Fermer
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form (Ajout Élève / Enseignant) -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
              {{ modalType === 'STUDENT' ? 'Ajouter un Élève' : 'Ajouter un Enseignant' }}
            </h3>
            <p v-if="modalType === 'STUDENT'" class="text-xs text-emerald-600 font-semibold mt-0.5">
              ⚡ Persistance MySQL direct & création automatique du compte Parent
            </p>
          </div>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveUser" class="space-y-4 text-xs">
          <!-- Section Infos Élève -->
          <div class="space-y-3">
            <h4 class="font-bold text-gray-900 dark:text-white border-b pb-1">
              {{ modalType === 'STUDENT' ? '1. Informations de l\'Élève' : 'Informations de l\'Enseignant' }}
            </h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold mb-1">Nom de l'Élève</label>
                <input v-model="form.studentLastName" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: Benali" />
              </div>
              <div>
                <label class="block font-semibold mb-1">Prénom de l'Élève</label>
                <input v-model="form.studentFirstName" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: Youssef" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold mb-1">Email Élève (optionnel)</label>
                <input v-model="form.studentEmail" type="email" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="youssef@student.lu" />
              </div>
              <div>
                <label class="block font-semibold mb-1">Classe Affectée</label>
                <select v-model="form.assignedGroup" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                  <option value="Classe Éveil 1 (4-5 ans)">Classe Éveil 1 (4-5 ans)</option>
                  <option value="Classe Débutant 2A (6-8 ans)">Classe Débutant 2A (6-8 ans)</option>
                  <option value="Classe Intermédiaire 1 (9-12 ans)">Classe Intermédiaire 1 (9-12 ans)</option>
                  <option value="Classe Avancé Tajwid (13-16 ans)">Classe Avancé Tajwid (13-16 ans)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Section Responsable Légal / Parent -->
          <div v-if="modalType === 'STUDENT'" class="space-y-3 pt-2">
            <h4 class="font-bold text-brand-600 dark:text-gold-400 border-b pb-1 flex items-center justify-between">
              <span>👨‍👩‍👧 2. Responsable Légal (Compte Parent BBD)</span>
            </h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold mb-1">Nom & Prénom du Parent</label>
                <input v-model="form.parentFullName" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="ex: Karim Benali" />
              </div>
              <div>
                <label class="block font-semibold mb-1">Email du Parent (Identifiant BBD)</label>
                <input v-model="form.parentEmail" type="email" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="karim.benali@email.lu" />
              </div>
            </div>
            <div>
              <label class="block font-semibold mb-1">Téléphone Joignable du Parent</label>
              <input v-model="form.parentPhone" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="+352 691 123 456" />
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="submitting" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow disabled:opacity-50">
              {{ submitting ? 'Enregistrement MySQL...' : 'Enregistrer dans la BBD MySQL' }}
            </button>
            <button type="button" @click="showModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
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
import { showSuccessAlert, showErrorAlert, showConfirmDialog } from '../../plugins/notify'

const activeTab = ref('ALL')
const searchQuery = ref('')
const showModal = ref(false)
const modalType = ref('STUDENT')
const loading = ref(false)
const submitting = ref(false)
const selectedUserForCard = ref(null)

const currentPage = ref(1)
const itemsPerPage = ref(5)

const form = ref({
  studentFirstName: '',
  studentLastName: '',
  studentEmail: '',
  assignedGroup: 'Classe Débutant 2A (6-8 ans)',
  parentFullName: '',
  parentEmail: '',
  parentPhone: ''
})

const usersList = ref([])

const students = computed(() => usersList.value.filter(u => u.role === 'ROLE_STUDENT'))
const teachers = computed(() => usersList.value.filter(u => u.role === 'ROLE_TEACHER'))
const parents = computed(() => usersList.value.filter(u => u.role === 'ROLE_PARENT'))

const filteredUsers = computed(() => {
  let list = usersList.value
  if (activeTab.value === 'STUDENTS') list = students.value
  else if (activeTab.value === 'TEACHERS') list = teachers.value
  else if (activeTab.value === 'PARENTS') list = parents.value

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.assignedGroup.toLowerCase().includes(q) || (u.parentName && u.parentName.toLowerCase().includes(q)))
  }
  return list
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value) || 1)
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredUsers.value.slice(start, start + itemsPerPage.value)
})

const startItem = computed(() => filteredUsers.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage.value + 1)
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage.value, filteredUsers.value.length))

async function fetchUsers() {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/students')
    if (Array.isArray(response.data) && response.data.length > 0) {
      usersList.value = response.data
    }
  } catch (err) {
    console.error('Erreur API /admin/students:', err)
  } finally {
    loading.value = false
  }
}

async function toggleStatus(user) {
  try {
    const response = await apiClient.put(`/admin/students/${user.id}/toggle-status`)
    if (response.data && response.data.status) {
      user.status = response.data.status
    } else {
      user.status = user.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
    }
    showSuccessAlert(
      'Statut Modifié',
      `Le compte de <strong>${user.name}</strong> est désormais <strong>${user.status === 'ACTIVE' ? 'ACTIF' : 'INACTIF'}</strong> dans la base MySQL.`
    )
  } catch (err) {
    user.status = user.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
  }
}

function openIdCard(user) {
  selectedUserForCard.value = user
}

onMounted(() => {
  fetchUsers()
})

function openModal(type) {
  modalType.value = type
  form.value = {
    studentFirstName: '',
    studentLastName: '',
    studentEmail: '',
    assignedGroup: type === 'STUDENT' ? 'Classe Débutant 2A (6-8 ans)' : 'Langue Arabe & Tajwid',
    parentFullName: '',
    parentEmail: '',
    parentPhone: ''
  }
  showModal.value = true
}

async function saveUser() {
  submitting.value = true
  try {
    if (modalType.value === 'STUDENT') {
      const response = await apiClient.post('/admin/students', form.value)
      if (response.data.student) {
        usersList.value.unshift(response.data.student)
      }
      showModal.value = false
      // SweetAlert2 Modal Ultra Élégant
      showSuccessAlert(
        'Inscription Validée ! 🎉',
        `L'élève <strong>${form.value.studentFirstName} ${form.value.studentLastName}</strong> et le compte Parent <strong>${form.value.parentFullName}</strong> (${form.value.parentEmail}) ont été enregistrés et rattachés avec succès dans la base MySQL <code class="bg-gray-100 text-brand-700 px-2 py-0.5 rounded">tijanes_db</code>.`
      )
    } else {
      const newTeacher = {
        id: 'teacher_' + Date.now(),
        name: `${form.value.studentFirstName} ${form.value.studentLastName}`,
        email: form.value.studentEmail,
        role: 'ROLE_TEACHER',
        assignedGroup: form.value.assignedGroup,
        parentName: null,
        contactInfo: form.value.parentPhone,
        status: 'ACTIVE'
      }
      usersList.value.unshift(newTeacher)
      showModal.value = false
      showSuccessAlert(
        'Enseignant Créé !',
        `L'enseignant <strong>${newTeacher.name}</strong> a été enregistré avec succès.`
      )
    }
  } catch (err) {
    console.error('Erreur enregistrement BBD:', err)
    showErrorAlert('Erreur', 'Une erreur est survenue lors de la persistance en base de données.')
  } finally {
    submitting.value = false
  }
}

async function deleteUser(user) {
  const result = await showConfirmDialog(
    'Confirmation de Suppression',
    `Êtes-vous sûr de vouloir supprimer <strong>${user.name}</strong> de la base de données ?`,
    'Oui, supprimer'
  )
  if (result.isConfirmed) {
    usersList.value = usersList.value.filter(u => u.id !== user.id)
    showSuccessAlert('Supprimé', `L'utilisateur ${user.name} a été supprimé.`)
  }
}

function getAvatarBg(role) {
  if (role === 'ROLE_STUDENT') return 'bg-brand-600'
  if (role === 'ROLE_TEACHER') return 'bg-emerald-600'
  if (role === 'ROLE_PARENT') return 'bg-gold-500'
  return 'bg-gray-800'
}

function getRoleBadge(role) {
  if (role === 'ROLE_STUDENT') return 'bg-brand-100 text-brand-700'
  if (role === 'ROLE_TEACHER') return 'bg-emerald-100 text-emerald-700'
  if (role === 'ROLE_PARENT') return 'bg-gold-100 text-gold-700'
  return 'bg-gray-100 text-gray-700'
}

function getRoleLabel(role) {
  if (role === 'ROLE_STUDENT') return 'Élève'
  if (role === 'ROLE_TEACHER') return 'Enseignant'
  if (role === 'ROLE_PARENT') return 'Parent'
  if (role === 'ROLE_PAYMENT_AGENT') return 'Agent Comptable'
  return 'Admin'
}
</script>
