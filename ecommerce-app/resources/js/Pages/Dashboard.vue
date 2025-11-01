<template>
 
   
    <!-- Header conditionnel -->
    <Authenticated
      v-if="props.auth?.user"
      @openAddProduct="props.auth?.user?.role === 'admin' ? (showAddProductForm = true) : null"
      @openAddCategory="props.auth?.user?.role === 'admin' ? (showAddCategoryForm = true) : null"
    />


    <!-- Fond avec gradient subtil -->
    <main class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
      <div class="container mx-auto py-8 px-4">
        

        
     
        <!-- Message de recherche amélioré -->
        <div v-if="search" class="mb-6 animate-fade-in">
          <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200">
            <div class="p-4 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="text-2xl">🔍</div>
                <div>
                  <p class="text-blue-800 font-medium">
                    Résultats de recherche pour : <strong>"{{ search }}"</strong>
                  </p>
                  <p class="text-blue-600 text-sm">{{ filteredProducts.length }} produit(s) trouvé(s)</p>
                </div>
              </div>
           
            </div>
          </div>
        </div>
        
        <!-- Section des filtres modernisée -->
        <div class="mb-8">
          <div class="card p-6">
            <!-- Filtres par catégorie -->
            <div>
           
              <div class="flex flex-wrap gap-4">
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

        <!-- Liste des produits -->
        <ProductGrid
          :products="filteredProducts"
          :user="props.auth.user"
          @edit-product="handleEditProduct"
          @delete-product="handleDeleteProduct"
          @add-to-cart="addToCart"
          @buy-now="handleBuyNow"
          @view-product="handleViewProduct"
        />

        <!-- Formulaires -->
        <ProductEditComplete
          v-if="editingProductId && props.auth?.user?.role === 'admin'"
          :product-id="editingProductId"
          @close="editingProductId = null"
          @updated="handleProductUpdated"
        />

        <AddProduct
          v-if="showAddProductForm && props.auth?.user?.role === 'admin'"
          @close="showAddProductForm = false"
          @product-added="handleProductAdded"
        />

        <AddCategory
          v-if="showAddCategoryForm && props.auth?.user?.role === 'admin'"
          @close="showAddCategoryForm = false"
          @category-added="handleCategoryAdded"
        />

        <!-- Toast notifications -->
        <Toast
          :show="toast.show"
          :message="toast.message"
          :type="toast.type"
          @close="toast.show = false"
        />

        <!-- Confirmation de suppression -->
        <DeleteConfirmation
          :show="showDeleteConfirmation"
          :product="productToDelete"
          :loading="isDeletingProduct"
          @confirm="confirmDelete"
          @cancel="cancelDelete"
        />

        
        

      </div>
    </main>
</template>
<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ProductGrid from "../Components/ProductGrid.vue";
import Authenticated from "../Layouts/AuthenticatedLayout.vue";
import GuestLayout from "../Layouts/GuestLayout.vue";
import AddProduct from "../Components/AddProduct.vue";
import AddCategory from "@/Components/AddCategory.vue";
import ProductEditComplete from "../Components/ProductEditComplete.vue";
import ProductDetailModal from "../Components/ProductDetailModal.vue";
import Toast from "../Components/Toast.vue";
import ErrorBoundary from "../Components/ErrorBoundary.vue";
import DeleteConfirmation from "../Components/DeleteConfirmation.vue";
import axios from 'axios';

// Props envoyés depuis Laravel via Inertia
const props = defineProps({
  products: {
    type: Array,
    default: () => [] // très important si Laravel n'envoie rien
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
})


const selectedCategory = ref('all');
const showAddProductForm = ref(false);
const showAddCategoryForm = ref(false);
const editingProductId = ref(null);
const showDeleteConfirmation = ref(false);
const productToDelete = ref(null);
const isDeletingProduct = ref(false);
const showProductDetail = ref(false);
const selectedProduct = ref(null);

// Toast notifications
const toast = ref({
  show: false,
  message: '',
  type: 'info'
});


const categories = computed(() => {
  return [...new Set(props.products.map(p => p.category?.name || 'Autre'))];
});


const filteredProducts = computed(() => {
  if (selectedCategory.value === 'all') return props.products;
  return props.products.filter(p => p.category?.name === selectedCategory.value);
});


function addToCart(productData) {
  try {
    console.log('Données reçues dans addToCart:', productData);
    
    // Vérifier si l'utilisateur est connecté
    if (!props.auth?.user) {
      router.visit(route('login'));
      return;
    }

    // Utiliser Inertia.js pour l'ajout au panier (gère automatiquement le CSRF)
    const cartData = typeof productData === 'object' && productData.productId 
      ? {
          product_id: productData.productId,
          quantity: productData.quantity || 1,
          size: productData.size || null,
          color: productData.color?.name || null
        }
      : {
          product_id: productData,
          quantity: 1,
          size: null,
          color: null
        };
        
    console.log('Données envoyées à l\'API:', cartData);

    router.post('/api/cart/add', cartData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        console.log('Succès - Produit ajouté au panier');

        // Notifier le panier flottant de la mise à jour
        window.dispatchEvent(new CustomEvent('cart-updated'));
      },
      onError: (errors) => {
        console.error('Erreurs lors de l\'ajout au panier:', errors);
        if (errors.message) {
          showToast(`Erreur: ${errors.message}`, 'error');
        } else {
          showToast('Erreur lors de l\'ajout au panier', 'error');
        }
      }
    });
  } catch (error) {
    // Erreur inattendue
    showToast('Une erreur inattendue s\'est produite', 'error');
  }
}







