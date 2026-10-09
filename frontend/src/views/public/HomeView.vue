<template>
  <div class="home-view">
    <!--= Start Hero Area =-->
    <div class="hero-slider-widget1">
      <div class="hero-area1">
        <div class="container">
          <div class="hero-content">
            <div class="hero-card">
              <div class="hero-card-body">
                <h3>{{ t('hero.title') }}</h3>
                <p>{{ t('hero.subtitle') }}</p>
                <router-link to="/register" class="default-btn">
                  {{ t('hero.cta_register') }}
                </router-link>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-lg-4 col-md-4">
                <div class="hero-count">
                  <div class="time">
                    <h3 class="counter">500</h3>
                    <span>+</span>
                  </div>
                  <h5>{{ t('about.stat_students') }}</h5>
                </div>
              </div>
              <div class="col-lg-4 col-md-4">
                <div class="hero-count">
                  <div class="time one">
                    <h3 class="counter">10</h3>
                    <span>+</span>
                  </div>
                  <h5>{{ t('about.stat_experience') }}</h5>
                </div>
              </div>
              <div class="col-lg-4 col-md-4">
                <div class="hero-count">
                  <div class="time two">
                    <h3 class="counter">99</h3>
                    <span>%</span>
                  </div>
                  <h5>{{ t('about.stat_satisfaction') }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--= End Hero Area =-->

    <!--= Start About Area =-->
    <div class="about-area1 ptb-100">
      <div class="container">
        <div class="about-item">
          <div class="row g-4 align-items-center">
            <div class="col-lg-6">
              <div class="about-content">
                <div class="about-card">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="/assets/images/svg/cricle.svg" alt="image" style="width: 22px; height: 22px;">
                    <span class="m-0 font-bold" style="color: #8CC63F; font-size: 16px;">{{ t('about.subtitle') }}</span>
                  </div>
                  <h3>{{ t('about.title') }}</h3>
                  <p>
                    {{ t('about.description') }}
                  </p>
                </div>

                <div class="row g-4">
                  <div class="col-lg-6 col-md-6">
                    <div class="hero-count">
                      <div class="time">
                        <img class="logo" src="/assets/images/logo/light-bulb.png" alt="image">
                        <div class="plus">
                          <h3 class="counter">100</h3>
                          <span>%</span>
                        </div>
                      </div>
                      <h6>{{ t('about.counter_workshops') }}</h6>
                    </div>
                  </div>
                  <div class="col-lg-6 col-md-6">
                    <div class="hero-count">
                      <div class="time">
                        <img class="logo-one" src="/assets/images/logo/education.png" alt="image">
                        <div class="plus">
                          <h3 class="counter">15</h3>
                          <span>+</span>
                        </div>
                      </div>
                      <h6>{{ t('about.counter_teachers') }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="about-styel-image text-center">
                <img src="/assets/images/about/about-group-image.png" alt="image">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-3 col-md-6">
            <div class="about-home-image">
              <router-link to="/about">
                <img src="/assets/images/about/about-image-2.png" alt="image">
              </router-link>
              <h3>
                <router-link to="/about">{{ t('about.feature_1') }}</router-link>
              </h3>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="about-home-image">
              <router-link to="/about">
                <img src="/assets/images/about/about-image-3.png" alt="image">
              </router-link>
              <h3>
                <router-link to="/about">{{ t('about.feature_2') }}</router-link>
              </h3>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="about-home-image">
              <router-link to="/about">
                <img src="/assets/images/about/about-image-4.png" alt="image">
              </router-link>
              <h3>
                <router-link to="/about">{{ t('about.feature_3') }}</router-link>
              </h3>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="about-home-image">
              <router-link to="/about">
                <img src="/assets/images/about/about-image-5.png" alt="image">
              </router-link>
              <h3>
                <router-link to="/about">{{ t('about.feature_4') }}</router-link>
              </h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--= End About Area =-->

    <!--= Start Programs Area (Smarty Programs Carousel) =-->
    <div class="programs-area1 ptb-100 bg-gray-50">
      <div class="container">
        <div class="section-title text-center mb-5">
          <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <img src="/assets/images/svg/cricle.svg" alt="image" style="width: 22px; height: 22px;">
            <span class="sub-title m-0 p-0 font-bold" style="color: #8CC63F; font-size: 16px;">{{ t('programs.subtitle') }}</span>
          </div>
          <h2>{{ t('programs.title') }}</h2>
        </div>

        <div class="programs-slide-widget">
          <div :key="locale" class="swiper programs py-4 px-1">
            <div class="swiper-wrapper">
              <div
                v-for="(level, index) in courseLevels"
                :key="level.id"
                class="swiper-slide h-auto"
              >
                <div
                  class="programs-card h-100 flex flex-col justify-between"
                  :class="['', 'active', 'one', 'two'][index % 4]"
                  @mouseenter="pauseAutoplay"
                  @mouseleave="resumeAutoplay"
                >
                  <div class="programs-card-body flex-1 flex flex-col justify-between" :class="['', 'active', 'one', 'two'][index % 4]">
                    <div>
                      <h3 class="program-title mb-2">
                        <router-link to="/register">{{ getLevelName(level) }}</router-link>
                      </h3>
                      <span class="font-bold text-xs text-brand-600 mb-2 d-inline-block">
                        ({{ level.targetAgeMin }} - {{ level.targetAgeMax }} ans)
                      </span>
                      <p class="program-description text-xs leading-relaxed text-gray-600 mt-2">
                        {{ getLevelDescription(level) }}
                      </p>
                    </div>
                    <div class="pt-3">
                      <router-link to="/register" class="arrow-btn d-inline-block" aria-label="S'inscrire">
                        <i class="ri-arrow-right-line"></i>
                      </router-link>
                    </div>
                  </div>
                  <div class="programs-number" :class="['', 'active', 'one', 'two'][index % 4]">
                    <span>Niveau</span>
                    <h5>{{ index + 1 }}</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Enhanced Navigation Controls -->
          <div class="programs-btn-wrapper d-flex justify-content-center align-items-center gap-3 mt-4">
            <div class="swiper-button-prev1 nav-circle-btn">
              <i class="ri-arrow-left-line"></i>
            </div>
            <div class="swiper-button-next1 nav-circle-btn">
              <i class="ri-arrow-right-line"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--= End Programs Area =-->

    <!--= Start Service Area =-->
    <div class="service-area1 ptb-100">
      <div class="container">
        <div class="section-title text-center">
          <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <img src="/assets/images/svg/cricle.svg" alt="image" style="width: 22px; height: 22px;">
            <span class="sub-title m-0 p-0 font-bold" style="color: #8CC63F; font-size: 16px;">{{ t('domain.subtitle') }}</span>
          </div>
          <h2>{{ t('domain.title') }}</h2>
        </div>
        <div class="row g-4 justify-content-center">
          <div class="col-lg-4 col-md-6">
            <div class="service-card">
              <div class="image-style1">
                <router-link to="/courses">
                  <img class="service" src="/assets/images/service/service-image-1.jpg" alt="image">
                </router-link>
              </div>
              <div class="servie-image">
                <div class="service-card-body">
                  <h3><router-link to="/courses">{{ t('domain.card1_title') }}</router-link></h3>
                  <p>{{ t('domain.card1_desc') }}</p>
                  <div class="service-logo">
                    <img class="logo" src="/assets/images/logo/primary.png" alt="image">
                  </div>
                  <router-link to="/courses" class="default-btn">{{ t('domain.learn_more') }}</router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="service-card">
              <div class="image-style1">
                <router-link to="/courses">
                  <img class="service" src="/assets/images/service/service.jpg" alt="image">
                </router-link>
              </div>
              <div class="servie-image">
                <div class="service-card-body">
                  <h3><router-link to="/courses">{{ t('domain.card2_title') }}</router-link></h3>
                  <p>{{ t('domain.card2_desc') }}</p>
                  <div class="service-logo">
                    <img class="logo" src="/assets/images/logo/compassion.png" alt="image">
                  </div>
                  <router-link to="/courses" class="default-btn">{{ t('domain.learn_more') }}</router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="service-card">
              <div class="image-style1">
                <router-link to="/courses">
                  <img class="service" src="/assets/images/service/service-image-2.jpg" alt="image">
                </router-link>
              </div>
              <div class="servie-image">
                <div class="service-card-body">
                  <h3><router-link to="/courses">{{ t('domain.card3_title') }}</router-link></h3>
                  <p>{{ t('domain.card3_desc') }}</p>
                  <div class="service-logo">
                    <img class="logo" src="/assets/images/logo/italian-pizza.png" alt="image">
                  </div>
                  <router-link to="/courses" class="default-btn">{{ t('domain.learn_more') }}</router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--= End Service Area =-->
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import apiClient from '../../plugins/axios'

const { t, locale } = useI18n()
const courseLevels = ref([])

function getLevelName(level) {
  if (!level) return ''
  const lang = locale.value || 'fr'
  if (level.translations && level.translations[lang] && level.translations[lang].name) {
    return level.translations[lang].name
  }
  if (level.translations && level.translations['fr'] && level.translations['fr'].name) {
    return level.translations['fr'].name
  }
  return level.name || ''
}

function getLevelDescription(level) {
  if (!level) return ''
  const lang = locale.value || 'fr'
  if (level.translations && level.translations[lang] && level.translations[lang].description) {
    return level.translations[lang].description
  }
  if (level.translations && level.translations['fr'] && level.translations['fr'].description) {
    return level.translations['fr'].description
  }
  return level.description || ''
}

let programsSwiperInstance = null

function pauseAutoplay() {
  if (programsSwiperInstance && programsSwiperInstance.autoplay) {
    programsSwiperInstance.autoplay.stop()
  }
}

function resumeAutoplay() {
  if (programsSwiperInstance && programsSwiperInstance.autoplay) {
    programsSwiperInstance.autoplay.start()
  }
}

function initProgramsSwiper() {
  const container = document.querySelector('.programs')
  if (!container || typeof window.Swiper === 'undefined') return

  if (container.swiper) {
    container.swiper.destroy(true, true)
  }
  if (programsSwiperInstance) {
    programsSwiperInstance.destroy(true, true)
    programsSwiperInstance = null
  }

  const isRtl = document.documentElement.getAttribute('dir') === 'rtl' || locale.value === 'ar'

  programsSwiperInstance = new window.Swiper('.programs', {
    dir: isRtl ? 'rtl' : 'ltr',
    slidesPerView: 1,
    spaceBetween: 25,
    loop: true,
    loopedSlides: 4,
    observer: true,
    observeParents: true,
    autoplay: {
      delay: 3500,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    },
    speed: 800,
    navigation: {
      nextEl: '.swiper-button-next1',
      prevEl: '.swiper-button-prev1',
    },
    breakpoints: {
      0: { slidesPerView: 1 },
      576: { slidesPerView: 1 },
      768: { slidesPerView: 2 },
      992: { slidesPerView: 3 },
      1200: { slidesPerView: 4 }
    }
  })
}

watch(locale, async () => {
  await nextTick()
  setTimeout(() => {
    initProgramsSwiper()
  }, 120)
})

onMounted(async () => {
  try {
    const res = await apiClient.get('/public/course-levels')
    if (Array.isArray(res.data)) {
      courseLevels.value = res.data.sort((a, b) => a.id - b.id)
    }
    await nextTick()
    setTimeout(() => {
      initProgramsSwiper()
    }, 100)
  } catch (err) {
    console.error('Erreur lors de la récupération des niveaux', err)
  }
})
</script>

<style scoped>
.container {
  max-width: 1200px !important;
  margin-left: auto !important;
  margin-right: auto !important;
}

/* Swiper Equal Height Alignment */
:deep(.swiper-slide) {
  height: auto !important;
  display: flex !important;
}

/* Disable template 7px thick colored bottom block */
.programs-card::before,
.programs-card:hover::before,
.programs-card.active::before,
.programs-card.one::before,
.programs-card.two::before {
  display: none !important;
  content: none !important;
}

.programs-card {
  width: 100%;
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: space-between !important;
  border-radius: 20px;
  background: #ffffff !important;
  border: 2px solid #e5e7eb;
  padding: 65px 22px 25px 22px !important;
  margin-top: 25px;
  position: relative;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.programs-card-body {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
}

/* Program Description 3-line Clamp & Hover Expansion */
.program-description {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: pre-line;
  transition: all 0.3s ease;
}

.programs-card:hover .program-description {
  display: block;
  -webkit-line-clamp: unset;
  overflow: visible;
}

/* Hover Scale & Color Transition Enhancements */
.programs-card:hover {
  transform: translateY(-4px);
  border-color: #75c137 !important;
  box-shadow: 0 6px 16px rgba(117, 193, 55, 0.15) !important;
}

.programs-card.active:hover {
  border-color: #5a00a1 !important;
  box-shadow: 0 6px 16px rgba(90, 0, 161, 0.15) !important;
}

.programs-card.one:hover {
  border-color: #f5cd00 !important;
  box-shadow: 0 6px 16px rgba(245, 205, 0, 0.18) !important;
}

.programs-card.two:hover {
  border-color: #f21e09 !important;
  box-shadow: 0 6px 16px rgba(242, 30, 9, 0.15) !important;
}

.programs-card:hover .programs-number {
  transform: scale(1.06);
}

.arrow-btn i {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #75c137;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all 0.3s ease;
}

.programs-card-body.active .arrow-btn i {
  background: #5a00a1;
}

.programs-card-body.one .arrow-btn i {
  background: #f5cd00;
}

.programs-card-body.two .arrow-btn i {
  background: #f21e09;
}

.programs-card:hover .arrow-btn i {
  transform: scale(1.12);
}

[dir="rtl"] .arrow-btn i {
  transform: scaleX(-1);
}

[dir="rtl"] .programs-card:hover .arrow-btn i {
  transform: scaleX(-1) scale(1.12);
}

/* Navigation Buttons Position & Styling */
.nav-circle-btn {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px solid #8CC63F;
  color: #8CC63F;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(140, 198, 63, 0.15);
}

.nav-circle-btn:hover {
  background: #8CC63F;
  color: #ffffff;
  transform: scale(1.12);
  box-shadow: 0 8px 20px rgba(140, 198, 63, 0.35);
}

/* Stat Counter (500+ Élèves) Alignment & Sizing */
.hero-area1 .hero-content .hero-count .time {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 2px !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
  margin-bottom: 8px !important;
  position: static !important;
}

.hero-area1 .hero-content .hero-count .time h3,
.hero-area1 .hero-content .hero-count .time .h3 {
  font-size: 2.25rem !important;
  font-weight: 800 !important;
  line-height: 1 !important;
  margin: 0 !important;
}

.hero-area1 .hero-content .hero-count .time span {
  position: static !important;
  top: auto !important;
  left: auto !important;
  right: auto !important;
  font-size: 2.25rem !important;
  font-weight: 800 !important;
  line-height: 1 !important;
}

.hero-area1 .hero-content .hero-count h5 {
  font-size: 15px !important;
  font-weight: 600 !important;
  margin: 0 !important;
}

/* Notre Mission Counters Alignment & Sizing Fix */
.about-area1 .about-item .about-content .hero-count {
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  gap: 8px !important;
  width: 100% !important;
}

.about-area1 .about-item .about-content .hero-count .time {
  margin-bottom: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  position: relative !important;
  flex-shrink: 0 !important;
}

.about-area1 .about-item .about-content .hero-count .time .logo,
.about-area1 .about-item .about-content .hero-count .time .logo-one {
  width: 38px !important;
  height: 38px !important;
  flex-shrink: 0 !important;
}

.about-area1 .about-item .about-content .hero-count .time .plus {
  position: static !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 1px !important;
  white-space: nowrap !important;
}

.about-area1 .about-item .about-content .hero-count .time .plus h3 {
  font-size: 1.4rem !important;
  font-weight: 800 !important;
  margin: 0 !important;
  line-height: 1 !important;
}

.about-area1 .about-item .about-content .hero-count .time .plus span {
  position: static !important;
  font-size: 1.4rem !important;
  font-weight: 800 !important;
  line-height: 1 !important;
}

.about-area1 .about-item .about-content .hero-count h6 {
  margin: 0 !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  line-height: 1.3 !important;
  word-break: break-word !important;
}

/* Nos Domaines Icon & Text Alignment */
.service-area1 .service-card .servie-image .service-card-body {
  position: relative !important;
  padding-top: 50px !important;
}

.service-area1 .service-card .servie-image .service-card-body .service-logo {
  top: -45px !important;
  width: 90px !important;
  height: 90px !important;
  padding: 15px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

[dir="ltr"] .service-area1 .service-card .servie-image .service-card-body .service-logo {
  right: 20px !important;
  left: auto !important;
}

[dir="rtl"] .service-area1 .service-card .servie-image .service-card-body .service-logo {
  left: 20px !important;
  right: auto !important;
}

.service-area1 .service-card .servie-image .service-card-body .service-logo .logo {
  width: 50px !important;
  height: 50px !important;
  object-fit: contain !important;
}

/* Responsive Mobile Banner & Hero Area Fixes */
@media (max-width: 991px) {
  .hero-area1 {
    padding-top: 40px !important;
    padding-bottom: 40px !important;
    background-image: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0.45) 0%,
        rgba(255, 255, 255, 0.85) 100%
      ),
      url('/assets/images/hero-image/hero-image2.jpg') !important;
    background-position: center 20% !important;
    background-size: cover !important;
    background-repeat: no-repeat !important;
    min-height: 480px !important;
  }

  .hero-area1 .hero-content {
    width: 100% !important;
  }

  .hero-area1 .hero-content .hero-card {
    background: rgba(117, 193, 55, 0.92) !important;
    backdrop-filter: blur(8px) !important;
    border-radius: 24px !important;
    padding: 24px 18px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
    margin-bottom: 20px !important;
  }

  .hero-area1 .hero-content .hero-card h3 {
    font-size: 1.45rem !important;
    line-height: 1.35 !important;
    color: #ffffff !important;
  }

  .hero-area1 .hero-content .hero-card p {
    font-size: 0.95rem !important;
    margin-top: 10px !important;
    margin-bottom: 16px !important;
    color: #f3f4f6 !important;
  }

  .hero-area1 .hero-content .hero-count {
    background: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(6px) !important;
    border-radius: 16px !important;
    padding: 12px 8px !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    margin-bottom: 10px !important;
  }
}
</style>
