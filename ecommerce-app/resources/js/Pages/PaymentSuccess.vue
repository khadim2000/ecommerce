<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
      <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
        <!-- Icône de succès -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
          <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>

        <!-- Titre -->
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Paiement réussi !
        </h1>

        <!-- Message -->
        <p class="text-gray-600 mb-8">
          Votre paiement a été traité avec succès. Votre commande est en cours de préparation.
        </p>

        <!-- Détails du paiement -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails du paiement</h3>
          
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Méthode de paiement :</span>
              <span class="font-medium">{{ payment_method }}</span>
            </div>
            
            <div class="flex justify-between">
              <span class="text-gray-600">Montant :</span>
              <span class="font-medium">{{ formatPrice(amount) }}</span>
            </div>
            
            <div v-if="transaction_id" class="flex justify-between">
              <span class="text-gray-600">ID de transaction :</span>
              <span class="font-mono text-sm">{{ transaction_id }}</span>
            </div>
            
            <div v-if="order?.id" class="flex justify-between">
              <span class="text-gray-600">Numéro de commande :</span>
              <span class="font-medium">#{{ order.id }}</span>
            </div>
          </div>
        </div>

        <!-- Détails de la commande si disponibles -->
        <div v-if="order?.orderItems?.length" class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Articles commandés</h3>
          
          <div class="space-y-3">
            <div v-for="item in order.orderItems" :key="item.id" class="flex items-center space-x-4">
              <img 
                :src="item.product?.image || '/images/placeholder.jpg'" 
                :alt="item.product?.name"
                class="w-12 h-12 object-cover rounded"
              >
              <div class="flex-1">
                <p class="font-medium">{{ item.product?.name }}</p>
                <p class="text-sm text-gray-600">Quantité: {{ item.quantity }}</p>
              </div>
              <p class="font-medium">{{ formatPrice(item.product?.price * item.quantity) }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="space-y-4">
          <Link 
            href="/orders" 
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition"
          >
            Voir mes commandes
          </Link>
          
          <div class="text-center">
            <Link 
              href="/" 
              class="text-green-600 hover:text-green-500 font-medium"
            >
              Retour à l'accueil
            </Link>
          </div>
        </div>

        <!-- Message de confirmation email -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-blue-800">
            <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Un email de confirmation vous a été envoyé.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  order: Object,
  payment_method: String,
  amount: Number,
  transaction_id: String,
  phone: String
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}
</script>