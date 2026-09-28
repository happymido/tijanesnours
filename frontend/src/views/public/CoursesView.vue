<template>
  <div class="courses-view pb-5">
    <!-- Inner Banner -->
    <div class="py-5 bg-gradient-to-r from-brand-800 to-brand-900 text-white text-center">
      <div class="container py-4">
        <h1 class="text-3xl font-extrabold mb-2">Nos Programmes & Cours (4 à 16 ans)</h1>
        <p class="text-gray-200 text-sm max-w-xl mx-auto">
          Découvrez une pédagogie moderne et structurée pour accompagner votre enfant dans l'apprentissage de la langue arabe et du Saint Coran.
        </p>
      </div>
    </div>

    <!-- Courses Content -->
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="service-card border shadow-sm rounded-2xl overflow-hidden bg-white h-100">
            <div class="image-style1">
              <img class="service img-fluid w-100" src="/assets/images/service/service-image-1.jpg" alt="Langue Arabe">
            </div>
            <div class="service-card-body p-4">
              <span class="badge bg-brand-100 text-brand-700 font-bold text-xs mb-2">Éveil & Débutants</span>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Langue Arabe</h3>
              <p class="text-xs text-gray-600 leading-relaxed mb-4">
                Apprentissage ludique des lettres, vocalisation, vocabulaire du quotidien et initiation à la lecture courante.
              </p>
              <router-link to="/register" class="default-btn py-2 px-3 text-xs w-100 text-center">
                S'inscrire à ce cours
              </router-link>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="service-card border shadow-sm rounded-2xl overflow-hidden bg-white h-100">
            <div class="image-style1">
              <img class="service img-fluid w-100" src="/assets/images/service/service.jpg" alt="Coran & Tajwid">
            </div>
            <div class="service-card-body p-4">
              <span class="badge bg-gold-100 text-gold-700 font-bold text-xs mb-2">Mémorisation & Récitation</span>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Saint Coran & Tajwid</h3>
              <p class="text-xs text-gray-600 leading-relaxed mb-4">
                Règles fondamentales de Tajwid, correction de la prononciation et mémorisation adaptée à chaque profil.
              </p>
              <router-link to="/register" class="default-btn py-2 px-3 text-xs w-100 text-center">
                S'inscrire à ce cours
              </router-link>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="service-card border shadow-sm rounded-2xl overflow-hidden bg-white h-100">
            <div class="image-style1">
              <img class="service img-fluid w-100" src="/assets/images/service/service-image-2.jpg" alt="Éducation Éthique">
            </div>
            <div class="service-card-body p-4">
              <span class="badge bg-emerald-100 text-emerald-700 font-bold text-xs mb-2">Valeurs & Comportement</span>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Éducation Éthique & Civique</h3>
              <p class="text-xs text-gray-600 leading-relaxed mb-4">
                Transmission des valeurs universelles d'entraide, de respect, d'honnêteté et du bien vivre ensemble au Luxembourg.
              </p>
              <router-link to="/register" class="default-btn py-2 px-3 text-xs w-100 text-center">
                S'inscrire à ce cours
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
