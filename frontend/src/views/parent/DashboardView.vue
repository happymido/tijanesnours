<template>
  <div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-800 via-brand-700 to-blue-600 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-gold-300">
          Espace Parent & Famille
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Bienvenue, {{ parentName }}</h1>
        <p class="text-brand-100 text-sm mt-1">Suivi scolaire par compétences, bulletins PDF avec évaluations par étoiles, assiduité et facturation</p>
      </div>
      <router-link
        to="/register"
        class="px-5 py-3 bg-gold-500 hover:bg-gold-400 text-gray-900 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2"
      >
        <span>+</span> Inscrire un autre enfant
      </router-link>
    </div>

    <!-- Children Cards Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Mes Enfants Inscrits ({{ children.length }})</h2>
        <span class="text-xs text-gray-500 font-medium">Notation par compétences (1 à 5 ⭐)</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="child in children"
          :key="child.id"
          class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4 flex flex-col justify-between"
        >
          <div class="space-y-4">
            <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div
                  class="w-12 h-12 rounded-2xl font-bold flex items-center justify-center text-lg shadow-sm"
                  :class="child.badgeColor || 'bg-brand-100 text-brand-700'"
                >
                  {{ child.initials }}
                </div>
                <div>
                  <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ child.name }}</h3>
                  <p class="text-xs text-gray-500">{{ child.age }} ans • {{ child.className }}</p>
                </div>
              </div>
              <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full">Inscrit(e)</span>
            </div>

            <!-- Key Indicators -->
            <div class="grid grid-cols-3 gap-3 text-center">
              <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-gray-400 block">Présence</span>
                <p class="text-lg font-extrabold" :class="child.attendanceRate >= 80 ? 'text-emerald-600' : 'text-red-500'">
                  {{ child.attendanceRate }}%
                </p>
              </div>
              <div class="p-3 bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-800">
                <span class="text-[10px] uppercase font-bold text-amber-700 dark:text-amber-300 block">Moyenne</span>
                <p class="text-lg font-extrabold text-amber-600 dark:text-amber-400 flex items-center justify-center gap-1">
                  <span>⭐</span> {{ child.averageStars || '5.0' }} <span class="text-xs text-gray-400 font-normal">/ 5</span>
                </p>
              </div>
              <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-gray-400 block">Période</span>
                <p class="text-xs font-bold text-gray-700 dark:text-gray-200 mt-1 truncate">
                  {{ child.evaluation?.term || 'Trimestre 3' }}
                </p>
              </div>
            </div>

            <!-- Teacher Remarks Box -->
            <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700">
              <div class="flex items-center gap-1.5 text-xs font-bold text-brand-700 dark:text-brand-300 mb-1">
                <span>💬</span> Remarques de l'enseignant :
              </div>
              <p class="text-xs text-gray-600 dark:text-gray-300 italic line-clamp-2">
                "{{ child.evaluation?.teacherRemarks || 'Élève assidu, travail régulier et bon comportement.' }}"
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2 pt-2 border-t dark:border-gray-700">
            <button
              @click="openEvaluationModal(child)"
              class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2"
            >
              <span>⭐</span> Voir le Détail des Compétences
            </button>
            <div class="flex gap-2">
              <button
                @click="downloadBulletin(child)"
                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm"
              >
                <span>📄</span> Bulletin PDF (1-5 ⭐)
              </button>
              <button
                @click="downloadInvoice(child.name)"
                class="flex-1 py-2.5 border border-gray-200 dark:border-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center justify-center gap-1.5"
              >
                <span>💳</span> Facture PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Evaluation Detail Modal -->
    <div
      v-if="showDetailModal && selectedChild"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 my-8 space-y-6">
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center">
              ⭐
            </div>
            <div>
              <h3 class="font-extrabold text-lg text-gray-900 dark:text-white">
                Évaluation Trimestrielle : {{ selectedChild.name }}
              </h3>
              <p class="text-xs text-gray-500">
                {{ selectedChild.className }} • {{ selectedChild.evaluation?.term || 'Trimestre 3' }} ({{ selectedChild.evaluation?.schoolYear || '2026-2027' }})
              </p>
            </div>
          </div>
          <button
            @click="showDetailModal = false"
            class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1"
          >
            ✕
          </button>
        </div>

        <!-- Average Score Banner -->
        <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 rounded-2xl border border-amber-200 dark:border-amber-800 flex items-center justify-between">
          <div>
            <span class="text-xs uppercase font-bold text-amber-800 dark:text-amber-300">Niveau Global d'Acquisition</span>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Moyenne calculée sur l'ensemble des compétences</p>
          </div>
          <div class="text-right">
            <span class="text-2xl font-black text-amber-600 dark:text-amber-400">⭐ {{ selectedChild.averageStars }}</span>
            <span class="text-xs text-gray-500 block">sur 5 étoiles</span>
          </div>
        </div>

        <!-- Subjects & Skills list -->
        <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2">
          <div
            v-for="sub in curriculumList"
            :key="sub.id"
            class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 bg-gray-50/50 dark:bg-gray-800/50 space-y-3"
          >
            <div class="flex items-center gap-2 font-bold text-sm text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">
              <span class="text-lg">{{ sub.icon || '📖' }}</span>
              <span>{{ sub.name }}</span>
            </div>

            <div class="space-y-2">
              <div
                v-for="sk in sub.skills"
                :key="sk.id"
                class="flex items-center justify-between text-xs py-1"
              >
                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ sk.name }}</span>
                <div class="flex items-center gap-1 shrink-0">
                  <span
                    v-for="star in 5"
                    :key="star"
                    class="text-base"
                    :class="star <= getSkillRating(sk.id) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                  >
                    ★
                  </span>
                  <span class="text-[11px] font-bold text-gray-600 dark:text-gray-400 ml-1">
                    ({{ getSkillRating(sk.id) }}/5)
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Teacher Remarks -->
        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-700">
          <h4 class="text-xs font-bold text-gray-700 dark:text-gray-200 mb-1 flex items-center gap-1.5">
            <span>✍️</span> Remarques et appréciations de l'enseignant :
          </h4>
          <p class="text-xs text-gray-700 dark:text-gray-300 italic">
            "{{ selectedChild.evaluation?.teacherRemarks || 'Élève assidu, travail régulier et bon comportement.' }}"
          </p>
          <p class="text-[10px] text-gray-400 mt-2 text-right">
            Enseignant : {{ selectedChild.evaluation?.teacherName || 'Cheikh Mahmoud' }}
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="downloadBulletin(selectedChild)"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2"
          >
            <span>📄</span> Télécharger Bulletin PDF Officiel
          </button>
          <button
            @click="showDetailModal = false"
            class="px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>

    <!-- Financial Schedule Status -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
        Suivi des Échéances de Paiement Famille
      </h3>
      
      <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-blue-700 uppercase">Référence de Virement Structurée ISO 11649</span>
          <p class="text-xl font-mono font-extrabold text-blue-900 dark:text-blue-200 mt-1">RF12 2026 0042 89</p>
          <p class="text-xs text-blue-600 mt-0.5">Indiquez cette référence dans la communication de vos virements bancaires.</p>
        </div>
        <button @click="copyRef" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow">
          Copier la Référence
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { generateBulletinPdf, generateInvoicePdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert } from '../../plugins/notify'

