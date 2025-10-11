<template>
  <div v-if="hasError" class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
      <div class="mb-4">
        <i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>
      </div>
      <h2 class="text-xl font-bold text-gray-800 mb-2">Une erreur s'est produite</h2>
      <p class="text-gray-600 mb-4">{{ errorMessage }}</p>
      <button 
        @click="retry"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
      >
        <i class="fas fa-redo mr-2"></i>Réessayer
      </button>
    </div>
  </div>
  <slot v-else />
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';

const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((error) => {
  hasError.value = true;
  errorMessage.value = error.message || 'Une erreur inattendue s\'est produite';
  console.error('ErrorBoundary caught an error:', error);
  return false; // Empêche la propagation de l'erreur
});

const retry = () => {
  hasError.value = false;
  errorMessage.value = '';
  // Recharger la page pour réinitialiser l'état
  window.location.reload();
};
</script>