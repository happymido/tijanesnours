import { createI18n } from 'vue-i18n'
import fr from '../locales/fr.json'
import ar from '../locales/ar.json'
import en from '../locales/en.json'

const savedLocale = localStorage.getItem('locale') || 'fr'

const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'fr',
  messages: {
    fr,
    ar,
    en
  }
})

export function setLanguage(lang) {
  i18n.global.locale.value = lang
  localStorage.setItem('locale', lang)
  const dir = lang === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.setAttribute('dir', dir)
  document.documentElement.setAttribute('lang', lang)

  // Dynamically switch stylesheet between LTR and RTL
  const themeLink = document.getElementById('theme-style')
  if (themeLink) {
    themeLink.setAttribute('href', `/assets/css/style-${dir}.css`)
  }
}

// Initial direction setting
setLanguage(savedLocale)

export default i18n
