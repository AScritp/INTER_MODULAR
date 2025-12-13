<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <Link :href="`/workspaces/${workspace.id}`" class="text-blue-500 hover:text-blue-700">← Volver</Link>

            <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-6">Crear Evento</h1>

            <form @submit.prevent="store" class="space-y-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Título</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                  required
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Descripción</label>
                <textarea
                  v-model="form.description"
                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 h-24"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 font-semibold mb-2">Fecha y Hora Inicio</label>
                  <input
                    v-model="form.start_date"
                    type="datetime-local"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    required
                  />
                </div>
                <div>
                  <label class="block text-gray-700 font-semibold mb-2">Fecha y Hora Fin</label>
                  <input
                    v-model="form.end_date"
                    type="datetime-local"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    required
                  />
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
  form.post(`/workspaces/${props.workspace.id}/events`);
};
</script>
