<template>
  <div class="space-y-8">
    <!-- Header Banner Agent Comptable -->
    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-700 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-gold-300">
          Portail Guichet & Caisse Comptable
        </span>
        <h1 class="text-3xl font-extrabold mt-2">Bienvenue, {{ agentName }}</h1>
        <p class="text-blue-100 text-sm mt-1">Encaissements guichet, émission de reçus de caisse PDF et relances des impayés</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-blue-200 block">Encaissé Aujourd'hui</span>
          <strong class="text-xl font-extrabold text-gold-300">1 350 €</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-blue-200 block">Dossiers en Attente</span>
          <strong class="text-xl font-extrabold">3</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Guichet -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="activeTab = 'COUNTER'" :class="activeTab === 'COUNTER' ? 'border-b-2 border-blue-600 text-blue-600 pb-3' : 'text-gray-500 pb-3'">
        💶 Encaissement Guichet & Reçu
      </button>
      <button @click="activeTab = 'UNPAID'" :class="activeTab === 'UNPAID' ? 'border-b-2 border-blue-600 text-blue-600 pb-3' : 'text-gray-500 pb-3'">
        ⚠️ Suivi des Impayés & Relances
      </button>
    </div>

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
            <label class="block font-semibold mb-1">Responsable Légal / Parent</label>
            <select v-model="paymentForm.family" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-blue-700">
              <option value="Karim Benali">Karim Benali (Youssef & Aya Benali)</option>
              <option value="Mehdi Bennani">Mehdi Bennani (Rayane Bennani)</option>
              <option value="Sami Hamdi">Sami Hamdi (Lina Hamdi)</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Montant Encaissé (€)</label>
              <input v-model="paymentForm.amount" type="number" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-extrabold text-emerald-600 text-base" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Mode de Règlement</label>
              <select v-model="paymentForm.method" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option value="Espèces">💵 Espèces</option>
                <option value="Terminal Carte">💳 Terminal Carte</option>
                <option value="Chèque Bancaire">📜 Chèque Bancaire</option>
                <option value="Virement SEPA">🏦 Virement SEPA (ISO 11649)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Remarques / Référence Chèque ou Trans.</label>
            <input v-model="paymentForm.reference" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="ex: CHQ-890214 / SEPA-RF12" />
          </div>

          <button type="submit" :disabled="processing" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50">
            <span>🧾</span> {{ processing ? 'Traitement en cours...' : 'Valider l\'Encaissement & Imprimer Reçu PDF' }}
          </button>
        </form>
      </div>

      <!-- Historique des Derniers Encaissements -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Derniers Reçus Émis au Guichet</h3>
          <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">Aujourd'hui</span>
        </div>

        <div class="space-y-3 text-xs">
          <div v-for="tx in recentTransactions" :key="tx.id" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border flex items-center justify-between">
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white">{{ tx.family }}</h4>
              <span class="text-[10px] text-gray-400">{{ tx.date }} • {{ tx.method }}</span>
            </div>
            <div class="text-right">
              <strong class="text-emerald-600 font-extrabold text-sm">{{ tx.amount }} €</strong>
              <button @click="printReceipt(tx)" class="block text-[10px] text-blue-600 hover:underline font-bold">Imprimer Reçu</button>
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

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Famille / Parent</th>
              <th class="py-3 px-4">Élève(s) Concerne(s)</th>
              <th class="py-3 px-4">Solde Dû (€)</th>
              <th class="py-3 px-4">Statut Relance</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="unpaid in unpaidList" :key="unpaid.id">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ unpaid.family }}</td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">{{ unpaid.children }}</td>
              <td class="py-3.5 px-4 font-extrabold text-red-600 text-sm">{{ unpaid.amountDUE }} €</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { generateInvoicePdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert } from '../../plugins/notify'

const activeTab = ref('COUNTER')
const agentName = ref('M. Rachid (Comptabilité)')
const processing = ref(false)

const paymentForm = ref({
  family: 'Karim Benali',
  amount: 450,
  method: 'Espèces',
  reference: 'REC-2026-0042'
})

const recentTransactions = ref([
  { id: 1, family: 'Karim Benali', amount: 450, method: 'Espèces', date: 'Aujourd\'hui 10:30' },
  { id: 2, family: 'Mehdi Bennani', amount: 450, method: 'Terminal Carte', date: 'Aujourd\'hui 09:15' },
  { id: 3, family: 'Sami Hamdi', amount: 450, method: 'Virement SEPA', date: 'Hier 16:45' }
])

const unpaidList = ref([
  { id: 1, family: 'Youssef El Kadiri', children: 'Amine El Kadiri', amountDUE: 150, status: '1ère Relance Envoyée' },
  { id: 2, family: 'Tariq Mansouri', children: 'Sara Mansouri', amountDUE: 220, status: 'Échéance Dépassée (7j)' }
])

function processPayment() {
  processing.value = true
  setTimeout(() => {
    generateInvoicePdf(paymentForm.value.family, 'Attestation Guichet', paymentForm.value.amount)
    recentTransactions.value.unshift({
      id: Date.now(),
      family: paymentForm.value.family,
      amount: paymentForm.value.amount,
      method: paymentForm.value.method,
      date: 'À l\'instant'
    })
    processing.value = false
    showSuccessAlert(
      'Encaissement Réussi ! 🧾',
      `Le règlement de <strong>${paymentForm.value.amount} €</strong> pour <strong>${paymentForm.value.family}</strong> a été validé et le reçu PDF a été généré.`
    )
  }, 500)
}

function printReceipt(tx) {
  generateInvoicePdf(tx.family, 'Attestation Guichet', tx.amount)
}

function sendReminder(unpaid) {
  showSuccessAlert(
    'Relance Envoyée ! 📲',
    `Un rappel de règlement de <strong>${unpaid.amountDUE} €</strong> a été transmis à la famille <strong>${unpaid.family}</strong>.`
  )
}
</script>
