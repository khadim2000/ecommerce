<template>
  <div class="min-h-screen bg-gray-50">
    <Authenticated />
    
    <main class="container mx-auto py-8 px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Mon Panier</h1>
        
        <div v-if="cartItems.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">🛒</div>
          <h2 class="text-xl font-semibold text-gray-600 mb-2">Votre panier est vide</h2>
          <p class="text-gray-500 mb-6">Découvrez nos produits et ajoutez-les à votre panier</p>
          <Link :href="route('dashboard')" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
            Continuer mes achats
          </Link>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Liste des articles -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border">
              <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">Articles dans votre panier</h2>
                
                <div class="space-y-4">
                  <div 
                    v-for="item in cartItems" 
                    :key="item.id"
                    class="flex items-center space-x-4 p-4 border rounded-lg"
                  >
                    <img 
                      :src="item.product.image || '/images/placeholder.jpg'" 
                      :alt="item.product.name"
                      class="w-20 h-20 object-cover rounded-lg"
                    >
                    
                    <div class="flex-1">
                      <h3 class="font-semibold text-gray-900">{{ item.product.name }}</h3>
                      <p class="text-sm text-gray-600">{{ item.product.description }}</p>
                      
                      <!-- Affichage couleur et taille -->
                      <div v-if="item.color || item.size" class="mt-2 space-y-1">
                        <p v-if="item.color" class="text-sm text-gray-500">
                          <span class="inline-block w-4 h-4 rounded-full border border-gray-300 mr-2" 
                                :style="{ backgroundColor: getColorHex(item.color) }"></span>
                          Couleur: {{ item.color }}
                        </p>
                        <p v-if="item.size" class="text-sm text-gray-500">
                          Taille: {{ item.size }}
                        </p>
                      </div>
                      
                      <p class="text-lg font-semibold text-green-600">{{ formatPrice(item.product.price) }}</p>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                      <button 
                        @click="updateQuantity(item.id, item.quantity - 1)"
                        :disabled="item.quantity <= 1"
                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50"
                      >
                        -
                      </button>
                      <span class="w-8 text-center">{{ item.quantity }}</span>
                      <button 
                        @click="updateQuantity(item.id, item.quantity + 1)"
                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                      >
                        +
                      </button>
                    </div>
                    
                    <div class="text-right">
                      <p class="font-semibold">{{ formatPrice(item.product.price * item.quantity) }}</p>
                      <button 
                        @click="removeItem(item.id)"
                        class="text-red-500 hover:text-red-700 text-sm mt-1"
                      >
                        Supprimer
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Résumé de la commande -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
              <h2 class="text-lg font-semibold mb-4">Résumé de la commande</h2>
              
              <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                  <span>Sous-total</span>
                  <span>{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Livraison</span>
                  <span class="text-green-600">Gratuite</span>
                </div>
                <div class="border-t pt-3">
                  <div class="flex justify-between font-semibold text-lg">
                    <span>Total</span>
                    <span>{{ formatPrice(subtotal) }}</span>
                  </div>
                </div>
              </div>
              
              <button 
                @click="proceedToCheckout"
                :disabled="checkoutLoading || cartItems.length === 0"
                class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="checkoutLoading">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                  Redirection...
                </span>
                <span v-else>
                  Passer la commande
                </span>
              </button>

              <!-- Méthodes de paiement acceptées -->
              <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">Moyens de paiement acceptés :</p>
                <div class="flex items-center space-x-2">
                  <div class="flex items-center space-x-1 bg-orange-100 px-2 py-1 rounded text-xs">
                    <img src="/images/payment/orange-money.svg" alt="Orange Money" class="w-4 h-4" />
                    <span class="text-orange-700">Orange</span>
                  </div>
                  <div class="flex items-center space-x-1 bg-blue-100 px-2 py-1 rounded text-xs">
                    <img src="/images/payment/wave.svg" alt="Wave" class="w-4 h-4" />
                    <span class="text-blue-700">Wave</span>
                  </div>
                  <div class="flex items-center space-x-1 bg-blue-100 px-2 py-1 rounded text-xs">
                    <img src="/images/payment/visa.svg" alt="Visa" class="w-4 h-4" />
                    <span class="text-blue-700">Visa</span>
                  </div>
                  <div class="flex items-center space-x-1 bg-green-100 px-2 py-1 rounded text-xs">
                    <i class="fas fa-truck text-green-500"></i>
                    <span class="text-green-700">Cash</span>
                  </div>
                </div>
              </div>
              
              <Link 
                :href="route('dashboard')" 
                class="block text-center text-gray-600 hover:text-gray-800 mt-4"
              >
                Continuer mes achats
              </Link>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Authenticated from '../Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const cartItems = ref([])
const loading = ref(false)
const checkoutLoading = ref(false)

const subtotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.product.price * item.quantity)
  }, 0)
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-SN', {
    style: 'currency',
    currency: 'XOF' // FCFA correspond au code XOF
  }).format(price)
}
const getColorHex = (colorName) => {
  // Mapping complet des couleurs
  const colorMap = {
    // Couleurs primaires
    'Rouge': '#FF0000',
    'Bleu': '#0000FF',
    'Vert': '#00FF00',
    'Jaune': '#FFFF00',
    
    // Couleurs secondaires
    'Orange': '#FFA500',
    'Violet': '#800080',
    'Rose': '#FFC0CB',
    'Cyan': '#00FFFF',
    
    // Couleurs neutres
    'Noir': '#000000',
    'Blanc': '#FFFFFF',
    'Gris': '#808080',
    'Gris foncé': '#404040',
    'Gris clair': '#C0C0C0',
    
    // Couleurs terre
    'Marron': '#8B4513',
    'Beige': '#F5F5DC',
    'Crème': '#FFFDD0',
    'Taupe': '#8B7D6B',
    
    // Bleus variés
    'Bleu marine': '#000080',
    'Bleu ciel': '#87CEEB',
    'Bleu turquoise': '#40E0D0',
    'Bleu royal': '#4169E1',
    'Bleu acier': '#4682B4',
    
    // Rouges variés
    'Rouge foncé': '#8B0000',
    'Rouge bordeaux': '#800020',
    'Rouge cerise': '#DE3163',
    'Rouge corail': '#FF7F50',
    'Rouge tomate': '#FF6347',
    
    // Verts variés
    'Vert foncé': '#006400',
    'Vert lime': '#32CD32',
    'Vert menthe': '#98FB98',
    'Vert olive': '#808000',
    'Vert émeraude': '#50C878',
    'Vert forêt': '#228B22',
    
    // Violets variés
    'Violet foncé': '#4B0082',
    'Lavande': '#E6E6FA',
    'Mauve': '#E0B0FF',
    'Indigo': '#4B0082',
    
    // Jaunes variés
    'Jaune doré': '#FFD700',
    'Jaune citron': '#FFFACD',
    'Jaune moutarde': '#FFDB58',
    'Jaune canari': '#FFFF99',
    
    // Oranges variés
    'Orange foncé': '#FF8C00',
    'Orange pêche': '#FFCCCB',
    'Orange abricot': '#FBCEB1',
    'Orange mandarine': '#F28500',
    
    // Couleurs métalliques
    'Or': '#FFD700',
    'Argent': '#C0C0C0',
    'Bronze': '#CD7F32',
    'Cuivre': '#B87333',
    
    // Couleurs pastel
    'Rose pastel': '#FFB6C1',
    'Bleu pastel': '#ADD8E6',
    'Vert pastel': '#98FB98',
    'Violet pastel': '#DDA0DD',
    'Jaune pastel': '#FFFFE0',
    
    // Couleurs spéciales
    'Magenta': '#FF00FF',
    'Turquoise': '#40E0D0',
    'Sarcelle': '#008080',
    'Fuchsia': '#FF00FF',
    'Chartreuse': '#7FFF00'
  }
  return colorMap[colorName] || '#CCCCCC'
}

