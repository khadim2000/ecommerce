<template>
  <header class="sticky top-0 z-50">
    <div class="header-military shadow-xl backdrop-blur-md">
      <div class="container mx-auto px-4">
        <div class="header-content flex flex-wrap items-center justify-between py-4 gap-4">
          <!-- Logo ARME BI -->
          <div class="flex items-center space-x-3 flex-shrink-0 animate-slide-in">
            <div class="logo-container flex items-center justify-center">
              <!--
              <div class="relative">
                <img 
                  src="/images/logo.png" 
                  alt="ARME BI ONLINE SHOP" 
                  class="h-24 w-auto object-contain drop-shadow-2xl logo-glow logo-pulse"
                />
              </div>
              -->
            </div>
                     <div class="m-0 p-0">
         <h1 class="text-3xl font-black tracking-wide drop-shadow-2xl text-shadow-lg text-left">
  <span class="text-green-500">SUN</span>
  <span class="text-yellow-500">UY</span>
  <span class="text-red-500">EUF</span>
</h1>

            <p class="text-base text-green-50 font-bold drop-shadow-lg text-shadow-md">NIOKO MOOM</p>
          </div>
        </div>

          <!-- Barre de recherche améliorée -->
          <div class="flex-grow min-w-[250px] max-w-2xl mx-4">
            <form @submit.prevent="handleSearch" class="relative group">
              <div class="relative">
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Rechercher des produits..."
                  class="input-field pl-12 pr-4 py-3 bg-white/90 backdrop-blur-sm border-0 rounded-full shadow-lg focus:bg-white focus:shadow-xl transition-all duration-300 group-hover:bg-white"
                />
                <button
                  type="submit"
                  class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-green-600 transition-colors duration-200"
                >
                  
                </button>
              </div>
            </form>
          </div>

          <!-- Navigation pour utilisateurs non connectés -->
          <div class="flex flex-wrap gap-3 flex-shrink-0 justify-end items-center">
            <a
              href="/login"
              class="btn bg-white/10 hover:bg-white/20 text-white border border-white/30 hover:border-white/50 backdrop-blur-sm animate-pulse-hover"
            >
              🔐 Se connecter
            </a>
            <a
              href="/register"
              class="btn bg-white text-green-700 hover:bg-gray-50 shadow-md hover:shadow-lg animate-pulse-hover"
            >
              ✨ S'inscrire
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const searchQuery = ref('');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    // Rediriger vers la page d'accueil avec le paramètre de recherche
    router.get('/', { search: searchQuery.value.trim() }, {
      preserveState: true,
      preserveScroll: false
    })
  }
}
</script>