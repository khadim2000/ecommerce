<template>
  <div class="paytech-component">
    <!-- Mode simple avec props -->
    <button 
      v-if="mode === 'simple'"
      @click="initiatePayment"
      :class="buttonClass"
      :disabled="loading"
      type="button"
    >
      <span v-if="loading">Traitement...</span>
      <span v-else>{{ buttonText }}</span>
    </button>

    <!-- Mode formulaire -->
    <form v-else-if="mode === 'form'" @submit.prevent="initiatePayment">
      <input type="hidden" name="amount" :value="itemPrice">
      <input type="hidden" name="item_name" :value="itemName">
      <input type="hidden" name="currency" :value="currency">
      <input type="hidden" name="payment_method" :value="paymentMethod">
      
      <button 
        type="submit" 
        :class="buttonClass"
        :disabled="loading"
      >
        <span v-if="loading">Traitement...</span>
        <span v-else>{{ buttonText }}</span>
      </button>
    </form>

    <!-- Mode personnalisé avec slot -->
    <div v-else-if="mode === 'custom'" @click="initiatePayment">
      <slot>
        <button :class="buttonClass" :disabled="loading">
          <span v-if="loading">Traitement...</span>
          <span v-else>{{ buttonText }}</span>
        </button>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Props pour simuler la syntaxe Blade @paytech
const props = defineProps({
  // Paramètres de base (comme dans @paytech)
  itemName: {
    type: String,
    default: 'Produit'
  },
  itemPrice: {
    type: [Number, String],
    required: true
  },
  currency: {
    type: String,
    default: 'XOF'
  },
  paymentMethod: {
    type: String,
    default: 'orange_money'
  },
  buttonText: {
    type: String,
    default: 'Payer maintenant'
  },
  buttonClass: {
    type: String,
    default: 'btn btn-primary'
  },
  
  // Mode d'affichage
  mode: {
    type: String,
    default: 'simple', // simple, form, custom
    validator: (value) => ['simple', 'form', 'custom'].includes(value)
  },
  
  // Options PayTech
  orderId: {
    type: [Number, String],
    default: null
  },
  description: {
    type: String,
    default: null
  },
  customerName: {
    type: String,
    default: null
  },
  customerEmail: {
    type: String,
    default: null
  },
  customerPhone: {
    type: String,
    default: null
  },
  
  // Callbacks
  onSuccess: {
    type: Function,
    default: null
  },
  onError: {
    type: Function,
    default: null
  },
  onLoading: {
    type: Function,
    default: null
  }
})

// Émissions
const emit = defineEmits(['success', 'error', 'loading'])

// État local
const loading = ref(false)

// Méthodes
const initiatePayment = async () => {
  loading.value = true
  
  try {
    // Émettre l'événement de chargement
    emit('loading', true)
    if (props.onLoading) {
      props.onLoading(true)
    }

    // Préparer les données de paiement
    const paymentData = {
      currency: props.currency,
      payment_method: props.paymentMethod,
      description: props.description || `${props.itemName} - ${props.itemPrice} ${props.currency}`,
      amount: props.itemPrice,
      item_name: props.itemName,
      customer_name: props.customerName,
      customer_email: props.customerEmail,
      customer_phone: props.customerPhone
    }

    let orderId = props.orderId

    // Si pas d'orderId fourni, créer une commande
    if (!orderId) {
      const orderResponse = await axios.post('/api/orders', {
        address: 'Commande via PayTech Button',
        phone: props.customerPhone || '+221000000000',
        payment_method: props.paymentMethod,
        total: props.itemPrice
      })
      orderId = orderResponse.data.id
    }

    // Initier le paiement PayTech
    const payTechResponse = await axios.post(`/orders/${orderId}/pay`, paymentData)

    if (payTechResponse.status === 200 || payTechResponse.status === 302) {
      // Redirection vers PayTech
      if (payTechResponse.data?.payment_url) {
        window.location.href = payTechResponse.data.payment_url
      } else {
        // Si c'est une redirection directe, suivre la redirection
        window.location.href = payTechResponse.request.responseURL
      }
      
      // Émettre le succès
      emit('success', {
        orderId,
        paymentUrl: payTechResponse.data?.payment_url,
        transactionId: payTechResponse.data?.transaction_id
      })
      
      if (props.onSuccess) {
        props.onSuccess({
          orderId,
          paymentUrl: payTechResponse.data?.payment_url,
          transactionId: payTechResponse.data?.transaction_id
        })
      }
    } else {
      throw new Error('Erreur lors de l\'initiation du paiement PayTech')
    }

  } catch (error) {
    console.error('Erreur PayTech:', error)
    
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors du paiement'
    
    // Émettre l'erreur
    emit('error', errorMessage)
    
    if (props.onError) {
      props.onError(errorMessage)
    }
    
    // Afficher l'erreur à l'utilisateur
    alert('Erreur: ' + errorMessage)
    
  } finally {
    loading.value = false
    emit('loading', false)
    if (props.onLoading) {
      props.onLoading(false)
    }
  }
}
</script>

<style scoped>
.paytech-component {
  display: inline-block;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.3s ease;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background-color: #1e7e34;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Classes PayTech spécifiques */
.paytech-orange {
  background-color: #ff6600;
  color: white;
}

.paytech-wave {
  background-color: #0066cc;
  color: white;
}

.paytech-visa {
  background-color: #1a1f71;
  color: white;
}

.paytech-mastercard {
  background-color: #eb001b;
  color: white;
}
</style>













