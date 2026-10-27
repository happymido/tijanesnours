<template>
  <div class="about-view pb-5">
    <!-- Inner Banner -->
    <div class="py-5 bg-gradient-to-r from-brand-800 to-brand-900 text-white text-center">
      <div class="container py-4">
        <h1 class="text-3xl font-extrabold mb-2">{{ t('about_page.banner_title') }}</h1>
        <p class="text-gray-200 text-sm max-w-xl mx-auto">
          {{ t('about_page.banner_subtitle') }}
        </p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <span class="text-brand-600 font-bold uppercase tracking-wider text-xs">{{ t('about_page.history_subtitle') }}</span>
          <h2 class="text-2xl font-bold text-gray-900 mt-2 mb-3">
            {{ t('about_page.history_title') }}
          </h2>
          <p class="text-gray-600 text-sm leading-relaxed mb-3">
            {{ t('about_page.history_p1') }}
          </p>
          <p class="text-gray-600 text-sm leading-relaxed mb-4">
            {{ t('about_page.history_p2') }}
          </p>
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2 text-sm font-bold text-gray-800">
                <i class="ri-check-line text-emerald-600 font-bold text-lg"></i>
                <span>{{ t('about_page.pillar_1') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2 text-sm font-bold text-gray-800">
                <i class="ri-check-line text-emerald-600 font-bold text-lg"></i>
                <span>{{ t('about_page.pillar_2') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2 text-sm font-bold text-gray-800">
                <i class="ri-check-line text-emerald-600 font-bold text-lg"></i>
                <span>{{ t('about_page.pillar_3') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2 text-sm font-bold text-gray-800">
                <i class="ri-check-line text-emerald-600 font-bold text-lg"></i>
                <span>{{ t('about_page.pillar_4') }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 text-center">
          <img src="/assets/images/about/about-group-image.png" alt="École Tijanes Nours" class="img-fluid rounded-2xl shadow-lg">
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
