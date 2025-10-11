<template>
  <!-- Syntaxe ultra-simple comme @paytech -->
  <button 
    @click="payNow"
    :class="buttonClass"
    :disabled="loading"
    type="button"
  >
    <span v-if="loading">⏳ Traitement...</span>
    <span v-else>{{ buttonText }}</span>
  </button>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Props minimales pour simuler @paytech
const props = defineProps({
  itemName: { type: String, default: 'Produit' },
  itemPrice: { type: [Number, String], required: true },
  buttonText: { type: String, default: 'Payer maintenant' },
  buttonClass: { type: String, default: 'btn btn-primary' },
  currency: { type: String, default: 'XOF' },
  paymentMethod: { type: String, default: 'orange_money' }
})

const loading = ref(false)

const payNow = async () => {
  loading.value = true
  
  try {
    // Créer commande
    const orderResponse = await axios.post('/api/orders', {
      address: 'Commande PayTech Simple',
      phone: '+221000000000',
      payment_method: props.paymentMethod,
      total: props.itemPrice
    })

    const orderId = orderResponse.data.id

    // Initier paiement PayTech
    const payTechResponse = await axios.post(`/orders/${orderId}/pay`, {
      currency: props.currency,
      payment_method: props.paymentMethod,
      description: `${props.itemName} - ${props.itemPrice} ${props.currency}`
    })

    // Rediriger vers PayTech
    if (payTechResponse.data?.payment_url) {
      window.location.href = payTechResponse.data.payment_url
    } else {
      window.location.href = payTechResponse.request.responseURL
    }

  } catch (error) {
    alert('Erreur: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.btn-success:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}
</style>













