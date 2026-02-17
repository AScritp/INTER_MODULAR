<template>
  <Head :title="document.title" />

  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-900">
                {{ document.title }}
              </h1>
              <Link
                v-if="canEdit"
                :href="`/documents/${document.id}/edit`"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Editar
              </Link>
            </div>

            <div class="prose max-w-none" v-html="document.content"></div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  document: Object,
  canEdit: Boolean,
});
</script>
