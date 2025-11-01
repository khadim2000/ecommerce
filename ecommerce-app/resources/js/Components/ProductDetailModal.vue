<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
          <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-info-circle text-green-600 mr-3"></i>
            Détails du produit
          </h2>
          <button 
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
            aria-label="Fermer"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <!-- Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-120px)]">
          <div v-if="product" class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
            <!-- Image du produit -->
            <div class="space-y-4">
              <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden shadow-lg">
                <img 
                  v-if="product.image" 
                  :src="product.image" 
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-green-50 to-green-100">
                  <i class="fas fa-image text-6xl text-green-400"></i>
                </div>
              </div>
              
              <!-- Statut du stock -->
              <div class="flex items-center justify-center">
                <div v-if="product.stock > 0" class="flex items-center text-green-600 bg-green-50 px-4 py-2 rounded-full">
                  <i class="fas fa-check-circle mr-2"></i>
                  <span class="font-medium">{{ product.stock }} en stock</span>
                </div>
                <div v-else class="flex items-center text-red-500 bg-red-50 px-4 py-2 rounded-full">
                  <i class="fas fa-times-circle mr-2"></i>
                  <span class="font-medium">Rupture de stock</span>
                </div>
              </div>
            </div>

            <!-- Informations du produit -->
            <div class="space-y-6">
              <!-- Nom et prix -->
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                <div class="text-4xl font-bold text-green-600 mb-4">{{ formatPrice(product.price) }}</div>
              </div>

              <!-- Description -->
              <div v-if="product.description">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
              </div>

              <!-- Catégorie -->
              <div v-if="product.category">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Catégorie</h3>
                <span class="inline-block bg-gradient-to-r from-green-100 to-green-200 text-green-700 text-sm font-medium px-4 py-2 rounded-full border border-green-300">
                  <i class="fas fa-tag mr-2"></i>{{ product.category.name }}
                </span>
              </div>

              <!-- Couleurs -->
              <div v-if="product.colors && product.colors.length > 0">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Couleurs disponibles</h3>
                <div class="flex flex-wrap gap-3">
                  <div 
                    v-for="color in product.colors" 
                    :key="color.id"
                    class="flex items-center space-x-2 p-3 border border-gray-200 rounded-lg hover:border-green-300 transition-colors duration-200 cursor-pointer"
                    :class="{ 'border-green-500 bg-green-50': selectedColor?.id === color.id }"
                    @click="selectedColor = color"
                  >
                    <div 
                      class="w-6 h-6 rounded-full border border-gray-300 shadow-sm"
                      :style="{ backgroundColor: color.hex_code }"
                    ></div>
                    <span class="text-sm font-medium text-gray-700">{{ color.name }}</span>
                  </div>
                </div>
              </div>

              <!-- Tailles -->
              <div v-if="product.size && product.size.length > 0">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Tailles disponibles</h3>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="size in product.size"
                    :key="size"
                    @click="selectedSize = size"
                    :class="[
                      'px-4 py-2 border rounded-lg text-sm font-medium transition-colors duration-200',
                      selectedSize === size 
                        ? 'bg-green-600 text-white border-green-600' 
                        : 'bg-white text-gray-700 border-gray-300 hover:border-green-300'
                    ]"
                  >
                    {{ size }}
                  </button>
                </div>
              </div>

              <!-- Quantité -->
              <div v-if="product.stock > 0">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Quantité</h3>
                <div class="flex items-center space-x-4">
                  <div class="flex items-center border border-gray-300 rounded-lg">
                    <button 
                      @click="decreaseQuantity"
                      :disabled="quantity <= 1"
                      class="px-3 py-2 text-gray-600 hover:text-green-600 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
                      aria-label="Réduire la quantité"
                    >
                      <i class="fas fa-minus"></i>
                    </button>
                    <span class="px-4 py-2 text-lg font-medium min-w-[3rem] text-center">{{ quantity }}</span>
                    <button 
                      @click="increaseQuantity"
                      :disabled="quantity >= Math.min(10, product.stock)"
                      class="px-3 py-2 text-gray-600 hover:text-green-600 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
                      aria-label="Augmenter la quantité"
                    >
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                  <span class="text-sm text-gray-500">
                    Maximum: {{ Math.min(10, product.stock) }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div v-if="product.stock > 0" class="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                <button
                  @click.stop="handleAddToCart"
                  :disabled="loading"
                  class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center justify-center"
                >
                  <i class="fas fa-shopping-cart mr-2"></i>
                  <span v-if="loading">Ajout en cours...</span>
                  <span v-else>Ajouter au panier</span>
                </button>
                
                <button
                  @click.stop="handleBuyNow"
                  :disabled="loading"
                  class="flex-1 bg-green-700 hover:bg-green-800 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-md flex items-center justify-center"
                >
                  <i class="fas fa-bolt mr-2"></i>
                  <span v-if="loading">Traitement...</span>
                  <span v-else>Acheter maintenant</span>
                </button>
              </div>

              <!-- Message de rupture de stock -->
              <div v-else class="pt-6 border-t">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                  <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                  <p class="text-red-700 font-medium">Ce produit est actuellement en rupture de stock</p>
                  <p class="text-red-600 text-sm mt-1">Revenez plus tard pour le commander</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de notification -->
    <div 
      v-if="toast.show"
      class="fixed top-4 right-4 z-60 bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm transition-all duration-300"
      :class="toast.show ? 'animate-fade-in' : 'animate-fade-out'"
    >
      <div class="flex items-center space-x-3">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle text-green-500 text-xl' : 'fas fa-exclamation-circle text-red-500 text-xl'"></i>
        <span class="text-gray-800 flex-1">{{ toast.message }}</span>
        <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  product: { type: Object, default: null },
  user: { type: Object, default: null },
})

