<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres Généraux & Grille Tarifaire</h1>
      <p class="text-xs text-gray-500">Configuration de l'établissement et barème des frais de scolarité stockés en base de données</p>
    </div>

    <div v-if="loading" class="p-8 text-center text-sm font-semibold text-gray-500">
      Chargement des paramètres...
    </div>

    <form v-else @submit.prevent="saveSettings" class="space-y-6 max-w-4xl">
      <!-- Card 1: Grille Tarifaire & Frais -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <div class="flex items-center gap-2 border-b pb-3 dark:border-gray-700">
          <span class="text-xl">💶</span>
          <div>
            <h2 class="font-bold text-base text-gray-900 dark:text-white">Barème Familial & Options de Règlement</h2>
            <p class="text-xs text-gray-500">Ces montants sont appliqués automatiquement lors des inscriptions et dans le module de règlement finance</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tarif 1er Enfant (€ / an) :</label>
            <input 
              v-model="form.tariff_1_child" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tarif Total 2 Enfants (€ / an) :</label>
            <input 
              v-model="form.tariff_2_children" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tarif Total 3 Enfants (€ / an) :</label>
            <input 
              v-model="form.tariff_3_children" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tarif Total 4 Enfants (€ / an) :</label>
            <input 
              v-model="form.tariff_4_children" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-brand-600 dark:text-brand-400 mb-1">Montant Tranche 1 / Acompte (€) :</label>
            <input 
              v-model="form.tranche_1_amount" 
              type="number" 
              step="0.01" 
              required 
              class="w-full px-4 py-2.5 rounded-xl border border-brand-200 bg-brand-50/50 dark:bg-gray-700 font-extrabold text-brand-700 dark:text-brand-300"
            />
            <p class="text-[10px] text-gray-400 mt-1">Montant par défaut pour le choix "Paiement Partiel - Tranche 1 Payée"</p>
          </div>
        </div>
      </div>

      <!-- Card 2: Informations Légales -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <div class="flex items-center gap-2 border-b pb-3 dark:border-gray-700">
          <span class="text-xl">🏫</span>
          <div>
            <h2 class="font-bold text-base text-gray-900 dark:text-white">Identité & Coordonnées de l'Établissement</h2>
            <p class="text-xs text-gray-500">Informations affichées sur les reçus de règlement et attestations PDF</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom Officiel de l'Association</label>
            <input v-model="form.school_name" type="text" required class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Numéro RCS Luxembourg</label>
            <input v-model="form.school_rcs" type="text" required class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Adresse Principale</label>
            <input v-model="form.school_address" type="text" required class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Officiel de Contact</label>
            <input v-model="form.school_email" type="email" required class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
          </div>
        </div>
      </div>

      <!-- Action Button -->
      <div class="flex justify-end">
        <button 
          type="submit" 
          :disabled="saving" 
          class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md transition disabled:opacity-50 flex items-center gap-2"
        >
          <span v-if="saving">Enregistrement...</span>
          <span v-else>💾 Enregistrer les Paramètres & la Grille Tarifaire</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const loading = ref(false)
const saving = ref(false)

const form = ref({
  tariff_1_child: '690.00',
  tariff_2_children: '1300.00',
  tariff_3_children: '1800.00',
  tariff_4_children: '2400.00',
  tranche_1_amount: '230.00',
  school_name: 'École Tijanes Nours ASBL',
  school_rcs: 'RCS F12999',
  school_address: 'Centre Maryam / LJM Luxembourg',
  school_email: 'contact@tijanesnours.lu'
})

async function fetchSettings() {
  loading.value = true
  try {
    const res = await apiClient.get('/admin/settings')
    if (res.data && res.data.settings) {
      Object.assign(form.value, res.data.settings)
    }
  } catch (err) {
    console.error('Erreur chargement paramètres:', err)
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    const res = await apiClient.post('/admin/settings', form.value)
    showSuccessAlert(
      'Paramètres Enregistrés ! 🎉',
      res.data.message || 'La grille tarifaire et les paramètres ont été mis à jour avec succès.'
    )
  } catch (err) {
    console.error('Erreur enregistrement paramètres:', err)
    showErrorAlert(
      'Erreur de Sauvegarde',
      err.response?.data?.error || 'Une erreur s\'est produite lors de l\'enregistrement des paramètres.'
    )
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>
