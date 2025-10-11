<template>
  <div class="min-h-screen bg-gray-50">
    <Authenticated />
    
    <main class="container mx-auto py-8 px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Mes Commandes</h1>
        
        <div v-if="orders.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">📦</div>
          <h2 class="text-xl font-semibold text-gray-600 mb-2">Aucune commande</h2>
          <p class="text-gray-500 mb-6">Vous n'avez pas encore passé de commande</p>
          <Link :href="route('dashboard')" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
            Découvrir nos produits
          </Link>
        </div>

        <div v-else class="space-y-6">
          <div 
            v-for="order in orders" 
            :key="order.id"
            class="bg-white rounded-lg shadow-sm border p-6"
          >
            <div class="flex justify-between items-start mb-4">
              <div>
                <h2 class="text-lg font-semibold">Commande #{{ order.id }}</h2>
                <p class="text-sm text-gray-600">
                  Passée le {{ formatDate(order.created_at) }}
                </p>
              </div>
              <div class="text-right">
                <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ getStatusText(order.status) }}
                </span>
                <p class="text-lg font-semibold mt-1">{{ formatPrice(order.total) }}</p>
              </div>
            </div>
            
            <div class="border-t pt-4">
              <h3 class="font-medium mb-3">Articles commandés:</h3>
              <div class="space-y-2">
                <div 
                  v-for="item in order.order_items" 
                  :key="item.id"
                  class="flex items-center space-x-3"
                >
                  <img 
                    :src="item.product.image || '/images/placeholder.jpg'" 
                    :alt="item.product.name"
                    class="w-12 h-12 object-cover rounded"
                  >
                  <div class="flex-1">
                    <h4 class="font-medium">{{ item.product.name }}</h4>
                    <p class="text-sm text-gray-600">
                      Quantité: {{ item.quantity }} × {{ formatPrice(item.price) }}
                    </p>
                  </div>
                  <p class="font-semibold">{{ formatPrice(item.price * item.quantity) }}</p>
                </div>
              </div>
            </div>
            
            <div class="border-t pt-4 mt-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <h4 class="font-medium text-gray-700">Adresse de livraison:</h4>
                  <p class="text-gray-600">{{ order.address }}</p>
                </div>
                <div>
                  <h4 class="font-medium text-gray-700">Téléphone:</h4>
                  <p class="text-gray-600">{{ order.phone }}</p>
                </div>
              </div>
            </div>
          </div>
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

const orders = ref([])
const loading = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('sn-SN', {
    style: 'currency',
    currency: 'XOF'
  }).format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusText = (status) => {
  const statusMap = {
    'pending': 'En attente',
    'processing': 'En cours de traitement',
    'shipped': 'Expédiée',
    'delivered': 'Livrée',
    'cancelled': 'Annulée'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'processing': 'bg-blue-100 text-blue-800',
    'shipped': 'bg-purple-100 text-purple-800',
    'delivered': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const fetchOrders = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/orders')
    orders.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des commandes:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrders()
})
</script>

