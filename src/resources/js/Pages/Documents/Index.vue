<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <Link href="/workspaces" class="text-blue-500 hover:text-blue-700">← Volver</Link>

            <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-6">{{ workspace.name }} - Documentos</h1>

            <div class="mb-6">
              <Link
                :href="`/workspaces/${workspace.id}/documents/create`"
                class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded"
              >
                + Nuevo Documento
              </Link>
            </div>

            <div v-if="workspace.documents.length > 0" class="space-y-3">
              <div
                v-for="doc in workspace.documents"
                :key="doc.id"
                class="flex items-center justify-between bg-white border border-gray-200 p-4 rounded hover:shadow-md transition"
              >
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-800 text-lg">{{ doc.title }}</h3>
                  <p class="text-sm text-gray-500">
                    Creado: {{ new Date(doc.created_at).toLocaleDateString() }}
                  </p>
                </div>
                <Link
                  :href="`/documents/${doc.id}/edit`"
                  class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded"
                >
                  Abrir Editor
                </Link>
              </div>
            </div>

            <div v-else class="text-center py-12 text-gray-500">
              <p class="text-lg">No hay documentos en este workspace</p>
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
  workspace: Object,
});
</script>
