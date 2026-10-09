<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Suivi des Inscriptions & Gestion des Règlements</h1>
        <p class="text-xs text-gray-500">Filtrage des inscriptions non payées par défaut, encaissements guichet/virement et tranches</p>
      </div>
      <div class="flex gap-3">
        <button @click="fetchEnrollments" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-800 dark:text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
          <span>🔄</span> Actualiser Données
        </button>
      </div>
    </div>

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border-l-4 border-l-red-500 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-400 uppercase">Inscriptions Non Payées</span>
          <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">{{ pendingEnrollments.length }}</span>
        </div>
        <p class="text-3xl font-extrabold text-red-600 mt-2">{{ formatPrice(totalPendingAmount) }} €</p>
        <p class="text-xs text-gray-500 mt-1">Dossiers en attente de règlement principal</p>
      </div>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border-l-4 border-l-amber-500 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-400 uppercase">Paiements Partiels</span>
          <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-extrabold">{{ partialEnrollments.length }}</span>
        </div>
        <p class="text-3xl font-extrabold text-amber-600 mt-2">{{ formatPrice(totalPartialAmount) }} €</p>
        <p class="text-xs text-gray-500 mt-1">1ère tranche encaissée</p>
      </div>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border-l-4 border-l-emerald-500 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-400 uppercase">Inscriptions Payées</span>
          <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold">{{ paidEnrollments.length }}</span>
        </div>
        <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ formatPrice(totalPaidAmount) }} €</p>
        <p class="text-xs text-gray-500 mt-1">Règlements enregistrés</p>
      </div>
    </div>

    <!-- Grille des Tarifs par Fratrie -->
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-brand-900 text-white p-5 rounded-2xl shadow-md border border-gray-700">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-amber-300 flex items-center gap-2">
          <span>📋</span> Grille Tarifaire Scolaire 2026 - 2027 (Par Parent)
        </h3>
        <span class="text-[11px] text-amber-200/80 font-semibold">Réduction automatique appliquée par fratrie</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div class="bg-white/10 backdrop-blur p-3 rounded-xl border border-white/15">
          <span class="block text-gray-300 font-medium">1 Enfant</span>
          <strong class="text-base font-extrabold text-amber-300">690 € / an</strong>
        </div>
        <div class="bg-white/10 backdrop-blur p-3 rounded-xl border border-white/15">
          <span class="block text-gray-300 font-medium">2 Enfants</span>
          <strong class="text-base font-extrabold text-amber-300">1 300 € / an</strong>
        </div>
        <div class="bg-white/10 backdrop-blur p-3 rounded-xl border border-white/15">
          <span class="block text-gray-300 font-medium">3 Enfants</span>
          <strong class="text-base font-extrabold text-amber-300">1 800 € / an</strong>
        </div>
        <div class="bg-white/10 backdrop-blur p-3 rounded-xl border border-white/15">
          <span class="block text-gray-300 font-medium">4 Enfants</span>
          <strong class="text-base font-extrabold text-amber-300">2 400 € / an</strong>
        </div>
      </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
      <div class="flex text-xs font-bold gap-6">
        <button 
          @click="activeTab = 'PENDING'" 
          :class="activeTab === 'PENDING' ? 'border-b-2 border-red-600 text-red-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
        >
          ⚠️ Non Payé ({{ pendingEnrollments.length }})
        </button>
        <button 
          @click="activeTab = 'PARTIAL_PAID'" 
          :class="activeTab === 'PARTIAL_PAID' ? 'border-b-2 border-amber-600 text-amber-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
        >
          ⏳ Tranche 1 Payée ({{ partialEnrollments.length }})
        </button>
        <button 
          @click="activeTab = 'PAID'" 
          :class="activeTab === 'PAID' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
        >
          ✅ Payé ({{ paidEnrollments.length }})
        </button>
        <button 
          @click="activeTab = 'ALL'" 
          :class="activeTab === 'ALL' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
        >
          Tous ({{ enrollmentsList.length }})
        </button>
      </div>

      <div class="flex items-center gap-3">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher élève, parent, réf RF..."
          class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs w-64"
        />
      </div>
    </div>

    <!-- Datatable Inscriptions & Règlements -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div v-if="loading" class="text-center py-8 text-xs font-bold text-gray-500">
        <i class="ri-loader-4-line animate-spin text-lg me-2"></i> Chargement des inscriptions...
      </div>

      <div v-else-if="filteredEnrollments.length === 0" class="text-center py-8 text-xs font-bold text-gray-400">
        Aucune inscription trouvée pour ce filtre.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Élève & Niveau</th>
              <th class="py-3 px-4">Responsable Légal / Parent</th>
              <th class="py-3 px-4">Réf. Structurée (RF)</th>
              <th class="py-3 px-4">Montant Total</th>
              <th class="py-3 px-4">Statut Paiement</th>
              <th class="py-3 px-4">Mode de Règlement</th>
              <th class="py-3 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="item in filteredEnrollments" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
              <td class="py-3.5 px-4">
                <div class="font-bold text-gray-900 dark:text-white text-sm">{{ item.studentName }}</div>
                <div class="text-[11px] text-brand-600 font-semibold">{{ item.assignedGroup }}</div>
              </td>
              <td class="py-3.5 px-4">
                <div class="font-semibold text-gray-800 dark:text-gray-200">{{ item.parentName }}</div>
                <div class="text-[10px] text-gray-400">{{ item.parentEmail }} • {{ item.parentPhone }}</div>
              </td>
              <td class="py-3.5 px-4 font-mono font-bold text-gray-600 dark:text-gray-300">
                {{ item.structuredReference }}
              </td>
              <td class="py-3.5 px-4 font-extrabold text-sm">
                <div>{{ item.totalAmount }} €</div>
                <div v-if="item.remainingAmount > 0" class="text-[10px] text-red-500 font-bold">
                  Reste : {{ item.remainingAmount }} €
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span v-if="item.status === 'PENDING'" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-red-100 text-red-800 border border-red-200">
                  ⚠️ Non payé
                </span>
                <span v-else-if="item.status === 'PARTIAL_PAID'" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                  ⏳ Tranche 1 Payée
                </span>
                <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  ✅ Payé
                </span>
              </td>
              <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300">
                <span v-if="item.paymentMethod === 'CASH'">💵 Espèces</span>
                <span v-else-if="item.paymentMethod === 'WIRE_TRANSFER'">🏦 Virement Bancaire</span>
                <span v-else-if="item.paymentMethod === 'PARTIAL_TRANCHE_1'">💳 Tranche 1 Payée</span>
                <span v-else class="text-gray-400 font-normal">Non spécifié</span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <button 
                  v-if="item.status !== 'PAID'"
                  @click="openPaymentModal(item)" 
                  class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5 ms-auto"
                >
                  <span>💳</span> Enregistrer Règlement
                </button>
                <span v-else class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200">
                  <span>✅</span> Règlement Effectué
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Enregistrer Règlement -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-5">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Enregistrer le Règlement</h3>
            <p class="text-xs text-brand-600 font-semibold mt-0.5">Élève : {{ selectedEnrollment?.studentName }}</p>
          </div>
          <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="submitPayment" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Niveau & Famille :</label>
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl border text-xs space-y-1">
              <div><strong>Niveau :</strong> {{ selectedEnrollment?.assignedGroup }}</div>
              <div><strong>Parent :</strong> {{ selectedEnrollment?.parentName }} ({{ selectedEnrollment?.parentEmail }})</div>
              <div><strong>Référence RF :</strong> <code class="font-bold text-brand-600">{{ selectedEnrollment?.structuredReference }}</code></div>
            </div>
          </div>

          <div>
            <label class="block font-bold mb-1 text-gray-800 dark:text-gray-200">Mode & Option de Règlement * :</label>
            <select v-model="paymentForm.paymentMethod" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-sm">
              <option value="CASH">💵 Espèces (Paiement Intégral - {{ formatPrice(selectedEnrollment?.totalAmount || systemSettings?.tariff_1_child || 690) }} €)</option>
              <option value="WIRE_TRANSFER">🏦 Virement bancaire (Paiement Intégral - {{ formatPrice(selectedEnrollment?.totalAmount || systemSettings?.tariff_1_child || 690) }} €)</option>
              <option value="PARTIAL_TRANCHE_1">⏳ Paiement Partiel - Tranche 1 Payée ({{ formatPrice(tranche1Amount) }} €)</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Montant Encaissé (€) :</label>
            <input 
              v-model.number="paymentForm.amount" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-extrabold text-brand-600 text-base"
            />
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Remarques / Référence transaction (optionnel) :</label>
            <input 
              v-model="paymentForm.reference" 
              type="text" 
              class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" 
              placeholder="ex: Reçu N° C-8902 / Virement VIR-1102"
            />
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showPaymentModal = false" class="px-4 py-2 rounded-xl border font-bold text-gray-600">
              Annuler
            </button>
            <button type="submit" :disabled="submittingPayment" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-md disabled:opacity-50">
              <span v-if="submittingPayment">Enregistrement...</span>
              <span v-else>Valider le Règlement</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const enrollmentsList = ref([])