const parentName = ref('Karim Benali')
const showDetailModal = ref(false)
const selectedChild = ref(null)
const curriculumList = ref([])

const children = ref([
  {
    id: 1,
    name: 'Youssef Benali',
    initials: 'YB',
    age: 8,
    className: 'Classe Débutant 2A (6-8 ans)',
    badgeColor: 'bg-brand-100 text-brand-700',
    attendanceRate: 98,
    averageStars: '4.8',
    evaluation: null
  },
  {
    id: 3,
    name: 'Aya Benali',
    initials: 'AB',
    age: 5,
    className: 'Classe Éveil 1 (4-5 ans)',
    badgeColor: 'bg-gold-100 text-gold-700',
    attendanceRate: 100,
    averageStars: '4.8',
    evaluation: null
  }
])

async function fetchChildrenData() {
  for (const child of children.value) {
    try {
      // 1. Assiduité
      const attRes = await apiClient.get(`/student/attendance?student_id=${child.id}`)
      if (attRes.data?.summary?.rate !== undefined) {
        child.attendanceRate = attRes.data.summary.rate
      }
      if (attRes.data?.className) {
        child.className = attRes.data.className
      }
    } catch (e) {
      console.warn(`Erreur assiduité pour ${child.name}:`, e)
    }

    try {
      // 2. Évaluations trimestrielles
      const evalRes = await apiClient.get(`/student/evaluations?student_id=${child.id}`)
      if (evalRes.data) {
        if (Array.isArray(evalRes.data.curriculum) && evalRes.data.curriculum.length > 0) {
          curriculumList.value = evalRes.data.curriculum
        }
        if (Array.isArray(evalRes.data.evaluations) && evalRes.data.evaluations.length > 0) {
          child.evaluation = evalRes.data.evaluations[0]
          child.averageStars = child.evaluation.averageStars || '4.8'
        }
      }
    } catch (e) {
      console.warn(`Erreur évaluations pour ${child.name}:`, e)
    }
  }
}

function openEvaluationModal(child) {
  selectedChild.value = child
  showDetailModal.value = true
}

function getSkillRating(skillId) {
  if (!selectedChild.value?.evaluation?.ratings) return 5
  return selectedChild.value.evaluation.ratings[skillId] ?? 5
}

function downloadBulletin(child) {
  const evalData = {
    ...(child.evaluation || {}),
    curriculum: curriculumList.value,
    averageStars: child.averageStars || '4.8'
  }
  generateBulletinPdf(child.name, child.className, evalData)
  showSuccessAlert('Génération PDF', `Le bulletin scolaire de ${child.name} avec évaluations par étoiles a été généré et téléchargé au format PDF.`)
}

function downloadInvoice(childName) {
  generateInvoicePdf(parentName.value, childName, 690)
  showSuccessAlert('Génération PDF', `La facture officielle acquittée pour ${childName} a été téléchargée au format PDF.`)
}

function copyRef() {
  navigator.clipboard.writeText('RF12-2026-0042-89')
  showSuccessAlert('Référence Copiée', 'La référence de virement bancaire RF12-2026-0042-89 a été copiée dans le presse-papier !')
}

onMounted(() => {
  fetchChildrenData()
})
</script>
