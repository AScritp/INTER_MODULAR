<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-800">Mis Workspaces</h1>
              <Link href="/workspaces/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Nuevo Workspace
              </Link>
            </div>

            <!-- Tus Workspaces -->
            <div v-if="workspaces.length > 0" class="mb-8">
              <h2 class="text-xl font-semibold text-gray-700 mb-4">Tus Workspaces</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="workspace in workspaces"
                  :key="workspace.id"
                  class="bg-white border border-gray-300 rounded-lg p-6 hover:shadow-lg transition"
                >
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ workspace.name }}</h3>
                  <p class="text-gray-600 mb-4 text-sm">{{ workspace.description }}</p>
                  <div class="flex gap-2">
                    <Link
                      :href="`/workspaces/${workspace.id}`"
                      class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Ver
                    </Link>
                    <Link
                      :href="`/workspaces/${workspace.id}/edit`"
                      class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Editar
                    </Link>
                    <button
                      @click="deleteWorkspace(workspace.id)"
                      class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded text-sm"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Workspaces Compartidos -->
            <div v-if="sharedWorkspaces.length > 0">
              <h2 class="text-xl font-semibold text-gray-700 mb-4">Workspaces Compartidos</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="workspace in sharedWorkspaces"
                  :key="`shared-${workspace.id}`"
                  class="bg-blue-50 border border-blue-300 rounded-lg p-6 hover:shadow-lg transition"
                >
                  <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ workspace.name }}</h3>
                  <p class="text-blue-700 mb-4 text-sm">{{ workspace.description }}</p>
                  <Link
                    :href="`/workspaces/${workspace.id}`"
                    class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm"
                  >
                    Abrir
                  </Link>
                </div>
              </div>
            </div>

            <div v-if="workspaces.length === 0 && sharedWorkspaces.length === 0" class="text-center py-12">
              <p class="text-gray-500 text-lg">No tienes workspaces aún</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  workspaces: Array,
  sharedWorkspaces: Array,
});

const deleteWorkspace = (id) => {
  if (confirm("¿Estás seguro de que deseas eliminar este workspace?")) {
    router.delete(`/workspaces/${id}`);
  }
};
</script>
