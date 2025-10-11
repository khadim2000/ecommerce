<template>
  <!-- Composant invisible pour gérer le token CSRF -->
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import axios from 'axios'

let refreshInterval = null

// Fonction pour mettre à jour le token CSRF
const updateCsrfToken = async () => {
  try {
    const response = await axios.get('/api/csrf-token', {
      withCredentials: true
    })
    if (response.data.csrf_token) {
      // Mettre à jour le token dans le header
      axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token
      
      // Mettre à jour le meta tag
      const metaTag = document.head.querySelector('meta[name="csrf-token"]')
      if (metaTag) {
        metaTag.setAttribute('content', response.data.csrf_token)
      }
      
      console.log('CSRF token updated successfully')
    }
  } catch (error) {
    console.error('Failed to update CSRF token:', error)
    // En cas d'erreur, recharger la page pour obtenir un nouveau token
    if (error.response && error.response.status === 419) {
      console.warn('CSRF token expired, reloading page...')
      window.location.reload()
    }
  }
}

onMounted(() => {
  // Mettre à jour le token toutes les 30 minutes
  refreshInterval = setInterval(updateCsrfToken, 30 * 60 * 1000)
  
  // Mettre à jour le token immédiatement
  updateCsrfToken()
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
