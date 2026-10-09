<template>
  <div class="register-view py-5 bg-gray-50 min-h-screen">
    <div class="container max-w-2xl mx-auto">
      <div class="bg-white rounded-3xl p-5 p-sm-8 shadow-xl border border-gray-100">
        <div class="text-center mb-4">
          <span class="badge bg-gold-500 text-gray-900 font-bold px-3 py-1 rounded-full text-xs mb-2 d-inline-block">
            Rentrée 2026 - 2027
          </span>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Dossier d'Inscription Scolaire</h1>
          <p class="text-xs text-gray-500">Inscrivez votre enfant à l'École Tijanes Nours Luxembourg</p>
        </div>

        <div v-if="successSubmitted" class="p-6 bg-emerald-50 rounded-2xl border border-emerald-200 text-center space-y-4">
          <div class="w-16 h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center text-3xl mx-auto shadow-md">
            ✓
          </div>
          <h3 class="text-xl font-extrabold text-emerald-800">Inscription Transmise avec Succès ! 🎉</h3>
          <p class="text-xs text-gray-700 leading-relaxed max-w-md mx-auto">
            Le dossier d'inscription de <strong>{{ submittedStudentName }}</strong> pour le niveau <strong>{{ submittedLevel }}</strong> a été enregistré avec succès dans notre base de données et est maintenant disponible dans la partie administration.
          </p>
          <div class="pt-2 flex justify-center gap-3">
            <button @click="resetForm" class="btn btn-outline-secondary text-xs font-bold px-4 py-2 rounded-xl">
              Inscrire un autre enfant
            </button>
            <router-link to="/login" class="default-btn text-xs font-bold px-4 py-2 rounded-xl">
              Accéder à l'espace membre
            </router-link>
          </div>
        </div>
        
        <form v-else @submit.prevent="submitRegistration" class="space-y-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Nom de l'élève *</label>
              <input 
                v-model="form.studentLastName" 
                type="text" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
                placeholder="ex: Benali" 
              />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Prénom de l'élève *</label>
              <input 
                v-model="form.studentFirstName" 
                type="text" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
                placeholder="ex: Youssef" 
              />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Date de naissance *</label>
              <input 
                v-model="form.dateOfBirth" 
                type="date" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
              />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Niveau souhaité *</label>
              <select 
                v-model="form.desiredLevel" 
                required 
                class="form-select px-4 py-2.5 rounded-xl border-gray-300 text-sm"
              >
                <option value="" disabled>-- Choisir le niveau souhaité --</option>
                <option 
                  v-for="level in courseLevels" 
                  :key="level.id" 
                  :value="getLevelName(level)"
                >
                  {{ getLevelName(level) }} ({{ level.targetAgeMin }} - {{ level.targetAgeMax }} ans)
                </option>
              </select>
            </div>

            <div class="col-12 pt-2 border-t mt-3">
              <h4 class="text-xs font-bold text-brand-600 uppercase tracking-wider mb-2">👨‍👩‍👦 Responsable Légal / Parent</h4>
            </div>

            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Nom & Prénom du Parent *</label>
              <input 
                v-model="form.parentFullName" 
                type="text" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
                placeholder="ex: Karim Benali" 
              />
            </div>

            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Téléphone de contact *</label>
              <input 
                v-model="form.parentPhone" 
                type="tel" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
                placeholder="+352 691 123 456" 
              />
            </div>

            <div class="col-12">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Email du responsable légal *</label>
              <input 
                v-model="form.parentEmail" 
                type="email" 
                required 
                class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" 
                placeholder="parent@email.com" 
              />
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="submitting" 
            class="default-btn w-100 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all mt-4 disabled:opacity-50"
          >
            <span v-if="submitting">
              <i class="ri-loader-4-line animate-spin me-1"></i> Enregistrement en cours...
            </span>
            <span v-else>
              <i class="ri-send-plane-fill me-1"></i> Soumettre le dossier d'inscription
            </span>
          </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-xs text-gray-500">
          Déjà inscrit ? 
          <router-link to="/login" class="text-brand-600 font-bold hover:underline">Se connecter</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const { locale } = useI18n()

