<template>
  <div class="min-h-screen bg-gray-50">
    <Authenticated />

    <main class="container mx-auto py-8 px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Finaliser la commande</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Formulaire de livraison -->
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-6">Informations de livraison</h2>

            <form @submit.prevent="submitOrder" class="space-y-6">
         

            

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse de livraison *</label>
                <textarea v-model="form.address" rows="3" placeholder="Votre adresse complète..."
                  class="w-full px-3 py-2 border rounded-md" required></textarea>
                <div v-if="errors.address" class="text-red-600 text-sm mt-1">{{ errors.address[0] }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                <div class="flex">
                  <span class="inline-flex items-center px-3 rounded-l-md border bg-gray-50 text-gray-500 text-sm">+221</span>
                  <input v-model="form.phone" type="tel" maxlength="9"
                    placeholder="123456789" class="flex-1 px-3 py-2 border rounded-r-md" required />
                </div>
                <div v-if="errors.phone" class="text-red-600 text-sm mt-1">{{ errors.phone[0] }}</div>
              </div>

              <button type="submit"
                :disabled="loading || cartItems.length === 0"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700">
                <span v-if="loading">⏳ Traitement...</span>
                <span v-else>Finaliser la commande</span>
              </button>

              <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-lg p-4 mt-2">
                <div class="text-red-800 text-sm">{{ errors.general[0] }}</div>
              </div>
            </form>
          </div>

          <!-- Résumé de la commande -->
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-6">Résumé de la commande</h2>

            <div v-if="cartItems.length === 0" class="text-center py-8">
              <div class="text-4xl mb-4">🛒</div>
              <p class="text-gray-500">Votre panier est vide</p>
              <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">Continuer mes achats</Link>
            </div>

            <div v-else>
              <div class="space-y-4 mb-6">
                <div v-for="item in cartItems" :key="item.product_id" class="flex items-center space-x-4">
                  <img :src="item.product?.image || '/images/placeholder.jpg'" :alt="item.product?.name"
                    class="w-16 h-16 object-cover rounded-lg" />
                  <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ item.product?.name }}</h3>
                    <div class="text-sm text-gray-500">Quantité: {{ item.quantity }} × {{ formatPrice(item.product?.price) }}</div>
                  </div>
                  <div class="font-semibold text-gray-900">{{ formatPrice(item.quantity * item.product?.price) }}</div>
                </div>
              </div>

              <div class="border-t pt-4">
                <div class="flex justify-between items-center text-lg font-semibold">
                  <span>Total</span>
                  <span class="text-green-600">{{ formatPrice(cartTotal) }}</span>
                </div>
              </div>
            </div>
             <div v-if="cartItems.length ==! 0" class="text-center py-8">
              <div class="text-4xl mb-4">🛒</div>
              <p class="text-gray-500"> </p>
              <Link :href="route('cart')" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">Retour au panier</Link>
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

const loading = ref(false)
const errors = ref({})
const cartItems = ref([])

const form = ref({

  address: '',
  phone: ''
})

const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => total + (item.quantity * (item.product?.price || 0)), 0)
})

const formatPrice = (price) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(price || 0)

const validatePhoneNumber = (phone) => /^(77|76|70|75|78)[0-9]{7}$/.test(phone)

const fetchCartItems = async () => {
  try {
    const response = await axios.get('/api/cart')
    cartItems.value = response.data.items || response.data
  } catch (e) {
    console.error(e)
    errors.value = { general: ['Erreur lors du chargement du panier'] }
  }
}

const submitOrder = async () => {
  if (cartItems.value.length === 0) {
    errors.value = { general: ['Votre panier est vide'] }
    return
  }


 
  if (!form.value.address.trim()) { errors.value = { address: ['L\'adresse est obligatoire'] }; return }
  if (!form.value.phone || !validatePhoneNumber(form.value.phone)) { errors.value = { phone: ['Numéro de téléphone invalide'] }; return }

  loading.value = true
  errors.value = {}

  try {
    // Création de la commande côté Laravel
    const orderRes = await axios.post('/checkout/order', {
    
      address: form.value.address.trim(),
      phone: '+221' + form.value.phone
    })

    const orderId = orderRes.data.order.id

    // Appel API PayDunya pour récupérer le lien de paiement
    const paymentRes = await axios.post(`/orders/${orderId}/pay`)
    const paymentUrl = paymentRes.data.payment_url
      console.log('Réponse complète de l’API:', paymentRes);
  

    if (paymentUrl) {
      window.location.href = paymentUrl
    } else {
      errors.value = { general: ['Impossible de générer le lien de paiement'] }
    }

  } catch (e) {
    console.error(e)
    errors.value = { general: ['Erreur lors du traitement de la commande'] }
  } finally {
    loading.value = false
  }
}
onMounted(() => {
  fetchCartItems()
})
</script>
