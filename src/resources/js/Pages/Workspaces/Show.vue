<template>
  <Head :title="workspace.name" />
  
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="mb-6">
              <Link href="/workspaces" class="text-blue-500 hover:text-blue-700">← Volver</Link>
              <h1 class="text-3xl font-bold text-gray-800 mt-2">{{ workspace.name }}</h1>
              <p class="text-gray-600 mt-2">{{ workspace.description }}</p>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
              <div class="flex gap-4">
                <button
                  @click="activeTab = 'documents'"
                  :class="activeTab === 'documents' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                  class="py-2 px-4 font-semibold"
                >
                  Documentos
                </button>
                <button
                  @click="activeTab = 'calendar'"
                  :class="activeTab === 'calendar' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                  class="py-2 px-4 font-semibold"
                >
                  Calendario
                </button>
                <button
                  @click="activeTab = 'sharing'"
                  :class="activeTab === 'sharing' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                  class="py-2 px-4 font-semibold"
                >
                  Compartir
                </button>
              </div>
            </div>

            <!-- Documentos Tab -->
            <div v-show="activeTab === 'documents'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Documentos</h2>
                <Link
                  :href="`/workspaces/${workspace.id}/documents/create`"
                  class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded"
                >
                  + Nuevo Documento
                </Link>
              </div>

              <div v-if="workspace.documents.length > 0" class="space-y-2">
                <div
                  v-for="doc in workspace.documents"
                  :key="doc.id"
                  class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-200 hover:bg-gray-100"
                >
                  <div>
                    <h3 class="font-semibold text-gray-800">{{ doc.title }}</h3>
                    <p class="text-sm text-gray-500">Actualizado: {{ new Date(doc.updated_at).toLocaleDateString() }}</p>
                  </div>
                  <div class="flex gap-2">
                    <Link
                      :href="`/documents/${doc.id}/edit`"
                      class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Editar
                    </Link>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay documentos aún
              </div>
            </div>

            <!-- Calendario Tab -->
            <div v-show="activeTab === 'calendar'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Calendario de Eventos</h2>
                <Link
                  :href="`/workspaces/${workspace.id}/events/create`"
                  class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded"
                >
                  + Nuevo Evento
                </Link>
              </div>

              <div v-if="workspace.events.length > 0" class="space-y-2">
                <div
                  v-for="event in workspace.events"
                  :key="event.id"
                  class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-200 hover:bg-gray-100"
                >
                  <div>
                    <h3 class="font-semibold text-gray-800">{{ event.title }}</h3>
                    <p class="text-sm text-gray-600">
                      {{ new Date(event.start_date).toLocaleString() }} - {{ new Date(event.end_date).toLocaleString() }}
                    </p>
                    <p v-if="event.description" class="text-sm text-gray-700 mt-2">{{ event.description }}</p>
                  </div>
                  <div class="flex gap-2">
                    <Link
                      :href="`/events/${event.id}/edit`"
                      class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Editar
                    </Link>
                    <button
                      @click="deleteEvent(event.id)"
                      class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay eventos aún
              </div>
            </div>

            <!-- Compartir Tab -->
            <div v-show="activeTab === 'sharing'">
              <h2 class="text-xl font-semibold mb-4">Compartir Workspace</h2>
              <div v-if="isOwner" class="bg-blue-50 p-6 rounded border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-4">Invitar usuario</h3>
                <form @submit.prevent="addUser" class="space-y-4">
                  <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email del usuario</label>
                    <input
                      v-model="shareForm.email"
                      type="email"
                      class="w-full px-4 py-2 border border-gray-300 rounded"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-gray-700 font-semibold mb-2">Rol</label>
                    <select v-model="shareForm.role" class="w-full px-4 py-2 border border-gray-300 rounded">
                      <option value="editor">Editor</option>
                      <option value="viewer">Lector</option>
                    </select>
                  </div>
                  <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">
                    Invitar
                  </button>
                </form>

                <h3 class="font-semibold text-blue-900 mt-6 mb-4">Usuarios con acceso</h3>
                <div class="space-y-2">
                  <div class="flex justify-between items-center bg-white p-3 rounded">
                    <span class="text-gray-700">{{ workspace.user.name }} (Propietario)</span>
                  </div>
                  <div
                    v-for="user in workspace.users"
                    :key="user.id"
                    class="flex justify-between items-center bg-white p-3 rounded"
                  >
                    <span class="text-gray-700">{{ user.name }}</span>
                    <button
                      @click="removeUser(user.id)"
                      class="bg-red-500 hover:bg-red-700 text-white py-1 px-3 rounded text-sm"
                    >
                      Remover
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="bg-gray-50 p-6 rounded border border-gray-200 text-center text-gray-600">
                Solo el propietario del workspace puede compartirlo
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm, Head } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
  workspace: Object,
});

const activeTab = ref("documents");
const shareForm = useForm({
  email: "",
  role: "editor",
});

const isOwner = ref(false);

const addUser = () => {
  shareForm.post(`/workspaces/${props.workspace.id}/users`, {
    preserveScroll: true,
    onSuccess: () => {
      shareForm.reset();
    },
  });
};

const removeUser = (userId) => {
  if (confirm("¿Estás seguro?")) {
    window.location.href = `/workspaces/${props.workspace.id}/users/${userId}`;
  }
};

const deleteEvent = (eventId) => {
  if (confirm("¿Estás seguro de eliminar este evento?")) {
    const form = useForm({});
    form.delete(`/events/${eventId}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Recargar la página para actualizar la lista
        window.location.reload();
      },
    });
  }
};
</script>