const courseLevels = ref([])
const submitting = ref(false)
const successSubmitted = ref(false)
const submittedStudentName = ref('')
const submittedLevel = ref('')

const form = ref({
  studentFirstName: '',
  studentLastName: '',
  dateOfBirth: '2018-05-12',
  desiredLevel: '',
  parentFullName: '',
  parentEmail: '',
  parentPhone: ''
})

function getLevelName(level) {
  if (!level) return ''
  const currentLoc = locale.value || 'fr'
  if (level.translations && level.translations[currentLoc] && level.translations[currentLoc].name) {
    return level.translations[currentLoc].name
  }
  if (level.translations && level.translations.fr && level.translations.fr.name) {
    return level.translations.fr.name
  }
  return level.name || ''
}

onMounted(async () => {
  try {
    const res = await apiClient.get('/public/course-levels')
    if (Array.isArray(res.data) && res.data.length > 0) {
      courseLevels.value = res.data.sort((a, b) => a.id - b.id)
      form.value.desiredLevel = getLevelName(courseLevels.value[0])
    }
  } catch (err) {
    console.error('Erreur lors de la récupération des niveaux depuis la BBD', err)
    courseLevels.value = [
      { id: 1, name: 'Éveil (4 - 6 ans)', targetAgeMin: 4, targetAgeMax: 6 },
      { id: 2, name: 'Débutant (7 - 9 ans)', targetAgeMin: 7, targetAgeMax: 9 },
      { id: 3, name: 'Intermédiaire (10 - 12 ans)', targetAgeMin: 10, targetAgeMax: 12 },
      { id: 4, name: 'Coran & Tajwid (Tous niveaux)', targetAgeMin: 6, targetAgeMax: 16 }
    ]
    form.value.desiredLevel = courseLevels.value[0].name
  }
})

async function submitRegistration() {
  if (!form.value.desiredLevel) {
    showErrorAlert('Niveau requis', 'Veuillez sélectionner le niveau souhaité.')
    return
  }

  submitting.value = true
  try {
    const payload = {
      studentFirstName: form.value.studentFirstName.trim(),
      studentLastName: form.value.studentLastName.trim(),
      dateOfBirth: form.value.dateOfBirth,
      desiredLevel: form.value.desiredLevel,
      assignedGroup: form.value.desiredLevel,
      parentFullName: form.value.parentFullName.trim(),
      parentEmail: form.value.parentEmail.trim(),
      parentPhone: form.value.parentPhone.trim(),
      student: {
        firstName: form.value.studentFirstName.trim(),
        lastName: form.value.studentLastName.trim(),
        dateOfBirth: form.value.dateOfBirth,
        assignedGroup: form.value.desiredLevel
      },
      parent: {
        fullName: form.value.parentFullName.trim(),
        email: form.value.parentEmail.trim(),
        phone: form.value.parentPhone.trim()
      }
    }

    await apiClient.post('/enrollments', payload)

    submittedStudentName.value = `${form.value.studentFirstName} ${form.value.studentLastName}`
    submittedLevel.value = form.value.desiredLevel
    successSubmitted.value = true

    showSuccessAlert(
      'Inscription Transmise ! 🎉',
      `L'élève <strong>${submittedStudentName.value}</strong> pour le niveau <strong>${submittedLevel.value}</strong> a été inscrit avec succès dans la base de données !`
    )
  } catch (err) {
    console.error('Erreur enregistrement inscription:', err)
    const serverMessage = err.response?.data?.error || err.response?.data?.details || err.message || 'Une erreur s\'est produite lors de l\'enregistrement. Veuillez réessayer.'
    showErrorAlert(
      'Erreur d\'inscription',
      serverMessage
    )
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  form.value = {
    studentFirstName: '',
    studentLastName: '',
    dateOfBirth: '2018-05-12',
    desiredLevel: courseLevels.value.length > 0 ? getLevelName(courseLevels.value[0]) : '',
    parentFullName: '',
    parentEmail: '',
    parentPhone: ''
  }
  successSubmitted.value = false
}
</script>
