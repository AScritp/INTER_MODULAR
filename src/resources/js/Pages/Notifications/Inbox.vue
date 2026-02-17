<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
          <h1 class="text-2xl font-bold mb-4">Notificaciones</h1>
          <div v-if="notifications.length === 0" class="text-gray-500">Sin notificaciones</div>
          <div v-else class="space-y-3">
            <div v-for="n in notifications" :key="n.id" class="border rounded p-4 flex justify-between items-center">
              <div>
                <div class="font-semibold">
                  {{ renderTitle(n) }}
                </div>
                <div class="text-sm text-gray-600">
                  {{ renderDetails(n) }}
                </div>
              </div>
              <div class="flex gap-2">
                <template v-if="n.data.type === 'share_invitation' && n.data.status === 'pending'">
                  <Link :href="`/invitations/${n.data.invitation_id}/accept`" method="post" as="button" class="bg-green-500 text-white px-3 py-1 rounded">Aceptar</Link>
                  <Link :href="`/invitations/${n.data.invitation_id}/reject`" method="post" as="button" class="bg-red-500 text-white px-3 py-1 rounded">Rechazar</Link>
                </template>
                <Link :href="`/notifications/${n.id}/mark-read`" method="post" as="button" class="bg-gray-200 px-3 py-1 rounded">Marcar leída</Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({ notifications: Array });

const renderTitle = (n) => {
  switch (n.data.type) {
    case 'share_invitation': return 'Invitación para compartir recurso';
    case 'share_accepted': return 'Invitación aceptada';
    case 'share_rejected': return 'Invitación rechazada';
    default: return 'Notificación';
  }
};

const renderDetails = (n) => {
  if (n.data.type === 'share_invitation') {
    const scope = n.data.resource_type ? `${n.data.resource_type} #${n.data.resource_id}` : `workspace #${n.data.workspace_id}`;
    return `Te han invitado a: ${scope}`;
  }
  return '';
};
</script>