const systemSettings = ref({})
const loading = ref(false)
const activeTab = ref('PENDING') // Default tab is PENDING (Non Payé par Défaut)
const searchQuery = ref('')

const showPaymentModal = ref(false)
const selectedEnrollment = ref(null)
const submittingPayment = ref(false)

const paymentForm = ref({
  paymentMethod: 'CASH',
  amount: 690,
  reference: ''
})

const tranche1Amount = computed(() => {
  if (systemSettings.value?.tranche_1_amount) {
    return Number(systemSettings.value.tranche_1_amount)
  }
  if (selectedEnrollment.value?.totalAmount) {
    return Math.round(selectedEnrollment.value.totalAmount / 3)
  }
  return 230
})

watch(() => paymentForm.value.paymentMethod, (newMethod) => {
  if (newMethod === 'PARTIAL_TRANCHE_1') {
    paymentForm.value.amount = tranche1Amount.value
  } else {
    paymentForm.value.amount = selectedEnrollment.value ? selectedEnrollment.value.totalAmount : (Number(systemSettings.value?.tariff_1_child) || 690)
  }
})

const pendingEnrollments = computed(() => enrollmentsList.value.filter(e => e.status === 'PENDING'))
const partialEnrollments = computed(() => enrollmentsList.value.filter(e => e.status === 'PARTIAL_PAID'))
const paidEnrollments = computed(() => enrollmentsList.value.filter(e => e.status === 'PAID'))

