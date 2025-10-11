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
            class="text-gray-400 hover:text-gray-600 transition-colors"
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
                <div class="text-4xl font-bold text-green-600 mb-4">{{ product.price }}€</div>
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
                    class="flex items-center space-x-2 p-3 border border-gray-200 rounded-lg hover:border-green-300 transition-colors cursor-pointer"
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
                    :class="selectedSize === size 
                      ? 'px-4 py-2 border rounded-lg text-sm font-medium transition-colors bg-green-600 text-white border-green-600'
                      : 'px-4 py-2 border rounded-lg text-sm font-medium transition-colors bg-white text-gray-700 border-gray-300 hover:border-green-300'"
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
                      class="px-3 py-2 text-gray-600 hover:text-green-600 disabled:text-gray-400 disabled:cursor-not-allowed"
                    >
                      <i class="fas fa-minus"></i>
                    </button>
                    <span class="px-4 py-2 text-lg font-medium">{{ quantity }}</span>
                    <button 
                      @click="increaseQuantity"
                      :disabled="quantity >= Math.min(10, product.stock)"
                      class="px-3 py-2 text-gray-600 hover:text-green-600 disabled:text-gray-400 disabled:cursor-not-allowed"
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
              <div v-if="product.stock > 0" class="flex space-x-4 pt-6 border-t">
                <button
                    v-if="product.stock > 0"
                   @click.stop="handleAddToCart(product)"
                    
                    class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                >
                  <i class="fas fa-shopping-cart mr-2"></i>
                  Ajouter aun panier
                </button>
                
                <button
                  v-if="product.stock > 0"
    
                   @click.stop="handleBuyNow(product)"
                  
                  class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-md"
                >
                  <i class="fas fa-bolt mr-2"></i>
                  Acheter maintenant
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
      class="fixed top-4 right-4 z-60 bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm"
    >
      <div class="flex items-center space-x-3">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500'"></i>
        <span class="text-gray-800">{{ toast.message }}</span>
        <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
const auth = ref({ user: null })

const props = defineProps({
  show: { type: Boolean, default: false },
  product: { type: Object, default: null },
  auth: { type: Object, default: null },
  user: { type: Object, default: null },
})
// Emits
const isAuthenticated = () => {
   return props.user && props.user.id;
   };

const emit = defineEmits([ 'add-to-cart', 'buy-now','close'])

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

// Watchers
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    quantity.value = 1
    selectedColor.value = null
    selectedSize.value = null
    
    // Sélectionner automatiquement la première couleur si disponible
    if (newProduct.colors && newProduct.colors.length > 0) {
      selectedColor.value = newProduct.colors[0]
    }
  }
}, { immediate: true })

// Méthodes
const closeModal = () => {
  emit('close')
}





const increaseQuantity = () => {
  if (quantity.value < Math.min(10, props.product?.stock || 0)) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
};

const handleAddToCart = (product) => { 
  if (!isAuthenticated()) { 
    // Rediriger vers la page de login 
    router.visit('/login');
   return; 
   }
   
   const cartData = {
     productId: product.id,
     quantity: quantity.value,
     color: selectedColor.value,
     size: selectedSize.value
   };
   
   console.log('Données envoyées au panier:', cartData);
   
   // Émettre l'événement pour l'ajout au panier avec toutes les options sélectionnées
   emit('add-to-cart', cartData);
   
   // Afficher un toast de confirmation
   showToast(`${quantity.value} article(s) ajouté(s) au panier !`);
};

const handleBuyNow = (product) => {
   if (!isAuthenticated()) { 
    // Rediriger vers la page de login 
    router.visit('/login');
   return; 
   }
   
   // Émettre l'événement buy-now avec toutes les options sélectionnées
   emit('buy-now', {
     productId: product.id,
     quantity: quantity.value,
     color: selectedColor.value,
     size: selectedSize.value
   });
   
   // Fermer la modal après l'achat
   closeModal();
};
const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }
  
  // Auto-hide après 3 secondes
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

</script>

