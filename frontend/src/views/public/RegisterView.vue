<template>
  <div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-700">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Formulaire d'Inscription Scolaire 2026-2027</h1>
      <p class="text-sm text-gray-500 mb-8">Veuillez compléter le dossier d'inscription de votre enfant.</p>
      
      <!-- Multi-step wizard placeholder -->
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom de l'élève</label>
            <input type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm" placeholder="ex: Benali" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Prénom de l'élève</label>
            <input type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm" placeholder="ex: Youssef" />
          </div>
        </div>
        <button class="w-full py-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all">
          Continuer l'inscription
        </button>
      </div>
    </div>
  </div>
</template>
