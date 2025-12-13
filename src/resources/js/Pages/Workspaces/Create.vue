<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <Link href="/workspaces" class="text-blue-500 hover:text-blue-700">← Volver a Workspaces</Link>

            <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-6">Crear Workspace</h1>

            <form @submit.prevent="store" class="space-y-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Nombre del Workspace</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                  placeholder="Ej: Mi Proyecto Personal"
                  required
                />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                  {{ form.errors.name }}
                </div>
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Descripción</label>
                <textarea
                  v-model="form.description"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 h-24"
                  placeholder="Describe el propósito de este workspace..."
                ></textarea>
              </div>

              <div>
                <label class="flex items-center">
                  <input v-model="form.is_shared" type="checkbox" class="mr-2" />
                  <span class="text-gray-700">Permitir compartir este workspace</span>
                </label>
              </div>

              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white py-2 px-6 rounded font-semibold"
                >
                  {{ form.processing ? "Creando..." : "Crear Workspace" }}
                </button>
                <Link
                  href="/workspaces"
                  class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-6 rounded"
                >
                  Cancelar
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";

const form = useForm({
  name: "",
  description: "",
  is_shared: false,
});

const store = () => {
  form.post("/workspaces");
};
</script>
