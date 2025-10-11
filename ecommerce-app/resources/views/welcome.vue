<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { onMounted } from 'vue'

// Formulaire de connexion
const form = useForm({
  email: '',
  password: '',
  remember: false,
})

// Récupération du token CSRF à l'ouverture du composant
onMounted(async () => {
  try {
    const response = await axios.get('/csrf-token') // Route Laravel définie plus bas
    axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token
    console.log('CSRF token chargé')
  } catch (error) {
    console.error('Erreur lors du chargement du token CSRF', error)
  }
})

// Soumission du formulaire
const submit = async () => {
  form.post(route('login'), {
    onSuccess: () => {
      console.log('Connexion réussie')
    },
    onError: () => {
      console.log('Erreur lors de la connexion')
    },
    onFinish: () => {
      form.reset('password')
    }
  })
}
</script>

<template>
  <Head title="Connexion" />

  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Connexion</h2>

      <form @submit.prevent="submit" class="space-y-5">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autofocus
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Mot de passe -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <!-- Se souvenir -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-900">Se souvenir de moi</label>
        </div>

        <!-- Bouton Connexion -->
        <div>
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition duration-200"
          >
            Se connecter
          </button>
        </div>
      </form>

      <!-- Lien vers inscription -->
      <p class="mt-6 text-center text-sm text-gray-600">
        Pas encore inscrit ?
        <a href="/register" class="text-indigo-600 hover:underline font-medium">Créer un compte</a>
      </p>
    </div>
  </div>
</template>