const fetchCartItems = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/cart')
    cartItems.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement du panier:', error)
    // Si l'utilisateur n'est pas authentifié, rediriger vers la page de connexion
    if (error.response?.status === 401) {
      router.visit('/login')
    }
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (itemId, newQuantity) => {
  if (newQuantity < 1) return
  
  try {
    const response = await axios.put(`/api/cart/${itemId}`, {
      quantity: newQuantity
    })
    
    // Mettre à jour l'élément dans la liste
    const index = cartItems.value.findIndex(item => item.id === itemId)
    if (index !== -1) {
      cartItems.value[index] = response.data
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
  }
}

const removeItem = async (itemId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) return
  
  try {
    await axios.delete(`/api/cart/${itemId}`)
    cartItems.value = cartItems.value.filter(item => item.id !== itemId)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const proceedToCheckout = () => {
  // Vérifier si le panier n'est pas vide
  if (cartItems.value.length === 0) {
    alert('Votre panier est vide. Ajoutez des produits avant de passer commande.');
    return;
  }
  
  // Activer le loading
  checkoutLoading.value = true;
  
  // Rediriger vers la page de checkout
  console.log('Redirection vers checkout...');
  router.visit('/checkout', {
    method: 'get',
    onError: (errors) => {
      console.error('Erreur lors de la redirection vers checkout:', errors);
      alert('Erreur lors de l\'accès au checkout. Veuillez réessayer.');
      checkoutLoading.value = false;
    },
    onSuccess: () => {
      console.log('Redirection vers checkout réussie');
      // Le loading sera désactivé automatiquement lors du changement de page
    },
    onFinish: () => {
      checkoutLoading.value = false;
    }
  });
}

onMounted(() => {
  fetchCartItems()
})
</script>
