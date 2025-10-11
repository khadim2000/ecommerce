<template>
  <div class="paytech-directive">
    <!-- Syntaxe Blade @paytech([...]) -->
    <PayTechButton
      :item-name="itemName"
      :item-price="itemPrice"
      :currency="currency"
      :payment-method="paymentMethod"
      :button-text="buttonText"
      :button-class="buttonClass"
      :mode="mode"
      :order-id="orderId"
      :description="description"
      :customer-name="customerName"
      :customer-email="customerEmail"
      :customer-phone="customerPhone"
      @success="handleSuccess"
      @error="handleError"
      @loading="handleLoading"
    />
  </div>
</template>

<script setup>
// Props qui simulent exactement la syntaxe Blade @paytech
const props = defineProps({
  // Syntaxe: @paytech(['item_name' => 'Mon Produit', ...])
  itemName: {
    type: String,
    default: 'Mon Produit'
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
  
  // Options supplémentaires
  mode: {
    type: String,
    default: 'simple'
  },
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
  }
})

// Émissions
const emit = defineEmits(['success', 'error', 'loading'])

// Handlers
const handleSuccess = (data) => {
  emit('success', data)
  console.log('PayTech Success:', data)
}

const handleError = (error) => {
  emit('error', error)
  console.error('PayTech Error:', error)
}

const handleLoading = (isLoading) => {
  emit('loading', isLoading)
}
</script>

<style scoped>
.paytech-directive {
  display: inline-block;
}
</style>













