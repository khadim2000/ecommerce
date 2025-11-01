<template>
  <div v-if="cartItemCount > 0" class="fixed bottom-6 right-6 z-50">
    <!-- Bouton panier flottant -->
    <button
      @click="toggleCart"
      class="bg-green-800 hover:bg-green-900 text-white p-4 rounded-full shadow-lg transition-all duration-300 hover:scale-110 relative"
    >
      <!-- Icône panier -->
    <span class="mr-2">🛒</span>
      
      <!-- Badge avec nombre d'articles -->
      <span
        v-if="cartItemCount > 0"
        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center animate-pulse"
      >
        {{ cartItemCount }}
      </span>
    </button>

    <!-- Panier déroulant -->
    <div
      v-if="showCart"
      class="absolute bottom-16 right-0 bg-white rounded-lg shadow-xl border border-gray-200 w-80 max-h-96 overflow-hidden"
    >
      <!-- Header du panier -->
      <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="font-semibold text-gray-800">Panier ({{ cartItemCount }})</h3>
          <button
            @click="showCart = false"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Contenu du panier -->
      <div class="max-h-64 overflow-y-auto">
        <div v-if="cartItems.length === 0" class="p-6 text-center text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
          </svg>
          <p>Votre panier est vide</p>
        </div>

        <div v-else class="p-2">
          <div
            v-for="item in cartItems"
            :key="item.id"
            class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg"
          >
            <!-- Image du produit -->
            <div class="w-12 h-12 bg-gray-200 rounded-lg flex-shrink-0">
              <img
                v-if="item.product?.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-full h-full object-cover rounded-lg"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>

            <!-- Détails du produit -->
            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-medium text-gray-900 truncate">
                {{ item.product?.name }}
              </h4>
              <p class="text-xs text-gray-500">
                Qté: {{ item.quantity }}
                <span v-if="item.color" class="ml-2">
                  <span class="inline-block w-3 h-3 rounded-full border border-gray-300 mr-1" 
                        :style="{ backgroundColor: getColorHex(item.color) }"></span>
                  {{ item.color }}
                </span>
                <span v-if="item.size" class="ml-2">Taille: {{ item.size }}</span>
              </p>
              <p class="text-sm font-semibold text-blue-600">
                {{ formatPrice(item.product?.price * item.quantity) }}
              </p>
            </div>

            <!-- Bouton supprimer -->
            <button
              @click="removeItem(item.id)"
              :disabled="removingItem === item.id"
              class="text-red-500 hover:text-red-700 p-1 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="removingItem !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer du panier -->
      <div v-if="cartItems.length > 0" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div class="flex justify-between items-center mb-3">
          <span class="font-semibold text-gray-800">Total:</span>
          <span class="font-bold text-lg text-blue-600">{{ formatPrice(totalPrice) }}</span>
        </div>
        <Link
          :href="route('checkout')"
          @click="showCart = false"
          class="w-full bg-green-800 hover:bg-green-900 text-white py-2 px-4 rounded-lg text-center block transition-colors"
        >
          Voir le panier
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'

const showCart = ref(false)
const cartItems = ref([])
const loading = ref(false)
const removingItem = ref(null)

// Handler pour l'événement cart-updated
const handleCartUpdated = () => {
  fetchCartItems()
}

// Computed properties
const cartItemCount = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0)
})

const totalPrice = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.product?.price * item.quantity)
  }, 0)
})

// Methods
const toggleCart = () => {
  showCart.value = !showCart.value
  if (showCart.value) {
    fetchCartItems()
  }
}

const fetchCartItems = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/cart')
    cartItems.value = response.data
    // Émettre un événement pour notifier les autres composants
    window.dispatchEvent(new CustomEvent('cart-updated', { 
      detail: { count: cartItemCount.value, items: cartItems.value } 
    }))
  } catch (error) {
    console.error('Erreur lors du chargement du panier:', error)
    // Si l'utilisateur n'est pas authentifié, vider le panier
    if (error.response?.status === 401) {
      cartItems.value = []
    }
  } finally {
    loading.value = false
  }
}

const removeItem = async (itemId) => {
  try {
    removingItem.value = itemId
    await axios.delete(`/api/cart/${itemId}`)
    cartItems.value = cartItems.value.filter(item => item.id !== itemId)
    // Émettre un événement pour notifier les autres composants
    window.dispatchEvent(new CustomEvent('cart-updated', { 
      detail: { count: cartItemCount.value, items: cartItems.value } 
    }))
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  } finally {
    removingItem.value = null
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-SN', {
    style: 'currency',
    currency: 'XOF' // FCFA correspond au code XOF
  }).format(price)
}

const getColorHex = (colorName) => {
  // Mapping des noms de couleurs vers leurs codes hex
  const colorMap = {
    'Rouge': '#FF0000',
    'Bleu': '#0000FF',
    'Vert': '#00FF00',
    'Jaune': '#FFFF00',
    'Orange': '#FFA500',
    'Violet': '#800080',
    'Rose': '#FFC0CB',
    'Cyan': '#00FFFF',
    'Noir': '#000000',
    'Blanc': '#FFFFFF',
    'Gris': '#808080',
    'Gris foncé': '#404040',
    'Gris clair': '#C0C0C0',
    'Marron': '#8B4513',
    'Beige': '#F5F5DC',
    'Crème': '#FFFDD0',
    'Taupe': '#8B7D6B',
    'Bleu marine': '#000080',
    'Bleu ciel': '#87CEEB',
    'Bleu turquoise': '#40E0D0',
    'Bleu royal': '#4169E1',
    'Bleu acier': '#4682B4',
    'Rouge foncé': '#8B0000',
    'Rouge bordeaux': '#800020',
    'Rouge cerise': '#DE3163',
    'Rouge corail': '#FF7F50',
    'Rouge tomate': '#FF6347',
    'Vert foncé': '#006400',
    'Vert citron': '#32CD32',
    'Vert menthe': '#98FB98',
    'Vert olive': '#808000',
    'Vert sauge': '#9CAF88',
    'Doré': '#FFD700',
    'Argenté': '#C0C0C0',
    'Bordeaux': '#800020',
    'Kaki': '#F0E68C',
    'Turquoise': '#40E0D0',
    'Corail': '#FF7F50',
    'Lavande': '#E6E6FA',
    'Pêche': '#FFDAB9',
    'Magenta': '#FF00FF',
    'Indigo': '#4B0082',
    'Ambre': '#FFBF00',
    'Émeraude': '#50C878'
  }
  
  return colorMap[colorName] || '#808080' // Gris par défaut si la couleur n'est pas trouvée
}

// Écouter les événements d'ajout au panier et charger le panier initial
onMounted(() => {
  window.addEventListener('cart-updated', handleCartUpdated)
  fetchCartItems() // Charger le panier au démarrage
})

onBeforeUnmount(() => {
  window.removeEventListener('cart-updated', handleCartUpdated)
})
</script>