const totalPendingAmount = computed(() => pendingEnrollments.value.reduce((acc, curr) => acc + curr.remainingAmount, 0))
const totalPartialAmount = computed(() => partialEnrollments.value.reduce((acc, curr) => acc + curr.paidAmount, 0))
const totalPaidAmount = computed(() => paidEnrollments.value.reduce((acc, curr) => acc + curr.paidAmount, 0))

const filteredEnrollments = computed(() => {
  let list = enrollmentsList.value

  if (activeTab.value === 'PENDING') {
    list = list.filter(e => e.status === 'PENDING')
  } else if (activeTab.value === 'PARTIAL_PAID') {
    list = list.filter(e => e.status === 'PARTIAL_PAID')
  } else if (activeTab.value === 'PAID') {
    list = list.filter(e => e.status === 'PAID')
  }

  if (searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(e => 
      e.studentName.toLowerCase().includes(q) ||
      e.parentName.toLowerCase().includes(q) ||
      e.parentEmail.toLowerCase().includes(q) ||
      (e.structuredReference && e.structuredReference.toLowerCase().includes(q))
    )
  }

  return list
})

function formatPrice(val) {
  return Number(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchEnrollments() {
  loading.value = true
  try {
    const res = await apiClient.get('/admin/enrollments')
    if (Array.isArray(res.data)) {
      enrollmentsList.value = res.data
    }
  } catch (err) {
    console.error('Erreur lors de la récupération des inscriptions:', err)
  } finally {
    loading.value = false
  }
}

function openPaymentModal(item) {
  selectedEnrollment.value = item
  paymentForm.value = {
    paymentMethod: 'CASH',
    amount: item.totalAmount || 690,
    reference: ''
  }
  showPaymentModal.value = true
}

async function submitPayment() {
  if (!selectedEnrollment.value) return
  submittingPayment.value = true

  try {
    const payload = {
      paymentMethod: paymentForm.value.paymentMethod,
      amount: paymentForm.value.amount,
      reference: paymentForm.value.reference
    }

    const response = await apiClient.post(`/admin/enrollments/${selectedEnrollment.value.id}/payment`, payload)

    showPaymentModal.value = false
    await fetchEnrollments()

    const methodLabels = {
      'CASH': 'en Espèces',
      'WIRE_TRANSFER': 'par Virement bancaire',
      'PARTIAL_TRANCHE_1': 'en Paiement Partiel (Tranche 1 Payée)'
    }

    showSuccessAlert(
      'Règlement Enregistré ! 🎉',
      `Le paiement de <strong>${paymentForm.value.amount} €</strong> (${methodLabels[paymentForm.value.paymentMethod]}) pour l'élève <strong>${selectedEnrollment.value.studentName}</strong> a été validé avec succès.`
    )
  } catch (err) {
    console.error('Erreur enregistrement règlement:', err)
    showErrorAlert(
      'Erreur Règlement',
      err.response?.data?.error || 'Une erreur s\'est produite lors de l\'enregistrement du paiement.'
    )
  } finally {
    submittingPayment.value = false
  }
}

async function fetchSettings() {
  try {
    const res = await apiClient.get('/admin/settings')
    if (res.data && res.data.settings) {
      systemSettings.value = res.data.settings
    }
  } catch (err) {
    console.error('Erreur chargement paramètres:', err)
  }
}

onMounted(() => {
  fetchSettings()
  fetchEnrollments()
})
</script>
