<template>
  <div class="payment-methods">
    <label class="block text-sm font-medium text-gray-700 mb-3">
      Méthode de paiement *
    </label>
    
    <!-- Méthodes de paiement avec logos -->
    <div class="grid grid-cols-2 gap-3 mb-4">
      <!-- Orange Money -->
      <div 
        @click="selectPaymentMethod('orange_money')"
        :class="[
          'payment-option cursor-pointer p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md',
          selectedMethod === 'orange_money' 
            ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200' 
            : 'border-gray-200 hover:border-orange-300'
        ]"
      >
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center">
            <img src="/images/payment/orange-money.svg" alt="Orange Money" class="w-8 h-8" />
          </div>
          <div>
            <p class="font-medium text-sm">Orange Money</p>
            <p class="text-xs text-gray-500">Mobile Money</p>
          </div>
        </div>
      </div>


      <!-- Wave -->
      <div 
        @click="selectPaymentMethod('wave')"
        :class="[
          'payment-option cursor-pointer p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md',
          selectedMethod === 'wave' 
            ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' 
            : 'border-gray-200 hover:border-blue-300'
        ]"
      >
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center">
            <img src="/images/payment/wave.svg" alt="Wave" class="w-8 h-8" />
          </div>
          <div>
            <p class="font-medium text-sm">Wave</p>
            <p class="text-xs text-gray-500">Mobile Money</p>
          </div>
        </div>
      </div>

      <!-- Visa -->
      <div 
        @click="selectPaymentMethod('visa')"
        :class="[
          'payment-option cursor-pointer p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md',
          selectedMethod === 'visa' 
            ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-200' 
            : 'border-gray-200 hover:border-blue-400'
        ]"
      >
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center">
            <img src="/images/payment/visa.svg" alt="Visa" class="w-8 h-8" />
          </div>
          <div>
            <p class="font-medium text-sm">Visa</p>
            <p class="text-xs text-gray-500">Carte bancaire</p>
          </div>
        </div>
      </div>

      <!-- Mastercard -->
      <div 
        @click="selectPaymentMethod('mastercard')"
        :class="[
          'payment-option cursor-pointer p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md',
          selectedMethod === 'mastercard' 
            ? 'border-red-600 bg-red-50 ring-2 ring-red-200' 
            : 'border-gray-200 hover:border-red-400'
        ]"
      >
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center">
            <img src="/images/payment/mastercard.svg" alt="Mastercard" class="w-8 h-8" />
          </div>
          <div>
            <p class="font-medium text-sm">Mastercard</p>
            <p class="text-xs text-gray-500">Carte bancaire</p>
          </div>
        </div>
      </div>

      <!-- Paiement à la livraison -->
      <div 
        @click="selectPaymentMethod('cash_on_delivery')"
        :class="[
          'payment-option cursor-pointer p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md',
          selectedMethod === 'cash_on_delivery' 
            ? 'border-green-500 bg-green-50 ring-2 ring-green-200' 
            : 'border-gray-200 hover:border-green-300'
        ]"
      >
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
            <i class="fas fa-truck text-white text-sm"></i>
          </div>
          <div>
            <p class="font-medium text-sm">À la livraison</p>
            <p class="text-xs text-gray-500">Paiement cash</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Input caché pour le formulaire -->
    <input 
      type="hidden" 
      :value="selectedMethod" 
      @input="$emit('update:modelValue', $event.target.value)"
    />

    <!-- Message d'erreur -->
    <div v-if="error" class="text-red-500 text-sm mt-1">
      {{ error }}
    </div>

    <!-- Informations de sécurité -->
    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
      <div class="flex items-center space-x-2 text-sm text-gray-600">
        <i class="fas fa-shield-alt text-green-500"></i>
        <span>Paiement 100% sécurisé et crypté</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentMethods',
  props: {
    modelValue: {
      type: String,
      default: 'cash_on_delivery'
    },
    error: {
      type: String,
      default: null
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      selectedMethod: this.modelValue
    }
  },
  watch: {
    modelValue(newValue) {
      this.selectedMethod = newValue
    }
  },
  methods: {
    selectPaymentMethod(method) {
      this.selectedMethod = method
      this.$emit('update:modelValue', method)
    }
  }
}
</script>

<style scoped>
.payment-option {
  transition: all 0.2s ease-in-out;
}

.payment-option:hover {
  transform: translateY(-1px);
}

.payment-option:active {
  transform: translateY(0);
}
</style>
