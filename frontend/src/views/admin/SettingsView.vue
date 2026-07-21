<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres Généraux de l'École</h1>
      <p class="text-xs text-gray-500">Configuration générale de l'établissement Tijanes Nours Luxembourg (RCS F12999)</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8 max-w-2xl space-y-6">
      <div class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom Officiel de l'Association</label>
          <input type="text" value="École Tijanes Nours ASBL" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
        </div>
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Numéro RCS Luxembourg</label>
          <input type="text" value="RCS F12999" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
        </div>
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Adresse Principale</label>
          <input type="text" value="Centre Maryam / LJM Luxembourg" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
        </div>
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Officiel de Contact</label>
          <input type="email" value="contact@tijanesnours.lu" class="w-full px-4 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
        </div>
      </div>

      <button class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow">
        Enregistrer les Paramètres
      </button>
    </div>
  </div>
</template>

<script setup>
</script>
