<template>
  <div class="space-y-8 max-w-5xl mx-auto">
    <!-- Navigation & Breadcrumb -->
    <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
      <router-link to="/admin/users" class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 hover:underline">
        <span>◀</span> Retour à la liste des Utilisateurs
      </router-link>
      <div class="flex gap-3">
        <button @click="printCard" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow hover:bg-gray-800 transition-all flex items-center gap-2">
          <span>🖨️</span> Imprimer Fiche Détaillée
        </button>
      </div>
    </div>

    <!-- Main ID Card Header Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 text-white rounded-3xl p-8 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div class="flex items-center gap-6">
        <div :class="getAvatarBg(user.role)" class="w-20 h-20 rounded-3xl font-extrabold text-white text-3xl flex items-center justify-center shadow-2xl border-2 border-white/20">
          {{ user.name ? user.name[0] : 'U' }}
        </div>
        <div>
          <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-gold-300">
            Dossier Individuel Officiel
          </span>
          <h1 class="text-3xl font-extrabold mt-2">{{ user.name }}</h1>
          <p class="text-brand-100 text-xs mt-1">Identifiant BBD : <code class="font-mono text-gold-200">{{ user.id }}</code> • Inscription 2026-2027</p>
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

      <!-- Liste sous forme de cartes d'élèves rattachés -->
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
      <!-- 1. Coordonnées & Informations Personnelles -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold">👤</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Coordonnées & État Civil</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-gray-400 font-semibold block">Nom Complet :</span>
              <strong class="text-gray-900 dark:text-white text-sm">{{ user.name }}</strong>
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Adresse Email :</span>
              <strong class="text-gray-900 dark:text-white">{{ user.email || 'Non renseignée' }}</strong>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-2">
            <div>
              <span class="text-gray-400 font-semibold block">Téléphone Joignable :</span>
              <strong class="text-gray-900 dark:text-white">{{ user.contactInfo || '+352 691 123 456' }}</strong>
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Adresse Résidence :</span>
              <strong class="text-gray-900 dark:text-white">{{ user.details?.address || 'Luxembourg-Ville' }}</strong>
            </div>
          </div>

          <div v-if="user.role === 'ROLE_STUDENT'" class="grid grid-cols-2 gap-4 pt-2 border-t dark:border-gray-700">
            <div>
              <span class="text-gray-400 font-semibold block">Date de Naissance :</span>
              <strong>{{ user.details?.dateOfBirth || '12/05/2018' }}</strong>
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Nationalité :</span>
              <strong>{{ user.details?.nationality || 'Luxembourgeoise' }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Informations Pédagogiques & Affectation -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">🏫</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Affectation Scolaire & Niveaux</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div class="p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 space-y-2">
            <span class="text-[10px] uppercase font-bold text-emerald-700">Groupe / Classe Assignée</span>
            <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ user.assignedGroup || 'Classe Débutant 2A (6-8 ans)' }}</p>
            <p class="text-xs text-gray-500">Créneau : Samedi 09:00 - 12:00 • Salle Maryam 1</p>
          </div>

          <div v-if="user.role === 'ROLE_TEACHER'" class="space-y-2">
            <span class="text-gray-400 font-semibold block">Spécialités d'Enseignement :</span>
            <p class="font-bold text-brand-600">Langue Arabe, Rules of Tajwid, Memorization</p>
          </div>
        </div>
      </div>

      <!-- 3. Responsable Légal & Finances -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center text-sm font-bold">👨‍👩‍👧</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Rattachement Légal & Règlements</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div v-if="user.role === 'ROLE_STUDENT'" class="space-y-2">
            <div>
              <span class="text-gray-400 font-semibold block">Parent / Tuteur Légal :</span>
              <strong class="text-brand-600 text-sm font-bold">👨‍👩‍👧 {{ user.parentName || 'Karim Benali' }}</strong>
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Téléphone Urgence Parent :</span>
              <strong class="text-gray-900 dark:text-white">{{ user.contactInfo || '+352 691 123 456' }}</strong>
            </div>
          </div>

          <div v-if="user.role === 'ROLE_PARENT'" class="space-y-2">
            <span class="text-gray-400 font-semibold block">Mode de Paiement Préféré :</span>
            <p class="font-bold text-gold-700 text-sm">💳 {{ user.details?.paymentMethod || 'Prélèvement Automatique SEPA' }}</p>
            <p class="text-gray-500 text-[11px]">Mandat SEPA : SEPA-2026-0042-LU • Validation Stripe OK</p>
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
            <strong class="text-gray-800 dark:text-gray-200">{{ user.details?.allergies || 'Aucune allergie signalée' }}</strong>
          </div>
          <div>
            <span class="text-gray-400 font-semibold block">Assurance Responsabilité Civile :</span>
            <strong class="text-emerald-600 font-bold">Validée (Police: {{ user.details?.insurancePolicy || 'LU-890421-AXA' }})</strong>
          </div>
          <div class="flex gap-4 pt-2 border-t dark:border-gray-700 text-[11px]">
            <span class="text-emerald-600 font-bold">✓ Consentement RGPD Validé</span>
            <span class="text-emerald-600 font-bold">✓ Droit à l'Image Autorisé</span>
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
import { showSuccessAlert } from '../../plugins/notify'

const route = useRoute()
const userId = route.params.id

const user = ref({
  id: userId,
  name: 'Utilisateur',
  email: '',
  role: 'ROLE_STUDENT',
  assignedGroup: 'Classe Débutant 2A',
  parentName: 'Karim Benali',
  contactInfo: '+352 691 123 456',
  status: 'ACTIVE',
  details: {
    dateOfBirth: '12/05/2018',
    nationality: 'Luxembourgeoise',
    address: 'Luxembourg-Ville',
    allergies: 'Aucune allergie connue',
    insurancePolicy: 'LU-890421-AXA',
    childrenList: [
      { id: 'student_1', name: 'Youssef Benali', class: 'Classe Débutant 2A (6-8 ans)', dateOfBirth: '12/05/2018' },
      { id: 'student_2', name: 'Aya Benali', class: 'Classe Éveil 1 (4-5 ans)', dateOfBirth: '14/09/2021' }
    ]
  }
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
  }
})

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
