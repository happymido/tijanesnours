import Swal from 'sweetalert2'

export const showSuccessAlert = (title, message) => {
  return Swal.fire({
    title: title || 'Opération Réussie !',
    html: `<div class="text-sm text-gray-600 dark:text-gray-300 mt-2">${message}</div>`,
    icon: 'success',
    iconColor: '#10b981',
    confirmButtonText: 'D\'accord',
    confirmButtonColor: '#047857',
    customClass: {
      popup: 'rounded-3xl shadow-2xl border border-emerald-100 p-6 dark:bg-gray-800 dark:text-white',
      title: 'text-xl font-extrabold text-emerald-700 dark:text-emerald-400',
      confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg'
    },
    buttonsStyling: true,
    showClass: {
      popup: 'animate__animated animate__fadeInDown animate__faster'
    },
    hideClass: {
      popup: 'animate__animated animate__fadeOutUp animate__faster'
    }
  })
}

export const showErrorAlert = (title, message) => {
  return Swal.fire({
    title: title || 'Erreur !',
    html: `<div class="text-sm text-gray-600 dark:text-gray-300 mt-2">${message}</div>`,
    icon: 'error',
    iconColor: '#ef4444',
    confirmButtonText: 'Fermer',
    confirmButtonColor: '#b91c1c',
    customClass: {
      popup: 'rounded-3xl shadow-2xl border border-red-100 p-6 dark:bg-gray-800 dark:text-white',
      title: 'text-xl font-extrabold text-red-600',
      confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg'
    }
  })
}

export const showConfirmDialog = (title, message, confirmText = 'Oui, continuer') => {
  return Swal.fire({
    title: title,
    html: `<div class="text-sm text-gray-600 dark:text-gray-300 mt-2">${message}</div>`,
    icon: 'warning',
    iconColor: '#f59e0b',
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: 'Annuler',
    confirmButtonColor: '#b91c1c',
    cancelButtonColor: '#6b7280',
    customClass: {
      popup: 'rounded-3xl shadow-2xl border border-amber-100 p-6 dark:bg-gray-800 dark:text-white',
      title: 'text-xl font-extrabold text-amber-600',
      confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-md',
      cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-xs border'
    }
  })
}
