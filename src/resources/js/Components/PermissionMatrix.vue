<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="p-4 border rounded">
      <h3 class="font-semibold mb-2">Workspace</h3>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.read_workspace" /> Lectura</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.update_workspace" /> Modificar</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.delete_workspace" /> Eliminar</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.share_workspace" /> Compartir</label>
    </div>
    <div class="p-4 border rounded">
      <h3 class="font-semibold mb-2">Documentos</h3>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.read_existing_docs" /> Leer existentes</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.read_own_docs" /> Leer propios</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.create_doc" /> Crear</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.update_any_doc" /> Editar cualquier</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.update_own_doc" /> Editar propios</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.delete_any_doc" /> Eliminar cualquier</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.delete_own_doc" /> Eliminar propios</label>
    </div>
    <div class="p-4 border rounded">
      <h3 class="font-semibold mb-2">Eventos</h3>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.read_existing_events" /> Leer existentes</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.read_own_events" /> Leer propios</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.create_event" /> Crear</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.update_any_event" /> Editar cualquier</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.update_own_event" /> Editar propios</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.delete_any_event" /> Eliminar cualquier</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="local.delete_own_event" /> Eliminar propios</label>
    </div>
  </div>
  <div class="mt-4 flex gap-4">
    <label class="flex items-center gap-2"><input type="checkbox" v-model="inherit_existing_documents" /> Heredar documentos existentes</label>
    <label class="flex items-center gap-2"><input type="checkbox" v-model="inherit_existing_events" /> Heredar eventos existentes</label>
    <label class="flex items-center gap-2"><input type="checkbox" v-model="apply_to_future_only" /> Solo futuros</label>
  </div>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';

const props = defineProps({
  modelValue: Object,
});
const emit = defineEmits(['update:modelValue', 'update:inherit_existing_documents', 'update:inherit_existing_events', 'update:apply_to_future_only']);

const local = reactive({ ...(props.modelValue || {}) });
const inherit_existing_documents = ref(false);
const inherit_existing_events = ref(false);
const apply_to_future_only = ref(false);

watch(local, () => emit('update:modelValue', local), { deep: true });
watch(inherit_existing_documents, (v) => emit('update:inherit_existing_documents', v));
watch(inherit_existing_events, (v) => emit('update:inherit_existing_events', v));
watch(apply_to_future_only, (v) => emit('update:apply_to_future_only', v));
</script>
