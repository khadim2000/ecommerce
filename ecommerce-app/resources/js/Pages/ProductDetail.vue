<template>
  <div class="min-h-screen bg-gray-50">
    <Authenticated />
    
    <main class="container mx-auto py-8 px-4">
      <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6">
          <Link :href="route('home')" class="text-blue-600 hover:text-blue-800">← Retour aux produits</Link>
        </nav>

        <div v-if="loading" class="text-center py-12">
          <div class="text-4xl mb-4">⏳</div>
          <p>Chargement du produit...</p>
        </div>

        <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Image du produit -->
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <img 
              :src="product.image"
              :alt="product.name"
              class="w-full h-96 object-cover rounded-lg"
            />
          </div>

          <!-- Informations du produit -->
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>
            
            <div class="mb-6">
              <span class="text-4xl font-bold text-green-600">{{ formatPrice(product.price) }}</span>
              <span class="text-gray-500 ml-2">XOF</span>
            </div>

            <div class="mb-6">
              <p class="text-gray-700">{{ product.description }}</p>
            </div>

            <!-- Ajouter au panier -->
            <div class="mt-8 pt-6 border-t">
              <button 
                @click="addToCart"
                class="w-full bg-gray-600 text-white py-3 rounded-lg font-semibold hover:bg-gray-700 transition"
              >
                Ajouter au panier
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-4xl mb-4">❌</div>
          <h2 class="text-xl font-semibold text-gray-600 mb-2">Produit non trouvé</h2>
          <p class="text-gray-500 mb-6">Le produit que vous recherchez n'existe pas</p>
          <Link :href="route('home')" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Retour à l'accueil
          </Link>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import Authenticated from '../Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

// Props
const props = defineProps({
  productId: {
    type: [Number, String],
    required: true
  }
})

// État local
const product = ref(null)
const loading = ref(true)

// Méthodes
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF'
  }).format(price)
}

const fetchProduct = async () => {
  try {
    const response = await axios.get(`/api/products/${props.productId}`)
    product.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement du produit:', error)
    product.value = null
  } finally {
    loading.value = false
  }
}

const addToCart = async () => {
  try {
    await axios.post('/api/cart/add', {
      product_id: props.productId,
      quantity: 1
    })
    alert('Produit ajouté au panier !')
  } catch (error) {
    alert('Erreur lors de l\'ajout au panier')
  }
}

onMounted(() => {
  fetchProduct()
})
</script>
