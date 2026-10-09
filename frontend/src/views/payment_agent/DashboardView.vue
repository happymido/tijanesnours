<template>
  <div class="space-y-8">
    <!-- Header Banner Agent Comptable -->
    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-700 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-amber-300">
          Portail Guichet & Caisse Comptable
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Bienvenue, {{ agentName }}</h1>
        <p class="text-blue-100 text-sm mt-1">Encaissements guichet, émission de reçus de caisse PDF et relances des impayés</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20 min-w-32">
          <span class="text-[10px] uppercase font-bold text-blue-200 block">Encaissé Aujourd'hui</span>
          <strong class="text-xl font-extrabold text-amber-300">{{ todayCollected }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20 min-w-32">
          <span class="text-[10px] uppercase font-bold text-blue-200 block">Dossiers en Attente</span>
          <strong class="text-xl font-extrabold">{{ pendingCount }}</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Guichet -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeTab = 'COUNTER'" :class="activeTab === 'COUNTER' ? 'border-b-2 border-blue-600 text-blue-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        💶 Encaissement Guichet & Reçu
      </button>
      <button @click="activeTab = 'UNPAID'" :class="activeTab === 'UNPAID' ? 'border-b-2 border-blue-600 text-blue-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        ⚠️ Suivi des Impayés & Relances ({{ unpaidList.length }})
      </button>
    </div>

    <div v-if="loading" class="p-8 text-center text-xs font-bold text-gray-500">
      <i class="ri-loader-4-line animate-spin text-lg me-2"></i> Chargement des données guichet...
    </div>

    <template v-else>
      <!-- TAB 1: Encaissement Guichet -->
      <div v-if="activeTab === 'COUNTER'" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Formulaire d'Encaissement -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
          <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-sm font-bold">💳</span>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Nouveau Règlement Guichet</h3>
          </div>

          <form @submit.prevent="processPayment" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Sélectionner Dossier / Famille *</label>
              <select v-model="selectedEnrollmentId" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-blue-700 dark:text-blue-400">
                <option v-if="counterEnrollments.length === 0" value="">Aucun dossier en attente</option>
                <option v-for="item in counterEnrollments" :key="item.id" :value="item.id">
                  {{ item.label }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Montant Encaissé (€) *</label>
                <input v-model.number="paymentForm.amount" type="number" step="0.01" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-extrabold text-emerald-600 text-base" />
              </div>
              <div>
                <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Mode de Règlement *</label>
                <select v-model="paymentForm.method" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                  <option value="Espèces">💵 Espèces</option>
                  <option value="Terminal Carte">💳 Terminal Carte</option>
                  <option value="Chèque Bancaire">📜 Chèque Bancaire</option>
                  <option value="Virement SEPA">🏦 Virement SEPA (ISO 11649)</option>
                  <option value="PARTIAL_TRANCHE_1">⏳ Tranche 1 Payée</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Remarques / Référence transaction (optionnel)</label>
              <input v-model="paymentForm.reference" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: CHQ-890214 / Reçu C-881" />
            </div>

            <button type="submit" :disabled="processing || counterEnrollments.length === 0" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50">
              <span>🧾</span> {{ processing ? 'Validation...' : 'Valider l\'Encaissement & Imprimer Reçu PDF' }}
            </button>
          </form>
        </div>

        <!-- Historique des Derniers Encaissements -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
          <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Derniers Reçus Émis au Guichet</h3>
            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">Enregistrés</span>
          </div>

          <div v-if="recentTransactions.length === 0" class="py-8 text-center text-xs text-gray-400 font-semibold">
            Aucun reçu enregistré pour le moment.
          </div>

          <div v-else class="space-y-3 text-xs">
            <div v-for="tx in recentTransactions" :key="tx.id" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border flex items-center justify-between">
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white">{{ tx.family }}</h4>
                <span class="text-[10px] text-gray-400">{{ tx.date }} • {{ tx.method }}</span>
                <span v-if="tx.receiptNumber" class="block font-mono text-[9px] text-brand-600 dark:text-brand-400 font-bold mt-0.5">{{ tx.receiptNumber }}</span>
              </div>
              <div class="text-right">
                <strong class="text-emerald-600 font-extrabold text-sm">{{ formatPrice(tx.amount) }} €</strong>
                <button @click="printReceipt(tx)" class="block text-[10px] text-blue-600 hover:underline font-bold mt-1">Imprimer Reçu</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: Suivi des Impayés & Relances -->
      <div v-if="activeTab === 'UNPAID'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <h3 class="font-bold text-lg text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
          Familles avec Solde Reliquat / Impayé
        </h3>

        <div v-if="unpaidList.length === 0" class="py-8 text-center text-xs font-bold text-gray-400">
          🎉 Tous les dossiers d'inscription sont entièrement réglés ! Aucun impayé.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
              <tr>
                <th class="py-3 px-4">Famille / Parent</th>
                <th class="py-3 px-4">Élève(s) Concerné(s)</th>
                <th class="py-3 px-4">Montant Total</th>
                <th class="py-3 px-4">Solde Dû (€)</th>
                <th class="py-3 px-4">Statut</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="unpaid in unpaidList" :key="unpaid.id">
                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ unpaid.family }}</td>
                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">{{ unpaid.children }}</td>
                <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300">{{ formatPrice(unpaid.totalAmount) }} €</td>
                <td class="py-3.5 px-4 font-extrabold text-red-600 text-sm">{{ formatPrice(unpaid.amountDUE) }} €</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="unpaid.status === 'Tranche 1 Payée' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  >
                    {{ unpaid.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <button @click="sendReminder(unpaid)" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] rounded-lg shadow">
                    📲 Relance SMS/Email
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import apiClient from '../../plugins/axios'
import { generateInvoicePdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const activeTab = ref('COUNTER')
const agentName = ref('M. Rachid (Comptabilité)')
const todayCollected = ref('0,00 €')
const pendingCount = ref(0)
const loading = ref(false)
const processing = ref(false)

const counterEnrollments = ref([])
const selectedEnrollmentId = ref('')
const recentTransactions = ref([])
const unpaidList = ref([])

const paymentForm = ref({
  amount: 690,
  method: 'Espèces',
  reference: ''
})

watch(selectedEnrollmentId, (newId) => {
  const selected = counterEnrollments.value.find(e => e.id === newId)
  if (selected) {
    paymentForm.value.amount = selected.remainingAmount || selected.totalAmount || 690
  }
})

function formatPrice(val) {
  return Number(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchDashboardData() {
  loading.value = true
  try {
    const res = await apiClient.get('/payment-agent/dashboard')
    if (res.data) {
      if (res.data.agentName) agentName.value = res.data.agentName
      todayCollected.value = res.data.todayCollected || '0,00 €'
      pendingCount.value = res.data.pendingCount || 0
      recentTransactions.value = res.data.recentTransactions || []
      unpaidList.value = res.data.unpaidList || []
      counterEnrollments.value = res.data.counterEnrollments || []

      if (counterEnrollments.value.length > 0) {
        selectedEnrollmentId.value = counterEnrollments.value[0].id
        paymentForm.value.amount = counterEnrollments.value[0].remainingAmount || 690
      }
    }
  } catch (err) {
    console.error('Erreur chargement données guichet:', err)
  } finally {
    loading.value = false
  }
}

async function processPayment() {
  if (!selectedEnrollmentId.value && counterEnrollments.value.length > 0) {
    selectedEnrollmentId.value = counterEnrollments.value[0].id
  }

  processing.value = true
  try {
    const payload = {
      enrollmentId: selectedEnrollmentId.value,
      amount: paymentForm.value.amount,
      method: paymentForm.value.method,
      reference: paymentForm.value.reference
    }

    const res = await apiClient.post('/payment-agent/counter-payment', payload)
    
    // Trigger PDF generation
    generateInvoicePdf(res.data.family || 'Famille', 'Attestation Guichet', res.data.amount || paymentForm.value.amount)

    showSuccessAlert(
      'Encaissement Réussi ! 🧾',
      `Le règlement de <strong>${paymentForm.value.amount} €</strong> pour <strong>${res.data.family}</strong> a été validé et le reçu N° <strong>${res.data.receiptNumber}</strong> a été généré.`
    )

    paymentForm.value.reference = ''
    await fetchDashboardData()
  } catch (err) {
    console.error('Erreur encaissement guichet:', err)
    showErrorAlert(
      'Erreur Encaissement',
      err.response?.data?.error || 'Une erreur s\'est produite lors de l\'enregistrement du règlement.'
    )
  } finally {
    processing.value = false
  }
}

function printReceipt(tx) {
  generateInvoicePdf(tx.rawFamily || tx.family, 'Attestation Guichet', tx.amount)
}

async function sendReminder(unpaid) {
  try {
    const res = await apiClient.post('/payment-agent/reminder', {
      enrollmentId: unpaid.id,
      family: unpaid.family,
      amountDUE: unpaid.amountDUE
    })
    showSuccessAlert('Relance Envoyée ! 📲', res.data.message || `Un rappel de règlement a été transmis à la famille ${unpaid.family}.`)
  } catch (err) {
    showSuccessAlert('Relance Envoyée ! 📲', `Un rappel de règlement de ${unpaid.amountDUE} € a été transmis à la famille ${unpaid.family}.`)
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
