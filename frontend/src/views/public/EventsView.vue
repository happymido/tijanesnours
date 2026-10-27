<template>
  <div class="events-view pb-5">
    <!-- Inner Banner -->
    <div class="py-5 bg-gradient-to-r from-brand-800 to-brand-900 text-white text-center">
      <div class="container py-4">
        <h1 class="text-3xl font-extrabold mb-2">{{ t('events.title') }}</h1>
        <p class="text-gray-200 text-sm max-w-xl mx-auto">
          {{ t('events.subtitle') }}
        </p>
      </div>
    </div>

    <div class="container py-5">
      <!-- Categories Filter Tabs -->
      <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-5">
        <button
          @click="selectedCategory = null"
          class="btn filter-pill-btn px-4 py-2 text-xs font-bold rounded-pill transition-all"
          :class="selectedCategory === null ? 'active-filter' : 'bg-white text-gray-700 border'"
        >
          {{ t('events.filter_all') }}
        </button>

        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="selectedCategory = cat.id"
          class="btn filter-pill-btn px-4 py-2 text-xs font-bold rounded-pill transition-all d-flex align-items-center gap-2"
          :class="selectedCategory === cat.id ? 'active-filter' : 'bg-white text-gray-700 border'"
        >
          <span class="category-dot" :style="{ backgroundColor: cat.color }"></span>
          <span>{{ getCategoryName(cat) }}</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border text-brand-600" role="status">
          <span class="visually-hidden">{{ t('common.loading') }}</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredEvents.length === 0" class="text-center py-5 bg-white rounded-3xl border shadow-sm p-5 max-w-lg mx-auto">
        <div class="w-16 h-16 bg-brand-50 text-brand-600 rounded-full d-flex align-items-center justify-content-center mx-auto mb-3 text-2xl">
          <i class="ri-calendar-event-line"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-800 mb-1">Aucun événement à afficher</h4>
        <p class="text-xs text-gray-500 mb-0">{{ t('events.no_events') }}</p>
      </div>

      <!-- Events Grid -->
      <div v-else class="row g-4">
        <div v-for="event in filteredEvents" :key="event.id" class="col-lg-4 col-md-6">
          <div class="event-card h-100 bg-white rounded-3xl overflow-hidden border shadow-sm transition-all hover:shadow-md d-flex flex-column">
            <!-- Event Image (if any) or Styled Banner Header -->
            <div class="event-image-wrap position-relative">
              <img
                v-if="event.imageUrl"
                :src="event.imageUrl"
                :alt="event.title"
                class="w-100 object-cover"
                style="height: 180px;"
              />
              <div v-else class="event-placeholder-header d-flex align-items-center justify-content-center text-white" :style="{ background: `linear-gradient(135deg, ${event.category?.color || '#8CC63F'} 0%, #2b5329 100%)` }" style="height: 140px;">
                <i class="ri-calendar-todo-fill text-4xl opacity-80"></i>
              </div>

              <!-- Category Badge -->
              <div v-if="event.category" class="position-absolute top-3 end-3">
                <span
                  class="badge px-3 py-1.5 text-xs font-bold rounded-pill text-white shadow-sm"
                  :style="{ backgroundColor: event.category.color }"
                >
                  {{ getCategoryName(event.category) }}
                </span>
              </div>

              <!-- Date Badge Floating on the card -->
              <div class="event-date-badge shadow">
                <span class="day">{{ getDay(event.eventDate) }}</span>
                <span class="month">{{ getMonth(event.eventDate) }}</span>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-4 flex-grow-1 d-flex flex-column justify-content-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 leading-snug">
                  {{ getEventTitle(event) }}
                </h3>
                <p class="text-xs text-gray-600 line-clamp-3 mb-4 leading-relaxed">
                  {{ getEventDescription(event) }}
                </p>
              </div>

              <!-- Event Meta Details -->
              <div class="border-top pt-3 space-y-2 text-xs text-gray-600">
                <div class="d-flex align-items-center gap-2">
                  <i class="ri-time-line text-brand-600 font-bold"></i>
                  <span>{{ formatTimeRange(event.eventDate, event.endDate) }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <i class="ri-map-pin-line text-brand-600 font-bold"></i>
                  <span class="text-truncate">{{ event.location }}</span>
                </div>
                <div v-if="event.capacity" class="d-flex align-items-center gap-2 text-gray-500">
                  <i class="ri-user-star-line text-brand-600 font-bold"></i>
                  <span>{{ event.capacity }} participants max</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import apiClient from '../../plugins/axios'

const { t, locale } = useI18n()

const categories = ref([])
const events = ref([])
const selectedCategory = ref(null)
const isLoading = ref(true)

const filteredEvents = computed(() => {
  if (selectedCategory.value === null) {
    return events.value
  }
  return events.value.filter(e => e.category && e.category.id === selectedCategory.value)
})

async function fetchData() {
  isLoading.value = true
  try {
    const [catRes, eventsRes] = await Promise.all([
      apiClient.get('/public/event-categories'),
      apiClient.get('/public/events')
    ])
    categories.value = catRes.data || []
    events.value = eventsRes.data || []
  } catch (err) {
    console.error('Erreur lors du chargement des événements :', err)
  } finally {
    isLoading.value = false
  }
}

function getDay(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.getDate().toString().padStart(2, '0')
}

function getMonth(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const loc = locale.value === 'ar' ? 'ar-TN' : (locale.value === 'en' ? 'en-US' : 'fr-FR')
  return d.toLocaleDateString(loc, { month: 'short' }).toUpperCase()
}

function getEventTitle(event) {
  if (!event) return ''
  const loc = locale.value
  if (loc !== 'fr' && event.translations && event.translations[loc] && event.translations[loc].title) {
    return event.translations[loc].title
  }
  return event.title
}

function getEventDescription(event) {
  if (!event) return ''
  const loc = locale.value
  if (loc !== 'fr' && event.translations && event.translations[loc] && event.translations[loc].description) {
    return event.translations[loc].description
  }
  return event.description
}

function getCategoryName(category) {
  if (!category) return ''
  const loc = locale.value
  if (loc !== 'fr' && category.translations && category.translations[loc] && category.translations[loc].name) {
    return category.translations[loc].name
  }
  return category.name
}

function formatTimeRange(startStr, endStr) {
  if (!startStr) return ''
  const start = new Date(startStr)
  const loc = locale.value === 'ar' ? 'ar-TN' : (locale.value === 'en' ? 'en-US' : 'fr-FR')
  const startTime = start.toLocaleTimeString(loc, { hour: '2-digit', minute: '2-digit' })

  if (!endStr) return startTime

  const end = new Date(endStr)
  const endTime = end.toLocaleTimeString(loc, { hour: '2-digit', minute: '2-digit' })
  return `${startTime} - ${endTime}`
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.events-view {
  min-height: 80vh;
}

.category-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.filter-pill-btn {
  border-radius: 30px !important;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-pill-btn:hover {
  transform: translateY(-1px);
}

.active-filter {
  background-color: #8CC63F !important;
  color: #ffffff !important;
  border-color: #8CC63F !important;
  box-shadow: 0 4px 12px rgba(140, 198, 63, 0.35) !important;
}

.event-image-wrap {
  position: relative;
}

.event-date-badge {
  position: absolute;
  bottom: -15px;
  left: 20px;
  width: 52px;
  height: 52px;
  background-color: #ffffff;
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px solid #8CC63F;
  z-index: 2;
}

[dir="rtl"] .event-date-badge {
  left: auto;
  right: 20px;
}

.event-date-badge .day {
  font-size: 18px;
  font-weight: 800;
  line-height: 1;
  color: #111827;
}

.event-date-badge .month {
  font-size: 10px;
  font-weight: 800;
  color: #8CC63F;
  text-transform: uppercase;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
