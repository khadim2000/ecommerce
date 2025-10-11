<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
      <!-- Header -->
      <div class="bg-red-50 border-b border-red-200 p-6 rounded-t-xl">
        <div class="flex items-center space-x-3">
          <div class="bg-red-100 p-3 rounded-full">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-red-800">Confirmer la suppression</h3>
            <p class="text-red-600 text-sm">Cette action est irréversible</p>
          </div>
        </div>
      </div>

      <!-- Contenu -->
      <div class="p-6">
        <div v-if="product" class="mb-6">
          <!-- Informations du produit -->
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-3">
              <div v-if="product.image" class="w-16 h-16 rounded-lg overflow-hidden">
                <img :src="product.image" :alt="product.name" class="w-full h-full object-cover">
              </div>
              <div v-else class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                <i class="fas fa-image text-gray-400"></i>
              </div>
              <div class="flex-1">
                <h4 class="font-semibold text-gray-800">{{ product.name }}</h4>
                <p class="text-sm text-gray-600">{{ product.price }}€</p>
                <p v-if="product.category" class="text-xs text-green-600">{{ product.category.name }}</p>
              </div>
            </div>
          </div>

          <!-- Message de confirmation -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
              <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
              <div>
                <p class="text-yellow-800 font-medium mb-1">Attention !</p>
                <p class="text-yellow-700 text-sm">
                  Vous êtes sur le point de supprimer définitivement ce produit. 
                  Cette action supprimera également toutes les données associées (commandes, avis, etc.).
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex space-x-3 pt-4">
          <button 
            @click="$emit('cancel')"
            :disabled="loading"
            class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i class="fas fa-times mr-2"></i>Annuler
          </button>
          <button 
            @click="$emit('confirm')"
            :disabled="loading"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <i v-if="!loading" class="fas fa-trash mr-2"></i>
            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Suppression...' : 'Supprimer définitivement' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  product: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['confirm', 'cancel']);
</script>