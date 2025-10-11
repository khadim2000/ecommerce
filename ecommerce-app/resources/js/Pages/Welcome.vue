<template>
    <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center">
        <div class="text-center">
            <img 
                src="/images/logo.png" 
                alt="ARME BI ONLINE SHOP" 
                class="h-40 w-auto mx-auto mb-8 drop-shadow-2xl logo-glow logo-pulse"
            />
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Bienvenue sur ARME BI</h1>
            <p class="text-lg text-gray-600 mb-8">Votre boutique en ligne de confiance</p>
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
            <p class="text-sm text-gray-500 mt-4">Redirection vers le dashboard...</p>
        </div>
    </div>
</template>

<script>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

export default {
    setup() {
        onMounted(() => {
            router.visit('/dashboard')
        })
    }
}
</script>
