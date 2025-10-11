<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
          <i class="fas fa-edit text-green-600 mr-3"></i>
          Modifier le produit
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6" v-if="product">
        <!-- Nom du produit -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-tag mr-2 text-green-600"></i>Nom du produit *
          </label>
          <input 
            v-model="form.name"
          
            type="text" 
            required
            placeholder="props.product.name"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
          />
        </div>

        <!-- Référence du produit -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-barcode mr-2 text-purple-500"></i>Référence du produit
          </label>
          <input 
            v-model="form.reference" 
            type="text" 
            placeholder="Ex: TSH-001, JEA-2024"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
          />
          <p class="text-xs text-gray-500">Code unique pour identifier le produit</p>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-align-left mr-2 text-blue-500"></i>Description
          </label>
          <textarea 
            v-model="form.description" 
            rows="3"
            placeholder="Décrivez votre produit..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
          ></textarea>
        </div>

        <!-- Prix et Stock -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              <i class="fas fa-euro-sign mr-2 text-yellow-500"></i>Prix (€) *
            </label>
            <input 
              v-model.number="form.price" 
              type="number" 
              step="0.01" 
              required
              placeholder="0.00"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
            />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              <i class="fas fa-box mr-2 text-purple-500"></i>Stock *
            </label>
            <input 
              v-model.number="form.stock" 
              type="number" 
              required
              placeholder="0"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
            />
          </div>
        </div>

        <!-- Tailles -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-ruler mr-2 text-orange-500"></i>Tailles disponibles *
          </label>
          <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
            <label 
              v-for="size in availableSizes" 
              :key="size"
              class="flex items-center space-x-2 p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors"
            >
              <input 
                type="checkbox" 
                :value="size" 
                v-model="form.size_ids"
                class="rounded border-gray-300 text-green-600 focus:ring-green-600"
              />
              <span class="text-sm font-medium">{{ size }}</span>
            </label>
          </div>
          <p class="text-xs text-gray-500">Cliquez sur les tailles pour les sélectionner (plusieurs choix possibles)</p>
        </div>

        <!-- Couleurs -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-palette mr-2 text-pink-500"></i>Couleurs disponibles *
          </label>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
            <label 
              v-for="color in colors" 
              :key="color.id"
              class="flex items-center space-x-2 p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors"
            >
              <input 
                type="checkbox" 
                :value="color.id" 
                v-model="form.color_ids"
                class="rounded border-gray-300 text-green-600 focus:ring-green-600"
              />
              <div 
                class="w-4 h-4 rounded-full border border-gray-300" 
                :style="{ backgroundColor: color.hex_code }"
              ></div>
              <span class="text-sm font-medium">{{ color.name }}</span>
            </label>
          </div>
          <p class="text-xs text-gray-500">Cliquez sur les couleurs pour les sélectionner (plusieurs choix possibles)</p>
          <div v-if="colors.length === 0" class="text-red-500 text-sm">
            Aucune couleur disponible. Veuillez d'abord ajouter des couleurs.
          </div>
        </div>

        <!-- Catégorie -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-folder mr-2 text-indigo-500"></i>Catégorie *
          </label>
          <select 
            v-model="form.category_id" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
          >
            <option value="" disabled>Sélectionnez une catégorie</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Image -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            <i class="fas fa-image mr-2 text-teal-500"></i>Image du produit
          </label>
          <input 
            v-model="form.image" 
            type="url" 
            placeholder="https://example.com/image.jpg"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent transition-all duration-200"
          />
          <p class="text-xs text-gray-500">URL de l'image du produit</p>
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
            <span class="text-green-700 font-medium">Produit modifié avec succès !</span>
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
            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 font-medium shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-save mr-2"></i>
            {{ loading ? 'Modification en cours...' : 'Sauvegarder les modifications' }}
          </button>
        </div>
      </form>

      <!-- Loading state -->
      <div v-else class="p-6 text-center">
        <i class="fas fa-spinner fa-spin text-2xl text-green-600 mb-4"></i>
        <p class="text-gray-600">Chargement des données du produit...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  }
});

const emit = defineEmits(['close', 'updated']);

const product = ref(null);
const form = ref({
  name: '',
  reference: '',
  description: '',
  price: null,
  stock: null,
  size_ids: [],
  color_ids: [],
  category_id: '',
  image: '',
});

const availableSizes = ref([
  'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
  '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50',
  '6', '8', '10', '12', '14', '16', '18', '20', '22', '24',
  '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46',
  'One Size', 'Taille Unique'
]);

const categories = ref([]);
const colors = ref([]);
const error = ref('');
const success = ref(false);
const loading = ref(false);

// Charger les données du produit
const loadProduct = async () => {
  try {
    const response = await axios.get(`/admin/products/${props.productId}`, {
      withCredentials: true
    });
    product.value = response.data;
    
    // Remplir le formulaire
    form.value = {
      name: product.value.name || '',
      reference: product.value.reference || '',
      description: product.value.description || '',
      price: product.value.price || null,
      stock: product.value.stock || null,
      size_ids: product.value.size || [],
      color_ids: product.value.colors?.map(c => c.id) || [],
      category_id: product.value.category_id || '',
      image: product.value.image || '',
    };
  } catch (err) {
    error.value = 'Erreur lors du chargement du produit.';
  }
};

// Charger les catégories et couleurs
const loadCategoriesAndColors = async () => {
  try {
    const [catRes, colorRes] = await Promise.all([
      axios.get('/api/category', { withCredentials: true }),
      axios.get('/api/color', { withCredentials: true })
    ]);
    categories.value = catRes.data;
    colors.value = colorRes.data;
  } catch (err) {
    error.value = 'Erreur lors du chargement des catégories ou couleurs.';
  }
};

const submitForm = async () => {
  error.value = '';
  success.value = false;
  loading.value = true;

  try {
    const response = await axios.put(`/admin/products/${props.productId}`, form.value, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });
    
    success.value = true;
    
    // Émettre l'événement pour mettre à jour la liste des produits
    emit('updated', response.data.product);
    
    // Fermer le formulaire après un délai
    setTimeout(() => {
      emit('close');
    }, 2000);
    
  } catch (e) {
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(' ');
    } else {
      error.value = 'Erreur lors de la modification du produit.';
    }
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([
    loadProduct(),
    loadCategoriesAndColors()
  ]);
});
</script>

