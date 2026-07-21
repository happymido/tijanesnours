<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Élèves, Parents & Enseignants</h1>
        <p class="text-xs text-gray-500">Ajout, modification, rattachement automatique parent-enfant et affectation aux classes</p>
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

    <!-- Tabs Filter & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
      <div class="flex text-xs font-bold gap-6">
        <button @click="activeTab = 'ALL'" :class="activeTab === 'ALL' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Tous ({{ usersList.length }})</button>
        <button @click="activeTab = 'STUDENTS'" :class="activeTab === 'STUDENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Élèves ({{ students.length }})</button>
        <button @click="activeTab = 'TEACHERS'" :class="activeTab === 'TEACHERS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Enseignants ({{ teachers.length }})</button>
        <button @click="activeTab = 'PARENTS'" :class="activeTab === 'PARENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">Parents ({{ parents.length }})</button>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Rechercher par nom, email ou classe..."
        class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs w-72"
      />
    </div>

    <!-- Datatable -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Nom & Prénom</th>
              <th class="py-3 px-4">Rôle</th>
              <th class="py-3 px-4">Classe / Spécialité</th>
              <th class="py-3 px-4">Parent Rattaché / Contact</th>
              <th class="py-3 px-4">Statut</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
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
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Actif</span>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button @click="editUser(user)" class="text-brand-600 font-bold hover:underline">Éditer</button>
                <button @click="deleteUser(user.id)" class="text-red-600 font-bold hover:underline">Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Ajout / Édition avec Création Automatique du Parent) -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
              {{ modalType === 'STUDENT' ? 'Ajouter un Élève' : 'Ajouter un Enseignant' }}
            </h3>
            <p v-if="modalType === 'STUDENT'" class="text-xs text-emerald-600 font-semibold mt-0.5">
              ⚡ Création et rattachement automatique du compte Parent
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

          <!-- Section Responsable Légal / Parent (Création Automatique) -->
          <div v-if="modalType === 'STUDENT'" class="space-y-3 pt-2">
            <h4 class="font-bold text-brand-600 dark:text-gold-400 border-b pb-1 flex items-center justify-between">
              <span>👨‍👩‍👧 2. Responsable Légal (Création Automatique)</span>
            </h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold mb-1">Nom & Prénom du Parent</label>
                <input v-model="form.parentFullName" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="ex: Karim Benali" />
              </div>
              <div>
                <label class="block font-semibold mb-1">Email du Parent (Identifiant)</label>
                <input v-model="form.parentEmail" type="email" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="karim.benali@email.lu" />
              </div>
            </div>
            <div>
              <label class="block font-semibold mb-1">Téléphone Joignable du Parent</label>
              <input v-model="form.parentPhone" type="text" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="+352 691 123 456" />
            </div>
          </div>

          <div v-if="modalType === 'TEACHER'" class="space-y-3">
            <div>
              <label class="block font-semibold mb-1">Spécialité Enseignée</label>
              <select v-model="form.assignedGroup" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option value="Langue Arabe & Tajwid">Langue Arabe & Tajwid</option>
                <option value="Coran & Mémorisation">Coran & Mémorisation</option>
                <option value="Éducation Éthique">Éducation Éthique</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Téléphone de contact</label>
              <input v-model="form.parentPhone" type="text" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="+352 691 999 888" />
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow">
              {{ modalType === 'STUDENT' ? 'Créer Élève + Créer Compte Parent' : 'Créer Enseignant' }}
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
import { ref, computed } from 'vue'

const activeTab = ref('ALL')
const searchQuery = ref('')
const showModal = ref(false)
const modalType = ref('STUDENT')

const form = ref({
  studentFirstName: '',
  studentLastName: '',
  studentEmail: '',
  assignedGroup: 'Classe Débutant 2A (6-8 ans)',
  parentFullName: '',
  parentEmail: '',
  parentPhone: ''
})

