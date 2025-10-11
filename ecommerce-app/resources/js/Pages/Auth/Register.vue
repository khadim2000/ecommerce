<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
form.post('/register')
, {
    onFinish: () => form.reset('password', 'password_confirmation'),
  }
}
</script>

<template>
  <Head title="Inscription" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 via-white to-green-100">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
      <!-- Logo ARME BI -->
      <div class="text-center mb-6">
        <img 
          src="/images/logo.png" 
          alt="ARME BI ONLINE SHOP" 
          class="h-28 w-auto mx-auto mb-4 drop-shadow-2xl logo-glow logo-pulse"
        />
        <h2 class="text-2xl font-semibold text-gray-800">Créer un compte <span class="text-green-600">ARME BI</span></h2>
        <p class="text-sm text-gray-600">Rejoignez notre communauté</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Nom -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
          <input
            id="name"
            type="text"
            v-model="form.name"
            required
            autofocus
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <!-- Mot de passe -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            required
            autocomplete="new-password"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>

        <!-- Confirmation mot de passe -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
          <input
            id="password_confirmation"
            type="password"
            v-model="form.password_confirmation"
            required
            autocomplete="new-password"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
        </div>

        <!-- Bouton + lien -->
        <div class="flex items-center justify-between">
          <Link
            :href="route('login')"
            class="text-sm text-gray-600 hover:text-gray-900 underline"
          >
            Déjà inscrit ?
          </Link>

          <button
            type="submit"
            class="ml-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
            :disabled="form.processing"
          >
            S'inscrire
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
