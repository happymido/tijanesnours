<template>
  <div class="public-layout">
    <!-- Start Navbar Area --> 
    <nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom py-3 sticky-top" id="navbar">
      <div class="container d-flex align-items-center justify-between" style="max-width: 1200px; margin: 0 auto;">
        <!-- Logo -->
        <router-link class="navbar-brand p-0 d-flex align-items-center gap-2" to="/">
          <img src="/assets/img/logo.png" alt="Tijanes Nours" style="max-height: 55px;">
        </router-link>

        <!-- Menu Navigation Top Bar (Desktop) -->
        <div class="d-none d-lg-flex align-items-center mx-auto">
          <ul class="navbar-nav d-flex flex-row gap-4 mb-0">
            <li class="nav-item">
              <router-link to="/" class="nav-link text-dark font-bold text-sm px-2" active-class="text-brand-600 font-extrabold active">{{ t('nav.home') }}</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/courses" class="nav-link text-dark font-bold text-sm px-2" active-class="text-brand-600 font-extrabold active">{{ t('nav.courses') }}</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/about" class="nav-link text-dark font-bold text-sm px-2" active-class="text-brand-600 font-extrabold active">{{ t('nav.about') }}</router-link>
            </li>
          </ul>
        </div>

        <!-- Controls: Language Selector Flags + Auth -->
        <div class="others-options d-flex align-items-center gap-3">
          <div class="option-item">
            <div class="lang-switcher-flags d-flex align-items-center gap-1">
              <button
                @click="setLang('fr')"
                class="flag-btn"
                :class="{ active: currentLang === 'fr' }"
                title="Français"
              >
                <span>🇫🇷</span> <small class="d-none d-sm-inline">FR</small>
              </button>
              <button
                @click="setLang('ar')"
                class="flag-btn"
                :class="{ active: currentLang === 'ar' }"
                title="العربية"
              >
                <span>🇦🇪</span> <small class="d-none d-sm-inline">العربية</small>
              </button>
              <button
                @click="setLang('en')"
                class="flag-btn"
                :class="{ active: currentLang === 'en' }"
                title="English"
              >
                <span>🇬🇧</span> <small class="d-none d-sm-inline">EN</small>
              </button>
            </div>
          </div>

          <template v-if="!authStore.isAuthenticated">
            <div class="option-item">
              <router-link to="/register" class="default-btn py-2 px-3 text-xs">
                {{ t('nav.register') }}
              </router-link>
            </div>
            <div class="option-item d-none d-sm-block">
              <router-link to="/login" class="btn btn-outline-secondary font-bold text-xs px-3 py-2" style="border-radius: 30px;">
                {{ t('nav.login') }}
              </router-link>
            </div>
          </template>
          <template v-else>
            <div class="option-item">
              <button @click="goToDashboard" class="default-btn py-2 px-3 text-xs">
                {{ t('nav.dashboard') }}
              </button>
            </div>
          </template>

          <!-- Mobile Toggle Button -->
          <button 
            @click="isMobileMenuOpen = !isMobileMenuOpen" 
            class="navbar-toggler border-0 shadow-none d-lg-none bg-transparent p-1"
            type="button"
            aria-label="Toggle Navigation"
          >
            <span class="burger-menu text-2xl text-gray-800">
              <i :class="isMobileMenuOpen ? 'ri-close-line' : 'ri-menu-line'"></i>
            </span>
          </button>
        </div>
      </div>
    </nav>
    <!-- End Navbar Area -->

    <!-- Mobile Navigation Drawer Overlay & Drawer -->
    <Teleport to="body">
      <Transition name="fade">
        <div 
          v-if="isMobileMenuOpen" 
          class="mobile-menu-backdrop" 
          @click="isMobileMenuOpen = false"
        ></div>
      </Transition>
      
      <div 
        class="mobile-menu-drawer" 
        :class="{ 'is-open': isMobileMenuOpen }"
      >
        <div class="mobile-menu-header d-flex align-items-center justify-content-between p-3 border-bottom">
          <router-link to="/" class="logo d-inline-block" @click="isMobileMenuOpen = false">
            <img src="/assets/img/logo.png" alt="Tijanes Nours" style="max-height: 42px;">
          </router-link>
          <button 
            type="button" 
            class="close-btn border-0 bg-transparent p-2 text-2xl text-gray-600 hover:text-gray-900" 
            @click="isMobileMenuOpen = false"
            aria-label="Close"
          >
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="mobile-menu-body p-4 flex-1 overflow-y-auto">
          <ul class="navbar-nav mb-4 gap-2 list-unstyled">
            <li class="nav-item">
              <router-link 
                to="/" 
                class="nav-link font-bold text-base py-2 px-3 rounded text-gray-800" 
                active-class="bg-brand-50 text-brand-600 active" 
                @click="isMobileMenuOpen = false"
              >
                {{ t('nav.home') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/courses" 
                class="nav-link font-bold text-base py-2 px-3 rounded text-gray-800" 
                active-class="bg-brand-50 text-brand-600 active" 
                @click="isMobileMenuOpen = false"
              >
                {{ t('nav.courses') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/about" 
                class="nav-link font-bold text-base py-2 px-3 rounded text-gray-800" 
                active-class="bg-brand-50 text-brand-600 active" 
                @click="isMobileMenuOpen = false"
              >
                {{ t('nav.about') }}
              </router-link>
            </li>
          </ul>

          <div class="pt-4 border-top d-flex flex-column gap-3">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
              {{ currentLang === 'ar' ? 'اللغة' : 'Langue' }}
            </label>
            <div class="lang-switcher-flags d-flex align-items-center justify-content-between gap-2">
              <button
                @click="setLang('fr')"
                class="flag-btn flex-1 justify-content-center"
                :class="{ active: currentLang === 'fr' }"
              >
                <span>🇫🇷</span> <small>FR</small>
              </button>
              <button
                @click="setLang('ar')"
                class="flag-btn flex-1 justify-content-center"
                :class="{ active: currentLang === 'ar' }"
              >
                <span>🇦🇪</span> <small>العربية</small>
              </button>
              <button
                @click="setLang('en')"
                class="flag-btn flex-1 justify-content-center"
                :class="{ active: currentLang === 'en' }"
              >
                <span>🇬🇧</span> <small>EN</small>
              </button>
            </div>

            <div class="mt-2">
              <template v-if="!authStore.isAuthenticated">
                <div class="d-flex flex-column gap-2">
                  <router-link 
                    to="/register" 
                    class="default-btn w-100 text-center py-2 text-sm" 
                    @click="isMobileMenuOpen = false"
                  >
                    {{ t('nav.register') }}
                  </router-link>
                  <router-link 
                    to="/login" 
                    class="btn btn-outline-secondary w-100 font-bold text-sm py-2" 
                    style="border-radius: 30px;"
                    @click="isMobileMenuOpen = false"
                  >
                    {{ t('nav.login') }}
                  </router-link>
                </div>
              </template>
              <template v-else>
                <button 
                  @click="goToDashboard(); isMobileMenuOpen = false" 
                  class="default-btn w-100 text-center py-2 text-sm"
                >
                  {{ t('nav.dashboard') }}
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
    <!-- End Mobile Device Navbar Area -->

    <!-- Main View Outlet -->
    <main>
      <router-view />
    </main>

    <!-- Start Footer Area (Light Theme) -->
    <footer class="footer-area pt-5 pb-4 bg-gray-50 border-top text-gray-700">
      <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div class="row g-4">
          <div class="col-lg-5 col-md-6">
            <div class="footer-widget">
              <div class="logo mb-3">
                <router-link to="/">
                  <img src="/assets/img/logo.png" alt="Tijanes Nours" style="max-height: 50px;">
                </router-link>
              </div>
              <p class="text-xs leading-relaxed text-gray-600">
                {{ t('footer.about_text') }}
              </p>
              <div class="d-flex gap-2 mt-3">
                <button @click="setLang('fr')" class="btn btn-sm btn-outline-secondary text-xs">Français</button>
                <button @click="setLang('ar')" class="btn btn-sm btn-outline-secondary text-xs">العربية</button>
                <button @click="setLang('en')" class="btn btn-sm btn-outline-secondary text-xs">English</button>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="footer-widget style2">
              <h3 class="text-gray-900 font-bold text-base mb-3">{{ t('footer.contact_title') }}</h3>
              <ul class="footer-contact-list text-xs space-y-3 list-unstyled">
                <li class="contact-item">
                  <div class="contact-icon">
                    <i class="ri-map-pin-line"></i>
                  </div>
                  <span class="contact-text">Centre Maryam / LJM Luxembourg</span>
                </li>
                <li class="contact-item">
                  <div class="contact-icon">
                    <i class="ri-mail-line"></i>
                  </div>
                  <a href="mailto:contact@tijanesnours.lu" class="contact-text">contact@tijanesnours.lu</a>
                </li>
                <li class="contact-item">
                  <div class="contact-icon">
                    <i class="ri-phone-line"></i>
                  </div>
                  <a href="tel:+352691123456" class="contact-text">+352 691 123 456</a>
                </li>
              </ul>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="footer-widget">
              <h3 class="text-gray-900 font-bold text-base mb-2">{{ t('footer.quick_links') }}</h3>
              <ul class="footer-quick-links text-xs space-y-1 list-unstyled m-0 p-0">
                <li><router-link to="/" class="text-gray-700 hover:text-brand-600 d-inline-block py-1">{{ t('nav.home') }}</router-link></li>
                <li><router-link to="/courses" class="text-gray-700 hover:text-brand-600 d-inline-block py-1">{{ t('nav.courses') }}</router-link></li>
                <li><router-link to="/about" class="text-gray-700 hover:text-brand-600 d-inline-block py-1">{{ t('nav.about') }}</router-link></li>
                <li><router-link to="/login" class="text-gray-700 hover:text-brand-600 d-inline-block py-1">{{ t('nav.login') }}</router-link></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="copyright-area text-center mt-4 pt-3 border-top border-gray-200 text-xs text-gray-500">
          <p>&copy; 2026 {{ t('footer.copyright') }}</p>
        </div>
      </div>
    </footer>
    <!-- End Footer Area -->
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { setLanguage } from '../plugins/i18n'

const { t, locale } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const currentLang = ref(locale.value)
const isMobileMenuOpen = ref(false)

watch(locale, (newLoc) => {
  currentLang.value = newLoc
})

function changeLang() {
  setLanguage(currentLang.value)
}

function setLang(lang) {
  currentLang.value = lang
  setLanguage(lang)
}

function goToDashboard() {
  if (authStore.isAdmin) router.push('/admin/dashboard')
  else if (authStore.isPaymentAgent) router.push('/payment-agent/dashboard')
  else if (authStore.isParent) router.push('/parent/dashboard')
  else router.push('/')
}
</script>

<style scoped>
.container {
  max-width: 1200px !important;
  margin-left: auto !important;
  margin-right: auto !important;
}

.footer-contact-list {
  margin: 0 !important;
  padding: 0 !important;
}

.contact-item {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  position: static !important;
  padding: 0 !important;
  margin-bottom: 12px !important;
}

.contact-icon {
  width: 32px !important;
  height: 32px !important;
  min-width: 32px !important;
  border-radius: 50% !important;
  background-color: rgba(140, 198, 63, 0.15) !important;
  color: #8CC63F !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 16px !important;
  font-weight: bold !important;
  position: static !important;
  top: auto !important;
  left: auto !important;
}

.contact-text {
  color: #374151 !important;
  font-size: 13px !important;
  font-weight: 500 !important;
  text-decoration: none !important;
  transition: color 0.2s ease !important;
}

.contact-text:hover {
  color: #8CC63F !important;
}

.footer-quick-links {
  margin-top: 0 !important;
  padding-top: 0 !important;
}

.footer-quick-links li {
  margin-bottom: 2px !important;
}

/* Sticky Navbar Styling */
.navbar.sticky-top {
  position: sticky !important;
  top: 0 !important;
  z-index: 1030 !important;
  background-color: rgba(255, 255, 255, 0.98) !important;
  backdrop-filter: blur(10px) !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
}

/* Flag Buttons Language Switcher Styling */
.flag-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  padding: 4px 10px !important;
  border-radius: 20px !important;
  border: 1px solid #d1d5db !important;
  background-color: #ffffff !important;
  color: #374151 !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
}

.flag-btn:hover {
  border-color: #8CC63F !important;
  color: #8CC63F !important;
}

.flag-btn.active {
  background-color: #8CC63F !important;
  border-color: #8CC63F !important;
  color: #ffffff !important;
  font-weight: 700 !important;
}

/* Mobile Menu Backdrop & Drawer Styling */
.mobile-menu-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  z-index: 9998;
}

.mobile-menu-drawer {
  position: fixed;
  top: 0;
  bottom: 0;
  width: 320px;
  max-width: 85vw;
  background-color: #ffffff;
  z-index: 9999;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

/* LTR Positioning */
html:not([dir="rtl"]) .mobile-menu-drawer,
[dir="ltr"] .mobile-menu-drawer {
  right: 0;
  transform: translateX(100%);
}
html:not([dir="rtl"]) .mobile-menu-drawer.is-open,
[dir="ltr"] .mobile-menu-drawer.is-open {
  transform: translateX(0);
}

/* RTL Positioning */
html[dir="rtl"] .mobile-menu-drawer,
[dir="rtl"] .mobile-menu-drawer {
  left: 0;
  right: auto;
  transform: translateX(-100%);
}
html[dir="rtl"] .mobile-menu-drawer.is-open,
[dir="rtl"] .mobile-menu-drawer.is-open {
  transform: translateX(0);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
