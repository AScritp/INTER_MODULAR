<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <Link :href="`/workspaces/${workspace.id}`" class="text-blue-500 hover:text-blue-700">← Volver</Link>
                <h1 class="text-3xl font-bold text-gray-800 mt-2">Calendario - {{ workspace.name }}</h1>
              </div>
              <Link
                :href="`/workspaces/${workspace.id}/events/create`"
                class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded"
              >
                + Nuevo Evento
              </Link>
            </div>

            <!-- Calendar Grid (Simple) -->
            <div class="bg-gray-50 p-6 rounded">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="event in workspace.events"
                  :key="event.id"
                  class="bg-white border-l-4 border-blue-500 p-4 rounded shadow-sm hover:shadow-md transition"
                >
                  <h3 class="font-semibold text-gray-800 text-lg">{{ event.title }}</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    <strong>Inicio:</strong> {{ formatDate(event.start_date) }}
                  </p>
                  <p class="text-sm text-gray-600">
                    <strong>Fin:</strong> {{ formatDate(event.end_date) }}
                  </p>
                  <p v-if="event.description" class="text-sm text-gray-700 mt-2">{{ event.description }}</p>

                  <div class="flex gap-2 mt-4">
                    <Link
                      :href="`/events/${event.id}/edit`"
                      class="bg-blue-500 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm"
                    >
                      Editar
                    </Link>
                    <button
                      @click="deleteEvent(event.id)"
                      class="bg-red-500 hover:bg-red-700 text-white py-1 px-3 rounded text-sm"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="workspace.events.length === 0" class="text-center py-12 text-gray-500">
                No hay eventos programados
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  workspace: Object,
  events: Array,
});

const formatDate = (date) => {
  return new Date(date).toLocaleString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const deleteEvent = (eventId) => {
  if (confirm("¿Estás seguro de que deseas eliminar este evento?")) {
    window.location.href = `/events/${eventId}`;
  }
};
</script>
