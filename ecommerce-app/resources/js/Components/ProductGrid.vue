<template>
  <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <div 
      v-for="product in products" 
      :key="product.id"
      class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-2 border-green-300 overflow-hidden hover:border-green-500 hover:shadow-green-100 hover:bg-green-50/30 cursor-pointer product-card"
    >
      <!-- Image du produit -->
      <div class="relative h-48 bg-gray-100 overflow-hidden">
        <div v-if="product.stock > 0" class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
          <i class="fas fa-check mr-1"></i>Disponible
        </div>
        <div v-else class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
          <i class="fas fa-times mr-1"></i>Rupture
        </div>

        <img 
          v-if="product.image" 
          :src="product.image" 
          :alt="product.name"
          @click="openModal(product)"
          class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
        />
        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-green-50 to-green-100">
          <i class="fas fa-image text-4xl text-green-400"></i>
        </div>

        <!-- Badge admin -->
        <div v-if="user?.role === 'admin' && user" class="absolute top-2 right-2 flex space-x-1">
          <button 
            @click="$emit('edit-product', product.id)"
            class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-full shadow-lg transition-all duration-200 transform hover:scale-110"
            title="Modifier le produit"
          >
            <i class="fas fa-edit text-sm"></i>
          </button>
          <button 
            @click="$emit('delete-product', product.id)"
            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transition-all duration-200 transform hover:scale-110"
            title="Supprimer le produit"
          >
            <i class="fas fa-trash text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Contenu du produit -->
      <div class="p-4">
        <h3 @click="openModal(product)" class="text-lg font-bold text-gray-800 mb-1 line-clamp-2 cursor-pointer hover:text-green-600">
          {{ product.name }}
        </h3>

        <div class="flex items-center justify-between mb-3">
          <span class="text-2xl font-bold text-green-600">{{ product.price }} FCFA</span>
          <span v-if="product.stock <= 0" class="text-sm text-red-500 font-medium">
            <i class="fas fa-exclamation-triangle mr-1"></i>Rupture de stock
          </span>
        </div>

        <p v-if="product.description" class="text-gray-600 text-sm mb-3 line-clamp-2">
          {{ product.description }}
        </p>

        <div v-if="product.category" class="mb-3">
          <span class="inline-block bg-gradient-to-r from-green-100 to-green-200 text-green-700 text-xs font-medium px-3 py-1 rounded-full border border-green-300">
            <i class="fas fa-tag mr-1"></i>{{ product.category.name }}
          </span>
        </div>

        <div class="flex space-x-2">
          <button 
            v-if="product.stock > 0"
            @click.stop="handleAddToCart(product)"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-md"
          >
            <i class="fas fa-shopping-cart mr-2"></i>Ajouter
          </button>

    

          <button 
            v-else
            disabled
            class="flex-1 bg-gray-300 text-gray-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed"
          >
            <i class="fas fa-ban mr-2"></i>Indisponible
          </button>
        </div>
      </div>
    </div>

    <!-- Modale produit -->
    <ProductDetailModal 
      :show="modalVisible" 
      :product="selectedProduct" 
      :auth="auth"
     
  @add-to-cart="handleAddToCart"
  @buy-now="handleBuyNow"
      @close="modalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import ProductDetailModal from './ProductDetailModal.vue'

const modalVisible = ref(false)
const selectedProduct = ref(null)
const auth = ref({ user: null })
const showCart = ref(false)

const openModal = (product) => {
  selectedProduct.value = product
  modalVisible.value = true
}


const props = defineProps({
  products: {
    type: Array,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
});
const isAuthenticated = () => {
   return props.user && props.user.id;
   };

const emit = defineEmits(['edit-product', 'delete-product', 'add-to-cart', 'buy-now'])
const handleAddToCart = (product) => { 
  if (!isAuthenticated()) { 
    // Rediriger vers la page de login 
    router.visit('/login');
   return; 
   }
   // Émettre l'événement pour l'ajout au panier 
   emit('add-to-cart', product.id); 
};

const handleBuyNow = (productId) => {
   if (!isAuthenticated()) { 
    // Rediriger vers la page de login 
    router.visit('/login');
   return; 
   }
   // Émettre l'événement buy-now vers le parent
   emit('buy-now', productId);
};

</script>


