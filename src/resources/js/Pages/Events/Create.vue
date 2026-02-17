<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <Link :href="`/workspaces/${workspace.id}`" class="text-blue-500 hover:text-blue-700">← Volver</Link>

            <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-6">Crear Evento</h1>

            <!-- Global error banner -->
            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
              <p class="font-semibold">Por favor corrige los siguientes errores:</p>
              <ul class="list-disc list-inside mt-2">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>

            <form @submit.prevent="store" class="space-y-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Título</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="w-full px-4 py-2 border rounded focus:outline-none"
                  :class="form.errors.title ? 'border-red-500' : 'border-gray-300 focus:border-blue-500'"
                  required
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Descripción</label>
                <textarea
                  v-model="form.description"
                  class="w-full px-4 py-2 border rounded focus:outline-none h-24"
                  :class="form.errors.description ? 'border-red-500' : 'border-gray-300 focus:border-blue-500'"
                ></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 font-semibold mb-2">Fecha y Hora Inicio</label>
                  <input
                    v-model="form.start_date"
                    type="datetime-local"
                    class="w-full px-4 py-2 border rounded focus:outline-none"
                    :class="form.errors.start_date ? 'border-red-500' : 'border-gray-300 focus:border-blue-500'"
                    required
                  />
                  <p v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</p>
                </div>
                <div>
                  <label class="block text-gray-700 font-semibold mb-2">Fecha y Hora Fin</label>
                  <input
                    v-model="form.end_date"
                    type="datetime-local"
                    class="w-full px-4 py-2 border rounded focus:outline-none"
                    :class="form.errors.end_date ? 'border-red-500' : 'border-gray-300 focus:border-blue-500'"
                    required
                  />
                  <p v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</p>
                </div>
              </div>

              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white py-2 px-6 rounded font-semibold"
                >
                  {{ form.processing ? "Creando..." : "Crear Evento" }}
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
  description: "",
  start_date: "",
  end_date: "",
});

const store = () => {
  form.transform((data) => ({
    ...data,
    start_date: data.start_date.replace('T', ' '),
    end_date: data.end_date.replace('T', ' '),
  })).post(`/workspaces/${props.workspace.id}/events`);
};
</script>

