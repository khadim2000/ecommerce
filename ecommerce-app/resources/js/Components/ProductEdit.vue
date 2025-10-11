<template>
  <div class="max-w-2xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 relative">
    <!-- Chargement -->
    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-70 flex justify-center items-center z-50">
      <div class="loader"></div>
    </div>

    <!-- Notification personnalisée -->
    <div v-if="notification.show" :class="['notification', notification.type]">
      {{ notification.message }}
    </div>

    <h2 class="text-2xl font-semibold mb-4">Modifier le produit</h2>

    <!-- Choix du mode -->
    <div class="mb-6">
      <label class="font-medium">Mode de modification :</label>
      <select v-model="mode" class="ml-2 border px-2 py-1 rounded">
        <option value="all">Toutes les colonnes</option>
        <option value="single">Un seul champ</option>
      </select>
    </div>

    <!-- Formulaire toutes colonnes -->
    <form v-if="mode === 'all'" @submit.prevent="updateProduct" class="space-y-4">
      <input v-model="form.name" placeholder="Nom" class="border p-2 rounded w-full" />
      <textarea v-model="form.description" placeholder="Description" class="border p-2 rounded w-full"></textarea>
      <input v-model.number="form.price" type="number" placeholder="Prix" class="border p-2 rounded w-full" />
      <input v-model.number="form.stock" type="number" placeholder="Stock" class="border p-2 rounded w-full" />
      <input v-model="form.reference" placeholder="Référence" class="border p-2 rounded w-full" />
      <input v-model="form.image_default" placeholder="Image URL" class="border p-2 rounded w-full" />
      <select v-model="form.category_id" class="border p-2 rounded w-full">
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <select v-model="form.color_ids" multiple class="border p-2 rounded w-full">
      <option v-for="color in colors" :key="color.id" :value="color.hex_code">
        {{ color.name }} 
      </option>

      </select>
      <div class="flex gap-4 mt-6">
        <button type="submit" class="btn-primary">Mettre à jour</button>
        <button type="button" @click="deleteDialog = true" class="btn-danger">Supprimer</button>
      </div>
    </form>

    <!-- Formulaire un champ -->
    <form v-else @submit.prevent="updateSingleField" class="space-y-4">
      <select v-model="selectedField" class="border p-2 rounded w-full">
        <option value="name">Nom</option>
        <option value="description">Description</option>
        <option value="price">Prix</option>
        <option value="stock">Stock</option>
        <option value="reference">Référence</option>
        <option value="image_default">Image</option>
      </select>
      <input v-model="form[selectedField]" class="border p-2 rounded w-full" />
      <div class="flex gap-4 mt-6">
        <button type="submit" class="btn-primary">Mettre à jour</button>
      </div>
    </form>

    <!-- Confirmation de suppression -->
    <div v-if="deleteDialog" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow-md w-80">
        <h3 class="text-lg font-bold mb-4">Confirmer la suppression</h3>
        <p class="mb-4">Voulez-vous vraiment supprimer ce produit ?</p>
        <div class="flex justify-end gap-4">
          <button @click="deleteDialog = false" class="btn-secondary">Annuler</button>
          <button @click="deleteProduct" class="btn-danger">Supprimer</button>
        </div>
      </div>
    </div>
  </div>
  
</template>

<script>
export default {
  props: {
    productId: Number,
  },
  data() {
    return {
      form: {
        name: '',
        description: '',
        price: 0,
        stock: 0,
        reference: '',
        image_default: '',
        category_id: '',
        color_ids: [],
      },
      mode: 'all',
      selectedField: '',
      categories: [],
      colors: [],
      deleteDialog: false,
      loading: false,
      notification: {
        show: false,
        message: '',
        type: 'success',
      },
    };
  },
  created() {
    this.fetchProduct();
    this.fetchCategories();
    this.fetchColors();
  },
  methods: {
    showNotification(message, type = 'success') {
      this.notification = { show: true, message, type };
      setTimeout(() => this.notification.show = false, 100);
    },
    async fetchProduct() {
      this.loading = true;
      try {
        const res = await fetch(`/api/products/${this.productId}`);
        const product = await res.json();
        this.form = {
          ...this.form,
          ...product,
         color_ids: product.colors?.map(c => c.hex_code) || [],

        };
        
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      const res = await fetch('/api/category');
      this.categories = await res.json();
    },
    async fetchColors() {
      const res = await fetch('/api/color');
      this.colors = await res.json();
    },
    async updateProduct() {
      this.loading = true;
      try {
        const res = await fetch(`/api/products/${this.productId}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
      
        });
        if (!res.ok)
        this.showNotification("Produit mis à jour avec succès !");
    
        window.location.href = '/dashboard';
   
      } catch {
        this.showNotification("Erreur lors de la mise à jour.", 'error');
      } finally {
        this.loading = false;
      }
    },
    async updateSingleField() {
      this.loading = true;
      try {
        const data = { [this.selectedField]: this.form[this.selectedField] };
        const res = await fetch(`/api/products/${this.productId}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
       
        });
        if (!res.ok) 
        this.showNotification("Champ mis à jour !");
        window.location.href = '/dashboard';
      } catch {
        this.showNotification("Erreur lors de la mise à jour du champ.", 'error');
      } finally {
        this.loading = false;
      }
    },
    async deleteProduct() {
      this.loading = true;
      try {
        await fetch(`/api/products/${this.productId}`, { method: 'DELETE' });
        this.showNotification("Produit supprimé !");
        this.deleteDialog = false;
        
 
      } catch {
        this.showNotification("Erreur lors de la suppression.", 'error');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.loader {
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.btn-primary {
  background-color: #2563eb;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
}
.btn-danger {
  background-color: #dc2626;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
}
.btn-secondary {
  background-color: #e5e7eb;
  color: black;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
}
.notification {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 14px 24px;
  border-radius: 8px;
  z-index: 1000;
  font-weight: bold;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.notification.success {
  background-color: #4ade80;
  color: #065f46;
}
.notification.error {
  background-color: #f87171;
  color: #7f1d1d;
}
</style>
