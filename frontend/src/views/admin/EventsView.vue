<template>
  <div class="space-y-6">
    <!-- Header with Title and Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Événements & Activités</h1>
        <p class="text-xs text-gray-500">Planifiez les événements scolaires et gérez les catégories en base de données</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="activeTab === 'events'"
          @click="openEventModal()"
          class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2"
        >
          <span>➕</span> Nouvel Événement
        </button>
        <button
          v-if="activeTab === 'categories'"
          @click="openCategoryModal()"
          class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2"
        >
          <span>➕</span> Nouvelle Catégorie
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 gap-4">
      <button
        @click="activeTab = 'events'"
        class="pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2"
        :class="activeTab === 'events' ? 'border-brand-600 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        <span>📅</span> Événements Scolaires ({{ events.length }})
      </button>
      <button
        @click="activeTab = 'categories'"
        class="pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2"
        :class="activeTab === 'categories' ? 'border-brand-600 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        <span>🏷️</span> Catégories Configurables ({{ categories.length }})
      </button>
    </div>

    <!-- TAB 1: EVENTS LIST -->
    <div v-if="activeTab === 'events'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Événement</th>
              <th class="py-3 px-4">Catégorie</th>
              <th class="py-3 px-4">Date & Heure</th>
              <th class="py-3 px-4">Lieu</th>
              <th class="py-3 px-4">Capacité</th>
              <th class="py-3 px-4">Statut</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-if="events.length === 0">
              <td colspan="7" class="text-center py-8 text-gray-400">
                Aucun événement enregistré. Cliquez sur "Nouvel Événement" pour commencer.
              </td>
            </tr>
            <tr v-for="item in events" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4">
                <div class="font-bold text-gray-900 dark:text-white">{{ item.title }}</div>
                <div class="text-[11px] text-gray-500 line-clamp-1 max-w-xs">{{ item.description }}</div>
              </td>
              <td class="py-3.5 px-4">
                <span
                  v-if="item.category"
                  class="px-2.5 py-1 rounded-full text-white font-bold text-[10px]"
                  :style="{ backgroundColor: item.category.color }"
                >
                  {{ item.category.name }}
                </span>
                <span v-else class="text-gray-400 italic">Sans catégorie</span>
              </td>
              <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300">
                <div>{{ item.eventDateFormatted }}</div>
                <div v-if="item.endDateFormatted" class="text-[10px] text-gray-400">Fin : {{ item.endDateFormatted }}</div>
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-400">
                {{ item.location }}
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-400">
                {{ item.capacity ? item.capacity + ' pers.' : 'Illimité' }}
              </td>
              <td class="py-3.5 px-4">
                <button
                  @click="toggleEventStatus(item)"
                  class="px-2.5 py-1 rounded-full font-bold text-[10px] transition-all"
                  :class="item.isPublished ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'"
                >
                  {{ item.isPublished ? '✓ Publié' : '⏳ Brouillon' }}
                </button>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button @click="openEventModal(item)" class="text-brand-600 hover:text-brand-700 font-bold">Éditer</button>
                <button @click="deleteEvent(item)" class="text-red-500 hover:text-red-700 font-bold ml-2">Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: CATEGORIES CRUD -->
    <div v-if="activeTab === 'categories'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">Couleur</th>
              <th class="py-3 px-4">Nom de la Catégorie</th>
              <th class="py-3 px-4">Slug URL</th>
              <th class="py-3 px-4">Description</th>
              <th class="py-3 px-4">Événements</th>
              <th class="py-3 px-4">Statut</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-if="categories.length === 0">
              <td colspan="7" class="text-center py-8 text-gray-400">
                Aucune catégorie enregistrée. Cliquez sur "Nouvelle Catégorie".
              </td>
            </tr>
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4">
                <div class="w-6 h-6 rounded-lg shadow-sm border border-gray-200" :style="{ backgroundColor: cat.color }"></div>
              </td>
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                {{ cat.name }}
              </td>
              <td class="py-3.5 px-4 text-gray-500 font-mono text-[11px]">
                {{ cat.slug }}
              </td>
              <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate">
                {{ cat.description || '-' }}
              </td>
              <td class="py-3.5 px-4 font-bold text-gray-700 dark:text-gray-300">
                {{ cat.eventsCount }}
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold" :class="cat.isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                  {{ cat.isActive ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button @click="openCategoryModal(cat)" class="text-brand-600 hover:text-brand-700 font-bold">Éditer</button>
                <button @click="deleteCategory(cat)" class="text-red-500 hover:text-red-700 font-bold ml-2">Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: ADD / EDIT EVENT -->
    <div v-if="showEventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ isEditingEvent ? 'Modifier l\'Événement' : 'Créer un Nouvel Événement' }}
          </h3>
          <button @click="showEventModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
        </div>

        <form @submit.prevent="saveEvent" class="space-y-4 text-xs">
          <!-- Language Tabs for Multilingual Title & Description -->
          <div class="border rounded-2xl p-3 bg-gray-50/70 dark:bg-gray-750 border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
              <span class="font-bold text-gray-800 dark:text-gray-200 text-xs flex items-center gap-1.5">
                🌐 Contenu multilingue
              </span>
              <div class="flex items-center gap-1">
                <button 
                  type="button" 
                  @click="activeLangTab = 'fr'"
                  class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                  :class="activeLangTab === 'fr' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100'"
                >
                  🇫🇷 Français (Défaut)
                </button>
                <button 
                  type="button" 
                  @click="activeLangTab = 'ar'"
                  class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                  :class="activeLangTab === 'ar' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100'"
                >
                  🇦🇪 العربية
                </button>
                <button 
                  type="button" 
                  @click="activeLangTab = 'en'"
                  class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                  :class="activeLangTab === 'en' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100'"
                >
                  🇬🇧 English
                </button>
              </div>
            </div>

            <!-- French (Base) -->
            <div v-show="activeLangTab === 'fr'" class="space-y-3">
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Titre de l'Événement (FR) *</label>
                <input v-model="eventForm.title" required type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Ex: Célébration fin d'année Coran" />
              </div>
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Description détaillée (FR) *</label>
                <textarea v-model="eventForm.description" required rows="4" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Détails du programme, horaires, public cible en français..."></textarea>
              </div>
            </div>

            <!-- Arabic -->
            <div v-show="activeLangTab === 'ar'" class="space-y-3" dir="rtl">
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1 text-right">عنوان الفعالية (العربية)</label>
                <input v-model="eventForm.translations.ar.title" type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs text-right" placeholder="مثال: حفل ختام السنة القرآنية وتكريم الحفظة" />
              </div>
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1 text-right">الوصف والتفاصيل (العربية)</label>
                <textarea v-model="eventForm.translations.ar.description" rows="4" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs text-right" placeholder="تفاصيل البرنامج، التوقيت، الفئات المستهدفة..."></textarea>
              </div>
            </div>

            <!-- English -->
            <div v-show="activeLangTab === 'en'" class="space-y-3">
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Event Title (EN)</label>
                <input v-model="eventForm.translations.en.title" type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Ex: Quran Year-End Celebration" />
              </div>
              <div>
                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Detailed Description (EN)</label>
                <textarea v-model="eventForm.translations.en.description" rows="4" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Event details, schedule, target audience in English..."></textarea>
              </div>
            </div>
          </div>

          <!-- Category Selection -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
            <select v-model="eventForm.categoryId" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs">
              <option :value="null">-- Sélectionner une catégorie --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Dates: Start & End -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Date & Heure Début *</label>
              <input v-model="eventForm.eventDate" required type="datetime-local" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" />
            </div>
            <div>
              <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Date & Heure Fin (Optionnel)</label>
              <input v-model="eventForm.endDate" type="datetime-local" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" />
            </div>
          </div>

          <!-- Location & Capacity -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Lieu</label>
              <input v-model="eventForm.location" type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Ex: Centre Maryam / LJM Luxembourg" />
            </div>
            <div>
              <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Capacité (Nb. places)</label>
              <input v-model.number="eventForm.capacity" type="number" min="1" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Ex: 100" />
            </div>
          </div>

          <!-- Image URL -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">URL Image (Optionnel)</label>
            <input v-model="eventForm.imageUrl" type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="/assets/images/about/about-group-image.png" />
          </div>

          <!-- Status Published -->
          <div class="flex items-center gap-2 pt-2">
            <input v-model="eventForm.isPublished" id="isPublished" type="checkbox" class="w-4 h-4 text-brand-600 rounded" />
            <label for="isPublished" class="font-bold text-gray-700 dark:text-gray-300">Publier immédiatement sur le site web</label>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button type="button" @click="showEventModal = false" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition-all">
              Annuler
            </button>
            <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow transition-all">
              {{ isEditingEvent ? 'Mettre à jour' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ADD / EDIT CATEGORY -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full p-6 border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center justify-between border-b pb-3 border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ isEditingCategory ? 'Modifier la Catégorie' : 'Nouvelle Catégorie' }}
          </h3>
          <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
        </div>

        <form @submit.prevent="saveCategory" class="space-y-4 text-xs">
          <!-- Name -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Nom de la Catégorie *</label>
            <input v-model="categoryForm.name" required type="text" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Ex: Stages de Vacances" />
          </div>

          <!-- Color Picker with presets -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Couleur du Badge</label>
            <div class="flex items-center gap-2 mb-2">
              <input v-model="categoryForm.color" type="color" class="w-10 h-10 p-1 border rounded-xl cursor-pointer" />
              <input v-model="categoryForm.color" type="text" class="flex-1 px-3 py-2 border rounded-xl font-mono text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>
            <!-- Preset Palette -->
            <div class="flex gap-2">
              <button
                v-for="color in presetColors"
                :key="color"
                type="button"
                @click="categoryForm.color = color"
                class="w-6 h-6 rounded-full border border-white shadow-sm"
                :style="{ backgroundColor: color }"
              ></button>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Description (Optionnel)</label>
            <textarea v-model="categoryForm.description" rows="2" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" placeholder="Brève note explicative..."></textarea>
          </div>

          <!-- Order -->
          <div>
            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Ordre d'affichage</label>
            <input v-model.number="categoryForm.position" type="number" min="0" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs" />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button type="button" @click="showCategoryModal = false" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition-all">
              Annuler
            </button>
            <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow transition-all">
              {{ isEditingCategory ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import apiClient from '../../plugins/axios'

const activeTab = ref('events')

const events = ref([])
const categories = ref([])

const presetColors = ['#8CC63F', '#5a00a1', '#f5cd00', '#0284c7', '#f21e09', '#10b981', '#ec4899', '#6366f1']

const activeLangTab = ref('fr')

// Modal States
const showEventModal = ref(false)
const isEditingEvent = ref(false)
const editingEventId = ref(null)
const eventForm = reactive({
  title: '',
  categoryId: null,
  eventDate: '',
  endDate: '',
  location: 'Centre Maryam / LJM Luxembourg',
  capacity: null,
  imageUrl: '',
  description: '',
  isPublished: true,
  translations: {
    ar: { title: '', description: '' },
    en: { title: '', description: '' }
  }
})

const showCategoryModal = ref(false)
const isEditingCategory = ref(false)
const editingCategoryId = ref(null)
const categoryForm = reactive({
  name: '',
  color: '#8CC63F',
  description: '',
  position: 0
})

async function fetchCategories() {
  try {
    const res = await apiClient.get('/admin/events/categories')
    categories.value = res.data || []
  } catch (err) {
    console.error('Erreur chargement catégories :', err)
  }
}

async function fetchEvents() {
  try {
    const res = await apiClient.get('/admin/events')
    events.value = res.data || []
  } catch (err) {
    console.error('Erreur chargement événements :', err)
  }
}

// Event Actions
function openEventModal(event = null) {
  activeLangTab.value = 'fr'
  if (event) {
    isEditingEvent.value = true
    editingEventId.value = event.id
    eventForm.title = event.title
    eventForm.categoryId = event.category ? event.category.id : null
    eventForm.eventDate = event.eventDate
    eventForm.endDate = event.endDate || ''
    eventForm.location = event.location
    eventForm.capacity = event.capacity
    eventForm.imageUrl = event.imageUrl || ''
    eventForm.description = event.description
    eventForm.isPublished = event.isPublished
    eventForm.translations = {
      ar: {
        title: event.translations?.ar?.title || '',
        description: event.translations?.ar?.description || ''
      },
      en: {
        title: event.translations?.en?.title || '',
        description: event.translations?.en?.description || ''
      }
    }
  } else {
    isEditingEvent.value = false
    editingEventId.value = null
    eventForm.title = ''
    eventForm.categoryId = categories.value.length > 0 ? categories.value[0].id : null
    eventForm.eventDate = ''
    eventForm.endDate = ''
    eventForm.location = 'Centre Maryam / LJM Luxembourg'
    eventForm.capacity = null
    eventForm.imageUrl = ''
    eventForm.description = ''
    eventForm.isPublished = true
    eventForm.translations = {
      ar: { title: '', description: '' },
      en: { title: '', description: '' }
    }
  }
  showEventModal.value = true
}

async function saveEvent() {
  try {
    if (isEditingEvent.value) {
      await apiClient.put(`/admin/events/${editingEventId.value}`, eventForm)
    } else {
      await apiClient.post('/admin/events', eventForm)
    }
    showEventModal.value = false
    await fetchEvents()
    await fetchCategories()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur lors de l\'enregistrement de l\'événement')
  }
}

async function toggleEventStatus(event) {
  try {
    const res = await apiClient.patch(`/admin/events/${event.id}/toggle-status`)
    event.isPublished = res.data.isPublished
  } catch (err) {
    alert('Erreur lors du changement de statut')
  }
}

async function deleteEvent(event) {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer l'événement "${event.title}" ?`)) return
  try {
    await apiClient.delete(`/admin/events/${event.id}`)
    await fetchEvents()
    await fetchCategories()
  } catch (err) {
    alert('Erreur lors de la suppression')
  }
}

// Category Actions
function openCategoryModal(cat = null) {
  if (cat) {
    isEditingCategory.value = true
    editingCategoryId.value = cat.id
    categoryForm.name = cat.name
    categoryForm.color = cat.color
    categoryForm.description = cat.description || ''
    categoryForm.position = cat.position || 0
  } else {
    isEditingCategory.value = false
    editingCategoryId.value = null
    categoryForm.name = ''
    categoryForm.color = '#8CC63F'
    categoryForm.description = ''
    categoryForm.position = categories.value.length + 1
  }
  showCategoryModal.value = true
}

async function saveCategory() {
  try {
    if (isEditingCategory.value) {
      await apiClient.put(`/admin/events/categories/${editingCategoryId.value}`, categoryForm)
    } else {
      await apiClient.post('/admin/events/categories', categoryForm)
    }
    showCategoryModal.value = false
    await fetchCategories()
    await fetchEvents()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur lors de l\'enregistrement de la catégorie')
  }
}

async function deleteCategory(cat) {
  if (!confirm(`Supprimer la catégorie "${cat.name}" ? Les événements rattachés perdront leur catégorie.`)) return
  try {
    await apiClient.delete(`/admin/events/categories/${cat.id}`)
    await fetchCategories()
    await fetchEvents()
  } catch (err) {
    alert('Erreur lors de la suppression')
  }
}

onMounted(() => {
  fetchCategories()
  fetchEvents()
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