const usersList = ref([
  { id: 1, name: 'Youssef Benali', email: 'youssef@student.lu', role: 'ROLE_STUDENT', assignedGroup: 'Classe Débutant 2A (6-8 ans)', parentName: 'Karim Benali', contactInfo: '+352 691 123 456' },
  { id: 2, name: 'Maryam El Amrani', email: 'maryam@student.lu', role: 'ROLE_STUDENT', assignedGroup: 'Classe Éveil 1 (4-5 ans)', parentName: 'Fatima El Amrani', contactInfo: '+352 691 222 333' },
  { id: 3, name: 'Adam Mansouri', email: 'adam@student.lu', role: 'ROLE_STUDENT', assignedGroup: 'Classe Intermédiaire 1 (9-12 ans)', parentName: 'Tariq Mansouri', contactInfo: '+352 691 444 555' },
  { id: 4, name: 'Cheikh Mahmoud', email: 'mahmoud@tijanesnours.lu', role: 'ROLE_TEACHER', assignedGroup: 'Langue Arabe & Tajwid', parentName: null, contactInfo: '+352 691 888 999' },
  { id: 5, name: 'Oustaz Hassan', email: 'hassan@tijanesnours.lu', role: 'ROLE_TEACHER', assignedGroup: 'Coran & Mémorisation', parentName: null, contactInfo: '+352 691 777 666' },
  { id: 6, name: 'Karim Benali', email: 'karim.benali@email.lu', role: 'ROLE_PARENT', assignedGroup: 'Enfants: Youssef', parentName: null, contactInfo: '+352 691 123 456' },
  { id: 7, name: 'Fatima El Amrani', email: 'fatima@email.lu', role: 'ROLE_PARENT', assignedGroup: 'Enfants: Maryam', parentName: null, contactInfo: '+352 691 222 333' },
  { id: 8, name: 'Admin Général', email: 'admin@tijanesnours.lu', role: 'ROLE_ADMIN', assignedGroup: 'Administration', parentName: null, contactInfo: '-' },
  { id: 9, name: 'Agent Comptable', email: 'comptable@tijanesnours.lu', role: 'ROLE_PAYMENT_AGENT', assignedGroup: 'Finances', parentName: null, contactInfo: '-' }
])

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

function saveUser() {
  if (modalType.value === 'STUDENT') {
    const studentFullName = `${form.value.studentFirstName} ${form.value.studentLastName}`
    const parentFullName = form.value.parentFullName
    const parentEmail = form.value.parentEmail
    const parentPhone = form.value.parentPhone

    // 1. Vérifier si le parent existe déjà, sinon le créer automatiquement
    let existingParent = usersList.value.find(u => u.role === 'ROLE_PARENT' && u.email.toLowerCase() === parentEmail.toLowerCase())
    
    if (!existingParent) {
      existingParent = {
        id: Date.now() + 1,
        name: parentFullName,
        email: parentEmail,
        role: 'ROLE_PARENT',
        assignedGroup: `Enfants: ${form.value.studentFirstName}`,
        parentName: null,
        contactInfo: parentPhone
      }
      // Insertion automatique du compte Parent dans la BBD
      usersList.value.unshift(existingParent)
    } else {
      existingParent.assignedGroup += `, ${form.value.studentFirstName}`
    }

    // 2. Créer le compte Élève et le rattacher automatiquement au Parent
    const newStudent = {
      id: Date.now(),
      name: studentFullName,
      email: form.value.studentEmail || `${form.value.studentFirstName.toLowerCase()}@student.lu`,
      role: 'ROLE_STUDENT',
      assignedGroup: form.value.assignedGroup,
      parentName: existingParent.name,
      contactInfo: parentPhone
    }

    usersList.value.unshift(newStudent)

    showModal.value = false
    alert(`✅ L'élève ${newStudent.name} et le compte Parent ${existingParent.name} (${existingParent.email}) ont été créés et rattachés automatiquement !`)

  } else {
    // Création d'un enseignant
    const newTeacher = {
      id: Date.now(),
      name: `${form.value.studentFirstName} ${form.value.studentLastName}`,
      email: form.value.studentEmail,
      role: 'ROLE_TEACHER',
      assignedGroup: form.value.assignedGroup,
      parentName: null,
      contactInfo: form.value.parentPhone
    }
    usersList.value.unshift(newTeacher)
    showModal.value = false
    alert(`Enseignant ${newTeacher.name} créé avec succès !`)
  }
}

function editUser(user) {
  alert(`Édition de la fiche de ${user.name}`)
}

function deleteUser(id) {
  if (confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
    usersList.value = usersList.value.filter(u => u.id !== id)
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
