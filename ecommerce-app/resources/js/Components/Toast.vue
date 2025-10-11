<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center animate-fade-in"
  >
    <div 
      :class="[
        'max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
        type === 'success' ? 'bg-green-50 border border-green-200' : '',
        type === 'error' ? 'bg-red-50 border border-red-200' : '',
        type === 'warning' ? 'bg-yellow-50 border border-yellow-200' : '',
        type === 'info' ? 'bg-blue-50 border border-blue-200' : ''
      ]"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <i 
              :class="[
                'text-2xl',
                type === 'success' ? 'fas fa-check-circle text-green-500' : '',
                type === 'error' ? 'fas fa-exclamation-circle text-red-500' : '',
                type === 'warning' ? 'fas fa-exclamation-triangle text-yellow-500' : '',
                type === 'info' ? 'fas fa-info-circle text-blue-500' : ''
              ]"
            ></i>
          </div>
          <div class="ml-3 w-0 flex-1">
            <p 
              :class="[
                'text-base font-medium',
                type === 'success' ? 'text-green-800' : '',
                type === 'error' ? 'text-red-800' : '',
                type === 'warning' ? 'text-yellow-800' : '',
                type === 'info' ? 'text-blue-800' : ''
              ]"
            >
              {{ message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button 
              @click="$emit('close')"
              class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <span class="sr-only">Fermer</span>
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  }
});

defineEmits(['close']);
</script>

<style scoped>
@keyframes fade-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
