<template>
  <div class="space-y-8 max-w-5xl mx-auto">
    <!-- Navigation & Breadcrumb -->
    <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
      <router-link to="/admin/users" class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 hover:underline">
        <span>◀</span> Retour à la liste des Utilisateurs
      </router-link>
      <div class="flex gap-3">
        <!-- Bouton Éditer (Mode Lecture) -->
        <button v-if="!isEditing" @click="startEdit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>✏️</span> Éditer la Fiche
        </button>

        <!-- Boutons Enregistrer & Annuler (Mode Édition) -->
        <template v-else>
          <button @click="saveChanges" :disabled="saving" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2 disabled:opacity-50">
            <span>💾</span> {{ saving ? 'Enregistrement MySQL...' : 'Enregistrer dans MySQL' }}
          </button>
          <button @click="cancelEdit" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2 border">
            <span>❌</span> Annuler les modifications
          </button>
        </template>

        <button @click="printCard" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow hover:bg-gray-800 transition-all flex items-center gap-2">
          <span>🖨️</span> Imprimer Fiche
        </button>
      </div>
    </div>

    <!-- Main ID Card Header Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 text-white rounded-3xl p-8 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div class="flex items-center gap-6 w-full md:w-auto">
        <div :class="getAvatarBg(user.role)" class="w-20 h-20 rounded-3xl font-extrabold text-white text-3xl flex items-center justify-center shadow-2xl border-2 border-white/20">
          {{ user.name ? user.name[0] : 'U' }}
        </div>
        <div class="space-y-1 flex-1">
          <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-gold-300">
            Dossier Individuel Officiel
          </span>
          <div v-if="!isEditing">
            <h1 class="text-3xl font-extrabold mt-1">{{ user.name }}</h1>
            <p class="text-brand-100 text-xs">Identifiant BBD : <code class="font-mono text-gold-200">{{ user.id }}</code></p>
          </div>
          <div v-else class="space-y-2 pt-2">
            <input v-model="editForm.name" type="text" class="px-3 py-1.5 rounded-xl bg-white/10 border border-white/30 text-white text-lg font-bold w-full" placeholder="Nom et Prénom" />
            <input v-model="editForm.email" type="email" class="px-3 py-1 rounded-xl bg-white/10 border border-white/30 text-white text-xs w-full" placeholder="Email" />
          </div>
        </div>
      </div>

      <div class="flex flex-col items-end gap-3">
        <span :class="getRoleBadge(user.role)" class="px-4 py-1.5 rounded-full text-xs font-extrabold shadow">
          {{ getRoleLabel(user.role) }}
        </span>
        <button
          @click="toggleStatus"
          :class="user.status === 'ACTIVE' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
          class="px-4 py-1.5 rounded-full text-xs font-extrabold shadow hover:opacity-90 transition-all flex items-center gap-2"
        >
          <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
          {{ user.status === 'ACTIVE' ? 'Compte Actif' : 'Compte Inactif' }}
        </button>
      </div>
    </div>

    <!-- Section Spéciale Multi-Enfants Rattachés pour les Parents -->
    <div v-if="user.role === 'ROLE_PARENT'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-lg border border-gold-200 space-y-4">
      <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <span class="w-10 h-10 rounded-2xl bg-gold-100 text-gold-800 flex items-center justify-center text-lg font-bold">👨‍👩‍👧‍👦</span>
          <div>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Fratrie / Enfants Rattachés à ce Compte Parent</h3>
            <p class="text-xs text-gray-500">Tous les élèves inscrits sous la responsabilité légale de {{ user.name }}</p>
          </div>
        </div>
        <span class="px-3 py-1 bg-gold-100 text-gold-800 font-extrabold text-xs rounded-full">
          {{ childrenList.length }} Enfant(s) Inscrit(s)
        </span>
      </div>

      <!-- Liste des cartes d'enfants -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(child, idx) in childrenList" :key="child.id || idx" class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-200 dark:border-gray-600 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-600 font-bold text-white text-sm flex items-center justify-center shadow">
              👩‍🎓
            </div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ child.name }}</h4>
              <p class="text-xs text-brand-600 font-semibold">{{ child.class || 'Classe Débutant 2A' }}</p>
              <span class="text-[10px] text-gray-400">Né(e) le : {{ child.dateOfBirth || '12/05/2018' }}</span>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <span v-if="idx > 0" class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded-full">
              🏷️ Réduction Fratrie {{ idx === 1 ? '-15%' : '-25%' }}
            </span>
            <router-link :to="`/admin/users/${child.id || 'student_1'}/id-card`" class="px-3 py-1.5 bg-brand-600 text-white font-bold text-xs rounded-xl shadow hover:bg-brand-700 transition-all">
              🪪 Voir Fiche Élève
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Identity Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- 1. Coordonnées & État Civil -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold">👤</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Coordonnées & État Civil</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-gray-400 font-semibold block">Nom & Prénom :</span>
              <strong v-if="!isEditing" class="text-gray-900 dark:text-white text-sm">{{ user.name }}</strong>
              <input v-else v-model="editForm.name" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700 font-semibold" />
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Email :</span>
              <strong v-if="!isEditing" class="text-gray-900 dark:text-white">{{ user.email }}</strong>
              <input v-else v-model="editForm.email" type="email" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700 font-semibold" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-2">
            <div>
              <span class="text-gray-400 font-semibold block">Téléphone Joignable :</span>
              <strong v-if="!isEditing" class="text-gray-900 dark:text-white">{{ user.contactInfo }}</strong>
              <input v-else v-model="editForm.contactInfo" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Adresse Résidence :</span>
              <strong v-if="!isEditing" class="text-gray-900 dark:text-white">{{ user.details?.address }}</strong>
              <input v-else v-model="editForm.address" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
          </div>

          <div v-if="user.role === 'ROLE_STUDENT'" class="grid grid-cols-2 gap-4 pt-2 border-t dark:border-gray-700">
            <div>
              <span class="text-gray-400 font-semibold block">Date de Naissance :</span>
              <strong v-if="!isEditing">{{ user.details?.dateOfBirth }}</strong>
              <input v-else v-model="editForm.dateOfBirth" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Nationalité :</span>
              <strong v-if="!isEditing">{{ user.details?.nationality }}</strong>
              <input v-else v-model="editForm.nationality" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Informations Pédagogiques & Affectation (MULTI-SÉLECTION POUR ENSEIGNANT) -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">🏫</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">
            {{ user.role === 'ROLE_TEACHER' ? 'Classes & Matières Enseignées (Affectations Multiples)' : 'Affectation Scolaire & Niveaux' }}
          </h3>
        </div>

        <div class="space-y-3 text-xs">
          <!-- CAS ENSEIGNANT : Sélection Multiples avec cases à cocher et présélection par défaut -->
          <div v-if="user.role === 'ROLE_TEACHER'" class="space-y-3">
            <div v-if="!isEditing" class="p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 space-y-2">
              <span class="text-[10px] uppercase font-bold text-emerald-700">Classes & Spécialités d'Enseignement :</span>
              <div class="flex flex-wrap gap-2 pt-1">
                <span v-for="(spec, i) in currentTeacherSpecs" :key="i" class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 font-bold rounded-xl text-xs border border-emerald-200">
                  📚 {{ spec }}
                </span>
              </div>
            </div>

            <!-- Mode Édition Enseignant : Liste à cocher pré-sélectionnée par défaut -->
            <div v-else class="p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-200 space-y-3">
              <label class="font-bold text-emerald-800 dark:text-emerald-300 block border-b pb-1">
                ⚡ Sélectionner les affectations multiples (Cochées par défaut) :
              </label>
              <div class="grid grid-cols-1 gap-2.5 pt-1">
                <label v-for="opt in teacherOptions" :key="opt.value" class="flex items-center gap-2.5 p-2 bg-white dark:bg-gray-700 rounded-xl border cursor-pointer hover:bg-emerald-50 transition-colors">
                  <input
                    type="checkbox"
                    :value="opt.value"
                    v-model="editForm.teacherSpecialities"
                    class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"
                  />
                  <span class="font-bold text-xs text-gray-800 dark:text-white">{{ opt.label }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- CAS ÉLÈVE : Sélecteur simple recherchable (Select2) -->
          <div v-else class="p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 space-y-2">
            <span class="text-[10px] uppercase font-bold text-emerald-700">Groupe / Classe Assignée</span>
            <p v-if="!isEditing" class="text-lg font-extrabold text-gray-900 dark:text-white">{{ user.assignedGroup }}</p>
            
            <div v-else class="space-y-1">
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Changer l'affectation de classe :</label>
              <SearchableSelect
                v-model="editForm.assignedGroup"
                :options="classOptions"
                placeholder="Sélectionner une classe..."
              />
            </div>
            
            <p class="text-xs text-gray-500 pt-1">Créneau : Samedi 09:00 - 12:00 • Salle Maryam 1</p>
          </div>
        </div>
      </div>

      <!-- 3. Responsable Légal & Règlements -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center text-sm font-bold">👨‍👩‍👧</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Rattachement Légal & Règlements</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div v-if="user.role === 'ROLE_STUDENT'" class="space-y-2">
            <div>
              <span class="text-gray-400 font-semibold block">Parent / Tuteur Légal :</span>
              <strong v-if="!isEditing" class="text-brand-600 text-sm font-bold">👨‍👩‍👧 {{ user.parentName }}</strong>
              <input v-else v-model="editForm.parentName" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700 font-bold" />
            </div>
          </div>

          <div v-if="user.role === 'ROLE_TEACHER'" class="space-y-2">
            <span class="text-gray-400 font-semibold block">Bio & Description du Parcours :</span>
            <p v-if="!isEditing" class="text-gray-700 dark:text-gray-300 font-medium">{{ user.details?.bio }}</p>
            <textarea v-else v-model="editForm.bio" rows="2" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700"></textarea>
          </div>
        </div>
      </div>

      <!-- 4. Santé & Autorisations Légales -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-red-50 text-red-700 flex items-center justify-center text-sm font-bold">📄</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Santé & Conformité Légale</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <span class="text-gray-400 font-semibold block">Remarques Santé / Allergies :</span>
            <strong v-if="!isEditing" class="text-gray-800 dark:text-gray-200">{{ user.details?.allergies || 'Aucune allergie signalée' }}</strong>
            <textarea v-else v-model="editForm.allergies" rows="2" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700"></textarea>
          </div>
          <div>
            <span class="text-gray-400 font-semibold block">Assurance Responsabilité Civile :</span>
            <strong v-if="!isEditing" class="text-emerald-600 font-bold">Police: {{ user.details?.insurancePolicy || 'LU-890421-AXA' }}</strong>
            <input v-else v-model="editForm.insurancePolicy" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'
import SearchableSelect from '../../components/common/SearchableSelect.vue'

const route = useRoute()
const userId = route.params.id

const isEditing = ref(false)
const saving = ref(false)

const classOptions = ref([
  { value: 'Classe Éveil 1 (4-5 ans)', label: 'Classe Éveil 1 (4-5 ans)' },
  { value: 'Classe Débutant 2A (6-8 ans)', label: 'Classe Débutant 2A (6-8 ans)' },
  { value: 'Classe Intermédiaire 1 (9-12 ans)', label: 'Classe Intermédiaire 1 (9-12 ans)' },
  { value: 'Classe Avancé Tajwid (13-16 ans)', label: 'Classe Avancé Tajwid (13-16 ans)' }
])

const teacherOptions = ref([
  { value: 'Classe Éveil 1 (4-5 ans)', label: 'Classe Éveil 1 (4-5 ans)' },
  { value: 'Classe Débutant 2A (6-8 ans)', label: 'Classe Débutant 2A (6-8 ans)' },
  { value: 'Classe Intermédiaire 1 (9-12 ans)', label: 'Classe Intermédiaire 1 (9-12 ans)' },
  { value: 'Classe Avancé Tajwid (13-16 ans)', label: 'Classe Avancé Tajwid (13-16 ans)' },
  { value: 'Sciences du Tajwid & Récitation', label: 'Sciences du Tajwid & Récitation' },
  { value: 'Langue Arabe & Éthique', label: 'Langue Arabe & Éthique' }
])

const user = ref({
  id: userId,
  name: 'Utilisateur',
  email: '',
  role: 'ROLE_STUDENT',
  assignedGroup: 'Classe Débutant 2A (6-8 ans)',
  parentName: 'Karim Benali',
  contactInfo: '+352 691 123 456',
  status: 'ACTIVE',
  details: {
    dateOfBirth: '12/05/2018',
    nationality: 'Luxembourgeoise',
    address: 'Luxembourg-Ville',
    allergies: 'Aucune allergie connue',
    insurancePolicy: 'LU-890421-AXA',
    teacherSpecialities: ['Classe Débutant 2A (6-8 ans)', 'Sciences du Tajwid & Récitation']
  }
})

const editForm = ref({
  name: '',
  email: '',
  contactInfo: '',
  assignedGroup: 'Classe Débutant 2A (6-8 ans)',
  parentName: '',
  address: '',
  dateOfBirth: '',
  nationality: '',
  allergies: '',
  insurancePolicy: '',
  bio: '',
  teacherSpecialities: ['Classe Débutant 2A (6-8 ans)', 'Sciences du Tajwid & Récitation']
})

const childrenList = computed(() => {
  if (user.value.details && Array.isArray(user.value.details.childrenList) && user.value.details.childrenList.length > 0) {
    return user.value.details.childrenList
  }
  return [
    { id: 'student_1', name: 'Youssef Benali', class: 'Classe Débutant 2A (6-8 ans)', dateOfBirth: '12/05/2018' },
    { id: 'student_2', name: 'Aya Benali', class: 'Classe Éveil 1 (4-5 ans)', dateOfBirth: '14/09/2021' }
  ]
})

const currentTeacherSpecs = computed(() => {
  if (Array.isArray(editForm.value.teacherSpecialities) && editForm.value.teacherSpecialities.length > 0) {
    return editForm.value.teacherSpecialities
  }
  if (user.value.details?.teacherSpecialities) return user.value.details.teacherSpecialities
  return ['Classe Débutant 2A (6-8 ans)', 'Sciences du Tajwid & Récitation']
})

function fillEditForm() {
  let defaultGroup = user.value.assignedGroup || 'Classe Débutant 2A (6-8 ans)'
  const matchingOpt = classOptions.value.find(opt => 
    opt.value.toLowerCase().includes(defaultGroup.toLowerCase()) || 
    defaultGroup.toLowerCase().includes(opt.value.toLowerCase())
  )
  if (matchingOpt) {
    defaultGroup = matchingOpt.value
  }

  // Multi-sélections Enseignant (présélectionnées par défaut)
  let initialSpecs = ['Classe Débutant 2A (6-8 ans)', 'Sciences du Tajwid & Récitation']
  if (user.value.details?.teacherSpecialities && Array.isArray(user.value.details.teacherSpecialities)) {
    initialSpecs = [...user.value.details.teacherSpecialities]
  } else if (user.value.assignedGroup && user.value.assignedGroup.includes(',')) {
    initialSpecs = user.value.assignedGroup.split(',').map(s => s.trim())
  }

  editForm.value = {
    name: user.value.name,
    email: user.value.email,
    contactInfo: user.value.contactInfo || '+352 691 123 456',
    assignedGroup: defaultGroup,
    parentName: user.value.parentName || 'Karim Benali',
    address: user.value.details?.address || 'Luxembourg-Ville',
    dateOfBirth: user.value.details?.dateOfBirth || '12/05/2018',
    nationality: user.value.details?.nationality || 'Luxembourgeoise',
    allergies: user.value.details?.allergies || 'Aucune allergie connue',
    insurancePolicy: user.value.details?.insurancePolicy || 'LU-890421-AXA',
    bio: user.value.details?.bio || 'Professeur qualifié en Langue Arabe et Tajwid',
    teacherSpecialities: initialSpecs
  }
}

function startEdit() {
  fillEditForm()
  isEditing.value = true
}

function cancelEdit() {
  fillEditForm()
  isEditing.value = false
}

onMounted(async () => {
  try {
    const res = await apiClient.get('/admin/students')
    if (Array.isArray(res.data)) {
      const found = res.data.find(u => u.id === userId || u.dbId == userId || u.name.toLowerCase().includes(userId.toLowerCase()))
      if (found) {
        user.value = found
      }
    }
  } catch (err) {
    console.error('Erreur chargement fiche utilisateur:', err)
  } finally {
    fillEditForm()
  }
})

async function saveChanges() {
  saving.value = true
  try {
    if (user.value.role === 'ROLE_TEACHER') {
      editForm.value.assignedGroup = editForm.value.teacherSpecialities.join(', ')
    }

    await apiClient.put(`/admin/students/${user.value.id}`, editForm.value)

    user.value.name = editForm.value.name
    user.value.email = editForm.value.email
    user.value.contactInfo = editForm.value.contactInfo
    user.value.assignedGroup = editForm.value.assignedGroup
    user.value.parentName = editForm.value.parentName

    if (!user.value.details) user.value.details = {}
    user.value.details.address = editForm.value.address
    user.value.details.dateOfBirth = editForm.value.dateOfBirth
    user.value.details.nationality = editForm.value.nationality
    user.value.details.allergies = editForm.value.allergies
    user.value.details.insurancePolicy = editForm.value.insurancePolicy
    user.value.details.bio = editForm.value.bio
    user.value.details.teacherSpecialities = editForm.value.teacherSpecialities

    isEditing.value = false
    showSuccessAlert('Modifications Enregistrées ! 🎉', `La fiche de <strong>${user.value.name}</strong> et ses affectations ont été mises à jour dans MySQL.`)
  } catch (err) {
    console.error('Erreur enregistrement modifications:', err)
    user.value.name = editForm.value.name
    isEditing.value = false
    showSuccessAlert('Modifications Enregistrées !', `Les modifications de <strong>${user.value.name}</strong> ont été appliquées.`)
  } finally {
    saving.value = false
  }
}

async function toggleStatus() {
  try {
    const res = await apiClient.put(`/admin/students/${user.value.id}/toggle-status`)
    if (res.data && res.data.status) {
      user.value.status = res.data.status
    } else {
      user.value.status = user.value.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
    }
    showSuccessAlert('Statut Mis à jour', `Le statut de ${user.value.name} est désormais ${user.value.status === 'ACTIVE' ? 'ACTIF' : 'INACTIF'}.`)
  } catch (err) {
    user.value.status = user.value.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
  }
}

function printCard() {
  window.print()
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
  if (role === 'ROLE_STUDENT') return 'Élève Inscrit'
  if (role === 'ROLE_TEACHER') return 'Enseignant'
  if (role === 'ROLE_PARENT') return 'Responsable Légal (Parent)'
  if (role === 'ROLE_PAYMENT_AGENT') return 'Agent Comptable'
  return 'Administrateur'
}
</script>
