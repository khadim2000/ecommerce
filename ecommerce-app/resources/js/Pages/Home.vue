<template>
  <div>
    <!-- Header pour utilisateurs non connectés -->
    <GuestLayout />

    <!-- Fond avec gradient subtil -->
    <main class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
      <div class="container mx-auto py-8 px-4">
        
        <!-- Titre du dashboard public -->
       
        
        
        <!-- Messages flash améliorés -->
        <div class="mb-6 space-y-3">
          <div v-if="$page.props.flash?.success" class="card bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 animate-fade-in">
            <div class="p-4 flex items-center space-x-3">
              <i class="fas fa-check-circle text-2xl text-green-600"></i>
              <div class="text-green-800 font-medium">{{ $page.props.flash.success }}</div>
            </div>
          </div>
          
          <div v-if="$page.props.flash?.error" class="card bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 animate-fade-in">
            <div class="p-4 flex items-center space-x-3">
              <i class="fas fa-times-circle text-2xl text-red-600"></i>
              <div class="text-red-800 font-medium">{{ $page.props.flash.error }}</div>
            </div>
          </div>
        </div>
        
        <!-- Message de recherche amélioré -->
        <div v-if="search" class="mb-6 animate-fade-in">
          <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200">
            <div class="p-4 flex items-center space-x-3">
              <i class="fas fa-search text-2xl text-blue-600"></i>
              <div class="text-blue-800 font-medium">
                Résultats pour : "{{ search }}"
              </div>
            </div>
          </div>
        </div>

        <!-- Section des filtres modernisée -->
        <div class="mb-8">
          <div class="card p-6">
            <!-- Filtres par catégorie -->
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                🏷️ Catégories
              </h3>
              <div class="flex flex-wrap gap-3">
                <button
                  v-for="category in ['all', ...categories]"
                  :key="category"
                  :class="[
                    'px-4 py-2 rounded-full border text-sm font-medium capitalize transition-all duration-200',
                    selectedCategory === category
                      ? 'bg-gradient-to-r from-green-600 to-green-700 text-white border-green-600 shadow-md transform scale-105'
                      : 'bg-white text-gray-700 border-gray-300 hover:border-green-600 hover:bg-green-50 hover:text-green-600'
                  ]"
                  @click="selectedCategory = category"
                >
                  {{ category === 'all' ? '🌐 Toutes' : `📦 ${category}` }}
                </button>
          </div>
          </div>
          </div>
        </div>

        <!-- Grille de produits -->
        <ProductGrid 
          :products="filteredProducts"
          :user="auth?.user"
          @edit-product="handleEditProduct"
          @delete-product="handleDeleteProduct"
          @add-to-cart="handleAddToCart"
          @buy-now="handleBuyNow"
        />

        <!-- Message si aucun produit -->
        <div v-if="filteredProducts.length === 0" class="text-center py-12">
          <div class="card bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200">
            <div class="p-8">
              <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
              <h3 class="text-xl font-semibold text-gray-600 mb-2">
                {{ search ? 'Aucun produit trouvé' : 'Aucun produit disponible' }}
              </h3>
              <p class="text-gray-500">
                {{ all ? 'Essayez avec d\'autres mots-clés' : 'Revenez plus tard pour découvrir nos nouveaux produits' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modales -->
    <AddProduct 
      v-if="showAddProductForm"
      @close="showAddProductForm = false"
      @product-added="handleProductAdded"
    />

    <ProductEditComplete
      v-if="showEditForm && editingProduct"
      :product="editingProduct"
      @close="closeEditForm"
      @product-updated="handleProductUpdated"
    />

    <DeleteConfirmation
      v-if="showDeleteConfirmation && deletingProduct"
      :product="deletingProduct"
      @close="closeDeleteConfirmation"
      @product-deleted="handleProductDeleted"
    />

    <!-- Notifications Toast -->
    <Toast
      v-if="toast.show"
      :message="toast.message"
      :type="toast.type"
      @close="toast.show = false"
    />
       
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import GuestLayout from '../Layouts/GuestLayout.vue';
import ProductGrid from '../Components/ProductGrid.vue';
import AddProduct from '../Components/AddProduct.vue';
import ProductEditComplete from '../Components/ProductEditComplete.vue';
import DeleteConfirmation from '../Components/DeleteConfirmation.vue';
import Toast from '../Components/Toast.vue';

// Props
const props = defineProps({
  products: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  search: {
    type: String,
    default: ''
  },
  auth: {
    type: Object,
    default: () => ({})
  }
});

// États locaux
const products = ref([...props.products]); // on stocke localement les produits
const selectedCategory = ref('all');
const showAddProductForm = ref(false);
const showEditForm = ref(false);
const showDeleteConfirmation = ref(false);
const editingProduct = ref(null);
const deletingProduct = ref(null);
const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

// Filtrage par catégorie
const categories = computed(() => {
  return [...new Set(products.value.map(p => p.category?.name || 'Autre'))];
});

const filteredProducts = computed(() => {
  if (selectedCategory.value === 'all') return products.value;
  return products.value.filter(p => p.category?.name === selectedCategory.value);
});




// Affichage immédiat des données au montage
onMounted(() => {
  products.value = [...props.products]; 
});
</script>