function clearSearch() {
  router.visit('/', {}, {
    preserveState: false,
    preserveScroll: false
  });
}

function handleProductAdded(newProduct) {
  // Ajouter le nouveau produit à la liste
  props.products.unshift(newProduct); // Ajouter au début pour le voir en premier
  
  // Fermer le formulaire
  showAddProductForm.value = false;
  
  // Afficher une notification toast
  showToast(`Produit "${newProduct.name}" ajouté avec succès !`, 'success');
}

function handleCategoryAdded(newCategory) {
  // Ajouter la nouvelle catégorie à la liste
  if (!categories.value.includes(newCategory.name)) {
    categories.value.push(newCategory.name);
  }
  
  // Fermer le formulaire
  showAddCategoryForm.value = false;
  
  // Afficher une notification toast
  showToast(`Catégorie "${newCategory.name}" ajoutée avec succès !`, 'success');
}

function showToast(message, type = 'info') {
  toast.value = {
    show: true,
    message,
    type
  };
}

async function handleEditProduct(productId) {
  try {
    // Vérifier que l'utilisateur est admin
    if (!props.auth?.user || props.auth.user.role !== 'admin') {
      showToast('Accès refusé. Seuls les administrateurs peuvent modifier les produits.', 'error');
      return;
    }
    editingProductId.value = productId;
    showToast('Mode édition activé', 'info');
  } catch (error) {
    showToast('Erreur lors de l\'édition du produit', 'error');
  }
}

function handleDeleteProduct(productId) {
  if (!props.auth?.user || props.auth.user.role !== 'admin') {
    showToast('Accès refusé. Seuls les administrateurs peuvent supprimer les produits.', 'error');
    return;
  }
  const product = props.products.find(p => p.id === productId);
  if (product) {
    productToDelete.value = product;
    showDeleteConfirmation.value = true;
  }
}

async function confirmDelete() {
  try {
    if (!productToDelete.value) return;
    isDeletingProduct.value = true;
    const response = await axios.delete(`/admin/products/${productToDelete.value.id}`, {
   
    });
    if (response.status === 200) {
      // Reload the page to get updated product list
      router.reload();
      showToast('Produit supprimé avec succès', 'success');
    }
  } catch (error) {
    if (error.response?.status === 401) {
      showToast('Non authentifié. Veuillez vous reconnecter.', 'error');
    } else if (error.response?.status === 403) {
      showToast('Accès refusé. Seuls les administrateurs peuvent supprimer les produits.', 'error');
    } else if (error.response?.status === 404) {
      showToast('Produit non trouvé', 'error');
    } else {
      showToast('Erreur lors de la suppression du produit', 'error');
    }
  } finally {
    isDeletingProduct.value = false;
    showDeleteConfirmation.value = false;
    productToDelete.value = null;
  }
}

function cancelDelete() {
  showDeleteConfirmation.value = false;
  productToDelete.value = null;
}

function handleProductUpdated(updatedProduct) {
  // Reload the page to get updated product list
  router.reload();
  editingProductId.value = null;
  showToast('Produit mis à jour avec succès', 'success');
}

function handleViewProduct(product) {
  selectedProduct.value = product;
  showProductDetail.value = true;
}

function closeProductDetail() {
  showProductDetail.value = false;
  selectedProduct.value = null;
}

function handleBuyNow(productData) {
  // D'abord ajouter le produit au panier avec les options sélectionnées
  addToCart(productData);
  
  // Puis rediriger vers le panier après un petit délai pour laisser l'ajout se faire
  setTimeout(() => {
    router.visit('/cart');
  }, 500);
}
</script>
