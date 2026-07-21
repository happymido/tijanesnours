<template>
  <div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-800 via-brand-700 to-blue-600 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-gold-300">
          Espace Parent & Famille
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Bienvenue, {{ parentName }}</h1>
        <p class="text-brand-100 text-sm mt-1">Suivi scolaire, notes, absences et facturation de vos enfants inscrits</p>
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
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Mes Enfants Inscrits (2)</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Child 1 -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
          <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-lg">
                YB
              </div>
              <div>
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Youssef Benali</h3>
                <p class="text-xs text-gray-500">8 ans • Niveau Débutant 2</p>
              </div>
            </div>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full">Inscrit</span>
          </div>

          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Présence</span>
              <p class="text-lg font-extrabold text-emerald-600">98%</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Moyenne</span>
              <p class="text-lg font-extrabold text-brand-600">18.5/20</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Sourates</span>
              <p class="text-lg font-extrabold text-gold-500">12 Mém.</p>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <button @click="downloadBulletin('Youssef')" class="flex-1 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs rounded-xl transition-all">
              Bulletin PDF
            </button>
            <button @click="downloadInvoice('Youssef')" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
              Facture PDF
            </button>
          </div>
        </div>

        <!-- Child 2 -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
          <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-gold-100 text-gold-700 font-bold flex items-center justify-center text-lg">
                SB
              </div>
              <div>
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Sarah Benali</h3>
                <p class="text-xs text-gray-500">5 ans • Maternelle / Éveil</p>
              </div>
            </div>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full">Inscrit</span>
          </div>

          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Présence</span>
              <p class="text-lg font-extrabold text-emerald-600">100%</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Appréciation</span>
              <p class="text-xs font-bold text-brand-600 mt-1">Excellente</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <span class="text-[10px] uppercase font-bold text-gray-400">Sourates</span>
              <p class="text-lg font-extrabold text-gold-500">4 Mém.</p>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <button @click="downloadBulletin('Sarah')" class="flex-1 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs rounded-xl transition-all">
              Bulletin PDF
            </button>
            <button @click="downloadInvoice('Sarah')" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
              Facture PDF
            </button>
          </div>
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
import { ref } from 'vue'

const parentName = ref('Karim Benali')

function downloadBulletin(child) {
  alert(`Téléchargement du bulletin trimestriel PDF pour ${child}.`)
}

function downloadInvoice(child) {
  alert(`Téléchargement de la facture PDF acquittée pour ${child}.`)
}

function copyRef() {
  navigator.clipboard.writeText('RF12-2026-0042-89')
  alert('Référence bancaire RF12-2026-0042-89 copiée dans le presse-papier !')
}
</script>
