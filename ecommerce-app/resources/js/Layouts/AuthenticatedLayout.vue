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

        <!-- Navigation moderne -->
        <div class="flex flex-wrap gap-3 flex-shrink-0 justify-end items-center">
          <!-- Si l'utilisateur est connecté -->
          <template v-if="user">
            <!-- Navigation pour tous les utilisateurs connectés -->
            <Link 
              :href="route('cart')" 
              class="btn bg-white/10 hover:bg-white/20 text-white border border-white/30 hover:border-white/50 backdrop-blur-sm animate-pulse-hover"
            >
              <span class="mr-2">🛒</span>
              <span class="hidden sm:inline">Panier</span>
            </Link>
            
            <Link 
              :href="route('orders')" 
              class="btn bg-white/10 hover:bg-white/20 text-white border border-white/30 hover:border-white/50 backdrop-blur-sm animate-pulse-hover"
            >
              <i class="fas fa-box mr-2"></i>
              <span class="hidden sm:inline">Commandes</span>
            </Link>
            
            <!-- Menu utilisateur avec dropdown -->
            <div class="relative group">
              <button class="btn bg-white/10 hover:bg-white/20 text-white border border-white/30 hover:border-white/50 backdrop-blur-sm">
                <i class="fas fa-user mr-2"></i>
                <span class="hidden sm:inline">{{ user.name || 'Utilisateur' }}</span>
                <i class="fas fa-chevron-down ml-1 transition-transform group-hover:rotate-180"></i>
              </button>
              
              <!-- Dropdown menu -->
              <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <div class="py-2">
                  <Link :href="route('profile')" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-user-circle mr-2"></i> Mon profil
                  </Link>
                  
                  <!-- Boutons admin -->
                  <template v-if="user.role === 'admin'">
                    <hr class="my-2 border-gray-200">
                    <button
                      class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
                      @click="$emit('openAddProduct')"
                    >
                      <i class="fas fa-plus mr-2"></i> Ajouter produit
                    </button>
                    <button
                      class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
                      @click="$emit('openAddCategory')"
                    >
                      <i class="fas fa-tags mr-2"></i> Ajouter catégorie
                    </button>
                  </template>
                  
                  <hr class="my-2 border-gray-200">
                  <button
                    @click="logout"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <i class="fas fa-sign-out-alt mr-2"></i> Se déconnecter
                  </button>
                </div>
              </div>
            </div>
          </template>

          <!-- Sinon, boutons connexion/inscription -->
          <template v-else>
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
          </template>
        </div>
      </div>
    </div>
    </div>
  </header>
  
  

  <!-- Gestionnaire de token CSRF -->
  <CsrfTokenManager />
  
  <!-- Panier flottant -->
  <FloatingCart />
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import FloatingCart from '../Components/FloatingCart.vue';
import ApplicationLogo from '../Components/ApplicationLogo.vue';
import ErrorBoundary from '../Components/ErrorBoundary.vue';
import CsrfTokenManager from '../Components/CsrfTokenManager.vue';

const user = usePage().props.user;
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

// Utilisateur connecté
const logout = () => {
   router.post(route('logout'), {}, {
     onSuccess: () => {
       // Rediriger vers la page d'accueil après déconnexion
       router.visit('/');
     }
   });
};
</script>
