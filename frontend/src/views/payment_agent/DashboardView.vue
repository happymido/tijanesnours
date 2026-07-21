<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md">
    <h1 class="text-2xl font-bold mb-4 text-emerald-600">Tableau de bord Agent Comptable</h1>
    <p class="text-sm text-gray-500 mb-6">Gestion des encaissements manuels, remises SEPA et rapprochements de virements ISO 11649.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200">
        <span class="text-xs font-bold text-emerald-700">Encaissements Manuels</span>
        <p class="text-2xl font-extrabold text-emerald-800 mt-2">Saisie & Reçus</p>
      </div>
      <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200">
        <span class="text-xs font-bold text-blue-700">Prélèvements SEPA</span>
        <p class="text-2xl font-extrabold text-blue-800 mt-2">Batch Pain.008</p>
      </div>
      <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200">
        <span class="text-xs font-bold text-amber-700">Rapprochement RF</span>
        <p class="text-2xl font-extrabold text-amber-800 mt-2">Ref ISO 11649</p>
      </div>
    </div>
  </div>
</template>
