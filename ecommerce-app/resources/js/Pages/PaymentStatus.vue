<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <!-- Icône de statut -->
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full" 
             :class="statusClass">
          <i :class="statusIcon" class="text-3xl"></i>
        </div>
        
        <!-- Titre -->
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          {{ statusTitle }}
        </h2>
        
        <!-- Message -->
        <p class="mt-2 text-sm text-gray-600">
          {{ statusMessage }}
        </p>
        
        <!-- Détails du paiement -->
        <div v-if="paymentDetails" class="mt-6 bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Détails du paiement</h3>
          <dl class="space-y-2">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Méthode de paiement:</dt>
              <dd class="text-sm font-medium text-gray-900">{{ paymentMethod }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Montant:</dt>
              <dd class="text-sm font-medium text-gray-900">{{ formatPrice(amount) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Numéro de téléphone:</dt>
              <dd class="text-sm font-medium text-gray-900 flex items-center">
                <i class="fas fa-phone mr-2 text-green-500"></i>
                {{ phoneNumber || 'Non fourni' }}
              </dd>
            </div>
            <div v-if="transactionId" class="flex justify-between">
              <dt class="text-sm text-gray-500">ID de transaction:</dt>
              <dd class="text-sm font-medium text-gray-900 font-mono">{{ transactionId }}</dd>
            </div>
          </dl>
        </div>
        
        <!-- Actions -->
        <div class="mt-8 space-y-4">
          <button
            @click="goToOrders"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
          >
            <i class="fas fa-list mr-2"></i>
            Voir mes commandes
          </button>
          
          <button
            @click="goToHome"
            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
          >
            <i class="fas fa-home mr-2"></i>
            Retour à l'accueil
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const status = ref('pending');
const paymentMethod = ref('');
const amount = ref(0);
const phoneNumber = ref('');
const transactionId = ref('');

const statusClass = computed(() => {
  switch (status.value) {
    case 'success':
      return 'bg-green-100 text-green-600';
    case 'error':
      return 'bg-red-100 text-red-600';
    case 'pending':
    default:
      return 'bg-yellow-100 text-yellow-600';
  }
});

const statusIcon = computed(() => {
  switch (status.value) {
    case 'success':
      return 'fas fa-check-circle';
    case 'error':
      return 'fas fa-times-circle';
    case 'pending':
    default:
      return 'fas fa-clock';
  }
});

const statusTitle = computed(() => {
  switch (status.value) {
    case 'success':
      return 'Paiement réussi !';
    case 'error':
      return 'Paiement échoué';
    case 'pending':
    default:
      return 'Paiement en cours...';
  }
});

const statusMessage = computed(() => {
  switch (status.value) {
    case 'success':
      return 'Votre paiement a été traité avec succès. Vous recevrez un email de confirmation.';
    case 'error':
      return 'Une erreur est survenue lors du traitement de votre paiement. Veuillez réessayer.';
    case 'pending':
    default:
      return 'Votre paiement est en cours de traitement. Veuillez patienter...';
  }
});

const paymentDetails = computed(() => {
  return paymentMethod.value || amount.value || phoneNumber.value;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF'
  }).format(price);
};

const goToOrders = () => {
  router.visit('/orders');
};

const goToHome = () => {
  router.visit('/');
};

onMounted(() => {
  // Simuler le traitement du paiement
  setTimeout(() => {
    status.value = 'success';
  }, 3000);
  
  // Récupérer les paramètres de l'URL depuis les props Inertia
  const urlParams = new URLSearchParams(window.location.search);
  paymentMethod.value = urlParams.get('method') || 'Orange Money';
  amount.value = parseFloat(urlParams.get('amount')) || 0;
  phoneNumber.value = urlParams.get('phone') || '';
  transactionId.value = urlParams.get('pay_token') || urlParams.get('transaction_id') || '';
});
</script>