const emit = defineEmits(['close'])

// État local
const loading = ref(false)
const quantity = ref(1)
const selectedColor = ref(null)
const selectedSize = ref(null)
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

// Computed
const isAuthenticated = computed(() => {
  return props.user && props.user.id
})

const maxQuantity = computed(() => {
  return props.product ? Math.min(10, props.product.stock) : 1
})

// Watchers
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    resetSelections()
    
    // Sélectionner automatiquement la première couleur si disponible
    if (newProduct.colors && newProduct.colors.length > 0) {
      selectedColor.value = newProduct.colors[0]
    }
    
    // Sélectionner automatiquement la première taille si disponible
    if (newProduct.size && newProduct.size.length > 0) {
      selectedSize.value = newProduct.sizes[0]
    }
  }
}, { immediate: true })

// Méthodes
const closeModal = () => {
  resetSelections()
  emit('close')
}

const resetSelections = () => {
  quantity.value = 1
  selectedColor.value = null
  selectedSize.value = null
}

const increaseQuantity = () => {
  if (quantity.value < maxQuantity.value) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('sn-SN', {
    style: 'currency',
    currency: 'XOF'
  }).format(price)
}

const handleAddToCart = async () => { 
  if (!isAuthenticated.value) { 
    router.visit('/login')
    return
  }

  if (!validateSelections()) {
    return
  }

  loading.value = true

  try {
    const cartData = {
      product_id: props.product.id,
      quantity: quantity.value,
      color: selectedColor.value?.name || null,
      size: selectedSize.value || null
    }

    // Utiliser axios qui gère automatiquement le CSRF token
    await axios.post('/api/cart/add', cartData)
     
 
    // Rafraîchir les données du panier dans le FloatingCart
    window.dispatchEvent(new CustomEvent('cart-updated'))
      
  } catch (error) {
    console.error('Erreur:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'ajout au panier'
    showToast(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

const handleBuyNow = async () => {
  if (!isAuthenticated.value) { 
    router.visit('/login')
    return
  }

  if (!validateSelections()) {
    return
  }

  loading.value = true

  try {
    const cartData = {
      product_id: props.product.id,
      quantity: quantity.value,
      color: selectedColor.value?.name || null,
      size: selectedSize.value || null
    }

    // Utiliser axios qui gère automatiquement le CSRF token
    await axios.post('/api/cart/add', cartData)
    
    // Rediriger vers la page de commande après ajout réussi
    router.visit('/checkout')
      
  } catch (error) {
    console.error('Erreur:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'achat'
    showToast(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

const validateSelections = () => {
  if (props.product.colors && props.product.colors.length > 0 && !selectedColor.value) {
    showToast('Veuillez sélectionner une couleur', 'error')
    return false
  }

  if (props.product.sizes && props.product.sizes.length > 0 && !selectedSize.value) {
    showToast('Veuillez sélectionner une taille', 'error')
    return false
  }

  return true
}

const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }
  
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

</script>

