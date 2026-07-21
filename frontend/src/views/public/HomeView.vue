<template>
  <div>
    <!-- Hero Banner -->
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50 to-white dark:from-gray-800 dark:to-gray-900 py-20 lg:py-28">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-6 text-center lg:text-left rtl:lg:text-right">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300">
              🌟 Établissement Scolaire Spécialisé au Luxembourg (RCS F12999)
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">
              {{ t('hero.title') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-xl">
              {{ t('hero.subtitle') }}
            </p>
            <div class="flex flex-wrap gap-4 justify-center lg:justify-start rtl:lg:justify-end pt-4">
              <router-link
                to="/register"
                class="px-6 py-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg shadow-brand-500/30 transition-all hover:scale-[1.02]"
              >
                {{ t('hero.cta_register') }}
              </router-link>
              <router-link
                to="/courses"
                class="px-6 py-3.5 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-sm transition-all"
              >
                {{ t('hero.cta_courses') }}
              </router-link>
            </div>
          </div>

          <!-- Feature Graphic / Hero Card -->
          <div class="relative flex justify-center">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl border border-gray-100 dark:border-gray-700 relative">
              <div class="absolute -top-4 -right-4 w-16 h-16 bg-gold-400 rounded-2xl rotate-12 flex items-center justify-center text-2xl shadow-lg">
                📖
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Programme Pédagogique 4-16 ans</h3>
              <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <li class="flex items-center gap-3">
                  <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                  Apprentissage ludique & progressif de l'Arabe
                </li>
                <li class="flex items-center gap-3">
                  <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                  Récitation & Mémorisation du Coran (Tajwid)
                </li>
                <li class="flex items-center gap-3">
                  <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                  Professeurs qualifiés & suivi personnalisé
                </li>
                <li class="flex items-center gap-3">
                  <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                  Inscriptions & Paiement sécurisé en ligne (Stripe/SEPA/Virement)
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
