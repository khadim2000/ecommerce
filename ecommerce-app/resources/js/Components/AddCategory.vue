<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
          <i class="fas fa-folder-plus text-blue-500 mr-3"></i>
          Ajouter une catégorie
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Nom de la catégorie -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-tag mr-2 text-blue-500"></i>Nom de la catégorie *
          </label>
          <input
            v-model="name"
            type="text"
            required
            placeholder="Ex: Vêtements, Électronique, Maison..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
          />
        </div>

        <!-- Slug -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-link mr-2 text-green-500"></i>Slug (URL) *
          </label>
          <input
            v-model="slug"
            type="text"
            required
            placeholder="ex: vetements, electronique, maison"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
          />
          <p class="text-xs text-gray-500">Utilisé dans l'URL (sans espaces, en minuscules)</p>
        </div>

        <!-- Messages d'erreur/succès -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
            <span class="text-red-700 font-medium">{{ error }}</span>
          </div>
        </div>

        <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700 font-medium">Catégorie ajoutée avec succès !</span>
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex space-x-4 pt-6 border-t border-gray-200">
          <button 
            type="button" 
            @click="$emit('close')"
            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 font-medium"
          >
            <i class="fas fa-times mr-2"></i>Annuler
          </button>
          <button 
            type="submit"
            :disabled="loading"
            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-plus mr-2"></i>
            {{ loading ? 'Ajout en cours...' : 'Ajouter la catégorie' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'category-added']);

const name = ref('')
const slug = ref('')
const error = ref('')
const success = ref(false)
const loading = ref(false)

const submitForm = async () => {
  error.value = ''
  success.value = false
  loading.value = true

  if (!name.value.trim() || !slug.value.trim()) {
    error.value = 'Les deux champs sont obligatoires.'
    loading.value = false
    return
  }

  try {
    const response = await axios.post('/api/category', {
      name: name.value,
      slug: slug.value
    })
    success.value = true
    
    // Émettre l'événement pour mettre à jour la liste des catégories
    emit('category-added', response.data);
    
    // Reset form après un délai pour montrer le message de succès
    setTimeout(() => {
      name.value = ''
      slug.value = ''
      success.value = false
    }, 2000);
    
  } catch (e) {
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(' ')
    } else {
      error.value = "Erreur lors de l'ajout de la catégorie."
    }
  } finally {
    loading.value = false
  }
}
</script>
