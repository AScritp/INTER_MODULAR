<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <Link :href="`/workspaces/${workspace.id}`" class="text-blue-500 hover:text-blue-700">← Volver</Link>

            <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-6">Crear Nuevo Documento</h1>

            <form @submit.prevent="store" class="space-y-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Título del Documento</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                  placeholder="Ej: Mi Primer Documento"
                  required
                />
                <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                  {{ form.errors.title }}
                </div>
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Contenido (opcional)</label>
                <textarea
                  v-model="form.content"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 h-32"
                  placeholder="Escribe el contenido inicial..."
                ></textarea>
              </div>

              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white py-2 px-6 rounded font-semibold"
                >
                  {{ form.processing ? "Creando..." : "Crear Documento" }}
                </button>
                <Link
                  :href="`/workspaces/${workspace.id}`"
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

const props = defineProps({
  workspace: Object,
});

const form = useForm({
  title: "",
  content: "",
});

const store = () => {
  form.post(`/workspaces/${props.workspace.id}/documents`);
};
</script>
