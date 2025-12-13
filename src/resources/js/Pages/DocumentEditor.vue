<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { debounce } from 'lodash';

const props = defineProps({
    documentId: Number
});

const document = ref(null);
const title = ref('');
const content = ref('');
const loading = ref(true);
const saving = ref(false);
const lastSaved = ref(null);

// Cargar documento
const loadDocument = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/documents/${props.documentId}`);
        document.value = response.data;
        title.value = response.data.title;
        content.value = response.data.content || '';
        lastSaved.value = new Date(response.data.updated_at);
    } catch (error) {
        console.error('Error loading document:', error);
    } finally {
        loading.value = false;
    }
};

// Autoguardado con debounce (espera 1 segundo después del último cambio)
const autosave = debounce(async () => {
    if (!document.value) return;
    
    saving.value = true;
    try {
        const response = await axios.post(`/api/documents/${document.value.id}/autosave`, {
            content: content.value
        });
        
        lastSaved.value = new Date(response.data.updated_at);
        console.log('Autoguardado exitoso');
    } catch (error) {
        console.error('Error en autoguardado:', error);
    } finally {
        saving.value = false;
    }
}, 1000);

// Guardar título (solo cuando pierde el foco)
const saveTitle = async () => {
    if (!document.value || title.value === document.value.title) return;
    
    try {
        await axios.put(`/api/workspaces/${document.value.workspace_id}/documents/${document.value.id}`, {
            title: title.value
        });
        document.value.title = title.value;
        console.log('Título guardado');
    } catch (error) {
        console.error('Error guardando título:', error);
    }
};

// Volver al dashboard
const goBack = () => {
    router.visit('/dashboard');
};

// Watch para autoguardado del contenido
watch(content, () => {
    if (!loading.value) {
        autosave();
    }
});

onMounted(() => {
    loadDocument();
});
</script>

<template>
    <Head :title="document?.title || 'Editor'" />

    <AuthenticatedLayout>
        <div v-if="loading" class="flex justify-center items-center h-screen">
            <p class="text-gray-500">Cargando documento...</p>
        </div>

        <div v-else class="min-h-screen bg-gray-50">
            <!-- Header fijo -->
            <div class="bg-white border-b sticky top-0 z-10 shadow-sm">
                <div class="max-w-4xl mx-auto px-6 py-4 flex justify-between items-center">
                    <button
                        @click="goBack"
                        class="text-gray-600 hover:text-gray-900 flex items-center gap-2"
                    >
                        ← Volver
                    </button>

                    <div class="flex items-center gap-4">
                        <!-- Indicador de guardado -->
                        <div class="text-sm">
                            <span v-if="saving" class="text-blue-500">
                                Guardando...
                            </span>
                            <span v-else-if="lastSaved" class="text-gray-500">
                                Guardado {{ lastSaved.toLocaleTimeString() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editor -->
            <div class="max-w-4xl mx-auto px-6 py-8">
                <div class="bg-white rounded-lg shadow-sm p-8 min-h-[600px]">
                    <!-- Título editable -->
                    <input
                        v-model="title"
                        type="text"
                        placeholder="Título del documento"
                        class="w-full text-4xl font-bold border-none focus:outline-none focus:ring-0 mb-6 px-0"
                        @blur="saveTitle"
                    />

                    <!-- Área de contenido -->
                    <textarea
                        v-model="content"
                        placeholder="Empieza a escribir..."
                        class="w-full min-h-[500px] text-lg border-none focus:outline-none focus:ring-0 resize-none px-0"
                    />

                    <!-- Información adicional -->
                    <div class="mt-8 pt-4 border-t text-sm text-gray-500">
                        <p>Creado: {{ new Date(document.created_at).toLocaleString() }}</p>
                        <p>Última modificación: {{ new Date(document.updated_at).toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Tips de uso -->
                <div class="mt-6 bg-blue-50 rounded-lg p-4 text-sm text-gray-700">
                    <p class="font-semibold mb-2">💡 Tips:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>El contenido se guarda automáticamente mientras escribes</li>
                        <li>El título se guarda cuando haces click fuera del campo</li>
                        <li>Puedes cerrar la pestaña sin perder cambios</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
textarea {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
}
</style>