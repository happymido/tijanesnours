<template>
  <div class="relative text-xs font-medium" ref="dropdownRef">
    <!-- Selected Item Box (Select2 Control Header) -->
    <button
      type="button"
      @click="toggleDropdown"
      class="w-full px-3.5 py-2.5 rounded-xl border bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-left flex justify-between items-center focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm transition-all"
    >
      <span v-if="selectedLabel" class="font-semibold text-gray-900 dark:text-white truncate">
        {{ selectedLabel }}
      </span>
      <span v-else class="text-gray-400 font-normal">
        {{ placeholder }}
      </span>
      <span class="text-gray-400 ml-2 text-[10px]">▼</span>
    </button>

    <!-- Dropdown Menu Overlay with Search Input -->
    <div
      v-if="isOpen"
      class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 py-2 space-y-2 max-h-60 overflow-hidden flex flex-col"
    >
      <!-- Search Input Box (Select2 Search) -->
      <div class="px-3 pt-1 pb-2 border-b border-gray-100 dark:border-gray-700">
        <input
          ref="searchInput"
          v-model="searchQuery"
          type="text"
          placeholder="🔍 Rechercher..."
          class="w-full px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500"
        />
      </div>

      <!-- Options List -->
      <div class="overflow-y-auto flex-1 divide-y divide-gray-50 dark:divide-gray-700/50">
        <div
          v-for="opt in filteredOptions"
          :key="opt.value"
          @click="selectOption(opt)"
          :class="modelValue === opt.value ? 'bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-200'"
          class="px-3 py-2 cursor-pointer transition-colors flex items-center justify-between"
        >
          <span>{{ opt.label }}</span>
          <span v-if="modelValue === opt.value" class="text-brand-600 dark:text-gold-400 font-extrabold text-xs">✓</span>
        </div>

        <div v-if="filteredOptions.length === 0" class="px-3 py-3 text-center text-gray-400 text-xs italic">
          Aucun résultat trouvé
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    default: () => [] // array of { value: any, label: string }
  },
  placeholder: {
    type: String,
    default: '-- Sélectionner --'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)
const dropdownRef = ref(null)

const selectedLabel = computed(() => {
  const found = props.options.find(o => o.value === props.modelValue)
  return found ? found.label : ''
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options
  const q = searchQuery.value.toLowerCase()
  return props.options.filter(o => o.label.toLowerCase().includes(q))
})

function toggleDropdown() {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => {
      if (searchInput.value) searchInput.value.focus()
    })
  }
}

function selectOption(opt) {
  emit('update:modelValue', opt.value)
  emit('change', opt)
  isOpen.value = false
  searchQuery.value = ''
}

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
