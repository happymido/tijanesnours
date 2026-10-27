<template>
  <div class="contact-view pb-5">
    <!-- Inner Banner -->
    <div class="py-5 bg-gradient-to-r from-brand-800 to-brand-900 text-white text-center">
      <div class="container py-4">
        <h1 class="text-3xl font-extrabold mb-2">{{ t('contact.title') }}</h1>
        <p class="text-gray-200 text-sm max-w-xl mx-auto">
          {{ t('contact.subtitle') }}
        </p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row g-5">
        <!-- Contact Information Cards (Left / Start Column) -->
        <div class="col-lg-5">
          <div class="contact-info-panel h-100 p-4 p-md-5 rounded-3xl bg-white shadow-sm border">
            <span class="text-brand-600 font-bold uppercase tracking-wider text-xs">{{ t('nav.contact') }}</span>
            <h2 class="text-2xl font-bold text-gray-900 mt-2 mb-4">
              {{ t('contact.info_title') }}
            </h2>

            <div class="space-y-4">
              <!-- Address -->
              <div class="d-flex align-items-start gap-3 p-3 rounded-2xl bg-gray-50 border">
                <div class="icon-box text-brand-600 bg-brand-50 rounded-xl p-3 flex-shrink-0">
                  <i class="ri-map-pin-2-fill text-xl"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-gray-500 uppercase">{{ t('contact.address_label') }}</div>
                  <div class="text-sm font-semibold text-gray-800 mt-1">{{ t('contact.address_value') }}</div>
                  <div class="text-xs text-gray-500 mt-0.5">Luxembourg (RCS F12999)</div>
                </div>
              </div>

              <!-- Email -->
              <div class="d-flex align-items-start gap-3 p-3 rounded-2xl bg-gray-50 border">
                <div class="icon-box text-brand-600 bg-brand-50 rounded-xl p-3 flex-shrink-0">
                  <i class="ri-mail-fill text-xl"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-gray-500 uppercase">{{ t('contact.email_label') }}</div>
                  <a href="mailto:contact@tijanesnours.lu" class="text-sm font-semibold text-brand-600 hover:underline mt-1 d-block">
                    contact@tijanesnours.lu
                  </a>
                  <div class="text-xs text-gray-500 mt-0.5">Réponse garantie sous 24h à 48h</div>
                </div>
              </div>

              <!-- Phone -->
              <div class="d-flex align-items-start gap-3 p-3 rounded-2xl bg-gray-50 border">
                <div class="icon-box text-brand-600 bg-brand-50 rounded-xl p-3 flex-shrink-0">
                  <i class="ri-phone-fill text-xl"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-gray-500 uppercase">{{ t('contact.phone_label') }}</div>
                  <a href="tel:+352691123456" class="text-sm font-semibold text-gray-800 hover:text-brand-600 mt-1 d-block">
                    {{ t('contact.phone_value') }}
                  </a>
                  <div class="text-xs text-gray-500 mt-0.5">Permanence téléphonique le week-end</div>
                </div>
              </div>

              <!-- Schedule -->
              <div class="d-flex align-items-start gap-3 p-3 rounded-2xl bg-gray-50 border">
                <div class="icon-box text-brand-600 bg-brand-50 rounded-xl p-3 flex-shrink-0">
                  <i class="ri-time-fill text-xl"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-gray-500 uppercase">{{ t('contact.hours_label') }}</div>
                  <div class="text-sm font-semibold text-gray-800 mt-1">{{ t('contact.hours_value') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Form (Right / End Column) -->
        <div class="col-lg-7">
          <div class="contact-form-panel p-4 p-md-5 rounded-3xl bg-white shadow-sm border">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
              {{ t('contact.form_title') }}
            </h2>
            <p class="text-gray-500 text-sm mb-4">
              Remplissez le formulaire ci-dessous, un message sera directement transmis à notre administration.
            </p>

            <!-- Success Alert -->
            <div v-if="successMessage" class="alert alert-success d-flex align-items-center gap-2 rounded-2xl p-3 mb-4 shadow-sm" role="alert">
              <i class="ri-checkbox-circle-fill text-2xl text-emerald-600"></i>
              <div>
                <strong class="d-block text-emerald-800">{{ t('contact.success_title') }}</strong>
                <span class="text-sm text-emerald-700">{{ successMessage }}</span>
              </div>
            </div>

            <!-- Error Alert -->
            <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center gap-2 rounded-2xl p-3 mb-4 shadow-sm" role="alert">
              <i class="ri-error-warning-fill text-2xl text-red-600"></i>
              <div>
                <strong class="d-block text-red-800">{{ t('contact.error_title') }}</strong>
                <span class="text-sm text-red-700">{{ errorMessage }}</span>
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- Nom Complet -->
                <div class="col-md-6">
                  <label class="form-label text-xs font-bold text-gray-700 uppercase">
                    {{ t('contact.full_name') }} <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.fullName"
                    type="text"
                    required
                    :disabled="isSubmitting"
                    class="form-control rounded-xl py-2.5 px-3 text-sm"
                    :class="{ 'is-invalid': fieldErrors.fullName }"
                    :placeholder="t('contact.full_name_placeholder')"
                  />
                  <div v-if="fieldErrors.fullName" class="invalid-feedback text-xs">
                    {{ fieldErrors.fullName }}
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <label class="form-label text-xs font-bold text-gray-700 uppercase">
                    {{ t('contact.email') }} <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    :disabled="isSubmitting"
                    class="form-control rounded-xl py-2.5 px-3 text-sm"
                    :class="{ 'is-invalid': fieldErrors.email }"
                    :placeholder="t('contact.email_placeholder')"
                  />
                  <div v-if="fieldErrors.email" class="invalid-feedback text-xs">
                    {{ fieldErrors.email }}
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                  <label class="form-label text-xs font-bold text-gray-700 uppercase">
                    {{ t('contact.phone') }}
                  </label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    :disabled="isSubmitting"
                    class="form-control rounded-xl py-2.5 px-3 text-sm"
                    :placeholder="t('contact.phone_placeholder')"
                  />
                </div>

                <!-- Subject -->
                <div class="col-md-6">
                  <label class="form-label text-xs font-bold text-gray-700 uppercase">
                    {{ t('contact.subject') }} <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.subject"
                    type="text"
                    required
                    :disabled="isSubmitting"
                    class="form-control rounded-xl py-2.5 px-3 text-sm"
                    :class="{ 'is-invalid': fieldErrors.subject }"
                    :placeholder="t('contact.subject_placeholder')"
                  />
                  <div v-if="fieldErrors.subject" class="invalid-feedback text-xs">
                    {{ fieldErrors.subject }}
                  </div>
                </div>

                <!-- Message -->
                <div class="col-12">
                  <label class="form-label text-xs font-bold text-gray-700 uppercase">
                    {{ t('contact.message') }} <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="form.message"
                    rows="5"
                    required
                    :disabled="isSubmitting"
                    class="form-control rounded-xl p-3 text-sm"
                    :class="{ 'is-invalid': fieldErrors.message }"
                    :placeholder="t('contact.message_placeholder')"
                  ></textarea>
                  <div v-if="fieldErrors.message" class="invalid-feedback text-xs">
                    {{ fieldErrors.message }}
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 pt-2">
                  <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="default-btn w-100 py-3 text-sm font-bold d-flex align-items-center justify-content-center gap-2"
                    style="border-radius: 30px; border: none; cursor: pointer;"
                  >
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <i v-else class="ri-send-plane-fill"></i>
                    <span>{{ isSubmitting ? t('contact.submitting') : t('contact.submit') }}</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import apiClient from '../../plugins/axios'

const { t } = useI18n()

const form = reactive({
  fullName: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const fieldErrors = reactive({})

async function handleSubmit() {
  // Clear previous alerts and errors
  successMessage.value = ''
  errorMessage.value = ''
  Object.keys(fieldErrors).forEach(key => delete fieldErrors[key])

  isSubmitting.value = true

  try {
    const response = await apiClient.post('/public/contact', {
      fullName: form.fullName.trim(),
      email: form.email.trim(),
      phone: form.phone.trim(),
      subject: form.subject.trim(),
      message: form.message.trim()
    })

    if (response.data && response.data.status === 'success') {
      successMessage.value = response.data.message || t('contact.success_msg')
      // Reset form
      form.fullName = ''
      form.email = ''
      form.phone = ''
      form.subject = ''
      form.message = ''
    } else {
      errorMessage.value = response.data.message || t('contact.error_default')
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.errors) {
      Object.assign(fieldErrors, err.response.data.errors)
      errorMessage.value = err.response.data.message || t('contact.error_default')
    } else if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = err.response.data.message
    } else {
      errorMessage.value = t('contact.error_default')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.contact-view {
  min-height: 80vh;
}

.icon-box {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
}

.default-btn {
  background-color: #8CC63F;
  color: #ffffff;
  transition: all 0.3s ease;
}

.default-btn:hover:not(:disabled) {
  background-color: #79ab35;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(140, 198, 63, 0.35);
}

.default-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.form-control:focus {
  border-color: #8CC63F;
  box-shadow: 0 0 0 0.2rem rgba(140, 198, 63, 0.25);
}
</style>
