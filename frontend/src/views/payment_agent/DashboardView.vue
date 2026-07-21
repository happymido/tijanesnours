<template>
  <div class="space-y-8">
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-600 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
          Espace Agent Comptable & Financier
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Gestion des Encaissements & Échéanciers</h1>
        <p class="text-emerald-100 text-sm mt-1">Saisie manuelle, remises SEPA Pain.008 et rapprochement de virements ISO 11649</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button
          @click="showManualModal = true"
          class="px-5 py-3 bg-white text-emerald-800 font-bold text-xs rounded-xl shadow-lg hover:bg-emerald-50 transition-all flex items-center gap-2"
        >
          <span>💵</span> Saisir un Encaissement Manuel
        </button>
        <button
          @click="generateSepaBatch"
          class="px-5 py-3 bg-emerald-950 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2"
        >
          <span>📄</span> Exporter SEPA XML Pain.008
        </button>
      </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Encaissements du Mois</span>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-emerald-600">12 450.00 €</span>
          <span class="text-xs text-gray-400">38 paiements</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Espèces, Stripe, SEPA & Virements</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Échéances en Attente</span>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-amber-600">4 200.00 €</span>
          <span class="text-xs text-amber-600 font-bold">14 dossiers</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Rapprochement bancaire nécessaire</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Prélèvements SEPA à Envoyer</span>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-brand-600">8 900.00 €</span>
          <span class="text-xs text-brand-600 font-bold">26 mandats</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Format ISO 20022 Pain.008</p>
      </div>
    </div>

    <!-- Pending Payments Table with ISO 11649 Ref matching -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Échéances à Recouvrer & Rapprochement Bancaire</h3>
          <p class="text-xs text-gray-500">Utilisez la référence structurée ISO 11649 pour valider un virement reçu</p>
        </div>
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Chercher par Ref RF12-XXXX..."
            class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs w-64"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Élève & Parent</th>
              <th class="py-3 px-4">Référence ISO 11649</th>
              <th class="py-3 px-4">Échéance</th>
              <th class="py-3 px-4">Montant</th>
              <th class="py-3 px-4">Mode</th>
              <th class="py-3 px-4">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="item in filteredSchedules" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                <div>{{ item.studentName }}</div>
                <div class="text-[10px] text-gray-400">Parent: {{ item.parentName }}</div>
              </td>
              <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-gold-400">
                {{ item.rfRef }}
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">{{ item.dueDate }}</td>
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ item.amount }} €</td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                  {{ item.method }}
                </span>
              </td>
              <td class="py-3.5 px-4 flex gap-2">
                <button
                  @click="validatePayment(item)"
                  class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow transition-all"
                >
                  Valider Paiement
                </button>
                <button
                  @click="generateReceipt(item)"
                  class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold text-[10px] transition-all"
                >
                  Reçu PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Enregistrement Manuel -->
    <div v-if="showManualModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Enregistrer un Paiement Manuel</h3>
        
        <div class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Montant Encaissé (€)</label>
            <input v-model="manualForm.amount" type="number" step="0.01" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="150.00" />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Mode d'encaissement</label>
            <select v-model="manualForm.paymentMethod" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700">
              <option value="CASH">Espèces</option>
              <option value="WIRE_TRANSFER">Virement Bancaire (RF)</option>
              <option value="CHECK">Chèque</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Référence / Numéro de transaction</label>
            <input v-model="manualForm.transactionReference" type="text" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: CHQ-890421" />
          </div>
        </div>

        <div class="flex gap-3 pt-2">
          <button @click="submitManualPayment" class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl text-xs shadow-md hover:bg-emerald-700">
            Valider & Générer Reçu PDF
          </button>
          <button @click="showManualModal = false" class="py-3 px-4 border text-gray-600 rounded-xl text-xs font-semibold">
            Annuler
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const showManualModal = ref(false)
const searchQuery = ref('')

const manualForm = ref({
  amount: 150.00,
  paymentMethod: 'CASH',
  transactionReference: ''
})

const pendingSchedules = ref([
  { id: 101, studentName: 'Youssef Benali', parentName: 'Karim Benali', rfRef: 'RF12-2026-0001-89', dueDate: '01/09/2026', amount: '150.00', method: 'VIREMENT' },
  { id: 102, studentName: 'Maryam El Amrani', parentName: 'Fatima El Amrani', rfRef: 'RF84-2026-0002-14', dueDate: '01/09/2026', amount: '150.00', method: 'ESPÈCES' },
  { id: 103, studentName: 'Adam Mansouri', parentName: 'Tariq Mansouri', rfRef: 'RF45-2026-0003-67', dueDate: '01/10/2026', amount: '150.00', method: 'SEPA' }
])

const filteredSchedules = computed(() => {
  if (!searchQuery.value) return pendingSchedules.value
  return pendingSchedules.value.filter(s => s.rfRef.toLowerCase().includes(searchQuery.value.toLowerCase()) || s.studentName.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

function validatePayment(item) {
  alert(`Paiement de ${item.amount} € validé avec succès pour ${item.studentName} (${item.rfRef}).`)
  pendingSchedules.value = pendingSchedules.value.filter(s => s.id !== item.id)
}

function generateReceipt(item) {
  alert(`Génération du reçu PDF numéroté REC-20260722-${item.id} pour ${item.studentName}.`)
}

function submitManualPayment() {
  alert(`Encaissement manuel de ${manualForm.value.amount} € (${manualForm.value.paymentMethod}) enregistré par l'Agent Comptable. Reçu généré.`)
  showManualModal.value = false
}

function generateSepaBatch() {
  alert(`Génération du fichier XML ISO 20022 Pain.008 avec ${pendingSchedules.value.length} prélèvements SEPA. Téléchargement démarré.`)
}
</script>
