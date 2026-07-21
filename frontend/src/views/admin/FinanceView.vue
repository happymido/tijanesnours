<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion Financière & Tarification</h1>
        <p class="text-xs text-gray-500">Inscriptions, Échéanciers, Remboursements et Relances automatiques</p>
      </div>
      <div class="flex gap-3">
        <button class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
          📢 Lancer Relances Automatiques
        </button>
      </div>
    </div>

    <!-- Financial Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-400 uppercase">Tarif Annuel de Base</span>
        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">450.00 € / an</p>
        <p class="text-xs text-gray-500 mt-1">Hors options manuels & accessoires</p>
      </div>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-400 uppercase">Réductions Multi-Enfants</span>
        <p class="text-xl font-bold text-emerald-600 mt-1">-15% 2ème enfant | -25% 3ème+</p>
        <p class="text-xs text-gray-500 mt-1">Appliqué automatiquement par l'ERP</p>
      </div>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold text-gray-400 uppercase">Impayés & En Retard</span>
        <p class="text-3xl font-extrabold text-amber-600 mt-1">4 200.00 €</p>
        <p class="text-xs text-amber-600 font-bold mt-1">14 relances envoyées par mail</p>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
