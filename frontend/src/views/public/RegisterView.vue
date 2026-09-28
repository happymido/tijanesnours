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
        
        <form @submit.prevent class="space-y-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Nom de l'élève</label>
              <input type="text" class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" placeholder="ex: Benali" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Prénom de l'élève</label>
              <input type="text" class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" placeholder="ex: Youssef" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Date de naissance</label>
              <input type="date" class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Niveau souhaité</label>
              <select class="form-select px-4 py-2.5 rounded-xl border-gray-300 text-sm">
                <option>Éveil (4 - 6 ans)</option>
                <option>Débutant (7 - 9 ans)</option>
                <option>Intermédiaire (10 - 12 ans)</option>
                <option>Coran & Tajwid (Tous niveaux)</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Email du responsable légal</label>
              <input type="email" class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" placeholder="parent@email.com" />
            </div>
            <div class="col-12">
              <label class="form-label text-xs font-bold text-gray-700 mb-1">Téléphone de contact</label>
              <input type="tel" class="form-control px-4 py-2.5 rounded-xl border-gray-300 text-sm" placeholder="+352 691 123 456" />
            </div>
          </div>

          <button type="submit" class="default-btn w-100 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all mt-4">
            <i class="ri-send-plane-fill me-1"></i> Soumettre le dossier d'inscription
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
