<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
      <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
        <!-- Icône d'échec -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
          <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>

        <!-- Titre -->
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Paiement échoué
        </h1>

        <!-- Message -->
        <p class="text-gray-600 mb-8">
          {{ message || 'Votre paiement n\'a pas pu être traité. Veuillez réessayer ou choisir une autre méthode de paiement.' }}
        </p>

        <!-- Détails du paiement si disponibles -->
        <div v-if="amount || transaction_id" class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails de la transaction</h3>
          
          <div class="space-y-3">
            <div v-if="payment_method" class="flex justify-between">
              <span class="text-gray-600">Méthode de paiement :</span>
              <span class="font-medium">{{ payment_method }}</span>
            </div>
            
            <div v-if="amount" class="flex justify-between">
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

        <!-- Raisons possibles -->
        <div class="bg-yellow-50 rounded-lg p-6 mb-8 text-left">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Causes possibles :</h3>
          
          <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-start">
              <svg class="h-4 w-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Solde insuffisant sur votre compte mobile money
            </li>
            <li class="flex items-start">
              <svg class="h-4 w-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Code PIN incorrect ou transaction expirée
            </li>
            <li class="flex items-start">
              <svg class="h-4 w-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Problème de connexion avec le service de paiement
            </li>
            <li class="flex items-start">
              <svg class="h-4 w-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Limite de transaction dépassée
            </li>
          </ul>
        </div>

        <!-- Actions -->
        <div class="space-y-4">
          <Link 
            href="/checkout" 
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition"
          >
            Réessayer le paiement
          </Link>
          
          <div class="text-center space-x-4">
            <Link 
              href="/cart" 
              class="text-red-600 hover:text-red-500 font-medium"
            >
              Modifier le panier
            </Link>
            <span class="text-gray-300">|</span>
            <Link 
              href="/" 
              class="text-red-600 hover:text-red-500 font-medium"
            >
              Retour à l'accueil
            </Link>
          </div>
        </div>

        <!-- Message d'aide -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-blue-800">
            <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Besoin d'aide ? Contactez notre service client au +221 33 XXX XX XX
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
  phone: String,
  reason: String,
  message: String
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}
</script>