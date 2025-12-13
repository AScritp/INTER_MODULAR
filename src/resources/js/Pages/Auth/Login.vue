<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

// Configurar axios para usar las mismas credenciales que Inertia
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const workspaces = ref([]);
const documents = ref([]);
const selectedWorkspace = ref(null);
const loading = ref(false);
const newWorkspaceName = ref('');
const newDocumentTitle = ref('');
const showWorkspaceForm = ref(false);
const showDocumentForm = ref(false);

// Cargar workspaces del usuario
const loadWorkspaces = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/dashboard/workspaces');
        workspaces.value = response.data;
        
        if (workspaces.value.length > 0 && !selectedWorkspace.value) {
            selectWorkspace(workspaces.value[0]);
        }
    } catch (error) {
        console.error('Error loading workspaces:', error);
        if (error.response?.status === 401) {
            alert('Sesión expirada. Por favor, recarga la página.');
        } else {
            alert('Error al cargar workspaces: ' + (error.response?.data?.message || error.message));
        }
    } finally {
        loading.value = false;
    }
};

// Seleccionar workspace y cargar sus documentos
const selectWorkspace = async (workspace) => {
    selectedWorkspace.value = workspace;
    loading.value = true;
    
    try {
        const response = await axios.get(`/dashboard/workspaces/${workspace.id}/documents`);
        documents.value = response.data;
    } catch (error) {
        console.error('Error loading documents:', error);
        alert('Error al cargar documentos: ' + (error.response?.data?.message || error.message));
    } finally {
        loading.value = false;
    }
};

// Crear nuevo workspace
const createWorkspace = async () => {
    if (!newWorkspaceName.value.trim()) {
        alert('Ingresa un nombre para el workspace');
        return;
    }
    
    try {
        const response = await axios.post('/dashboard/workspaces', {
            name: newWorkspaceName.value
        });
        
        workspaces.value.unshift(response.data);
        newWorkspaceName.value = '';
        showWorkspaceForm.value = false;
        selectWorkspace(response.data);
    } catch (error) {
        console.error('Error creating workspace:', error);
        alert('Error al crear workspace: ' + (error.response?.data?.message || error.message));
    }
};

// Crear nuevo documento
const createDocument = async () => {
    if (!newDocumentTitle.value.trim()) {
        alert('Ingresa un título para el documento');
        return;
    }
    
    if (!selectedWorkspace.value) {
        alert('Selecciona un workspace primero');
        return;
    }
    
    try {
        const response = await axios.post(
            `/dashboard/workspaces/${selectedWorkspace.value.id}/documents`,
            { title: newDocumentTitle.value, content: '' }
        );
        
        documents.value.unshift(response.data);
        newDocumentTitle.value = '';
        showDocumentForm.value = false;
    } catch (error) {
        console.error('Error creating document:', error);
        alert('Error al crear documento: ' + (error.response?.data?.message || error.message));
    }
};

// Eliminar documento
const deleteDocument = async (documentId) => {
    if (!confirm('¿Estás seguro de eliminar este documento?')) return;
    
    try {
        await axios.delete(
            `/dashboard/workspaces/${selectedWorkspace.value.id}/documents/${documentId}`
        );
        documents.value = documents.value.filter(doc => doc.id !== documentId);
    } catch (error) {
        console.error('Error deleting document:', error);
        alert('Error al eliminar documento: ' + (error.response?.data?.message || error.message));
    }
};

// Eliminar workspace
const deleteWorkspace = async (workspaceId) => {
    if (!confirm('¿Estás seguro de eliminar este workspace? Se eliminarán todos sus documentos.')) return;
    
    try {
        await axios.delete(`/dashboard/workspaces/${workspaceId}`);
        workspaces.value = workspaces.value.filter(ws => ws.id !== workspaceId);
        
        if (selectedWorkspace.value?.id === workspaceId) {
            selectedWorkspace.value = null;
            documents.value = [];
            if (workspaces.value.length > 0) {
                selectWorkspace(workspaces.value[0]);
            }
        }
    } catch (error) {
        console.error('Error deleting workspace:', error);
        alert('Error al eliminar workspace: ' + (error.response?.data?.message || error.message));
    }
};

onMounted(() => {
    loadWorkspaces();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard - Notion MVP
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-6">
                    
                    <!-- Sidebar: Workspaces -->
                    <div class="col-span-12 md:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Workspaces</h3>
                                <button
                                    @click="showWorkspaceForm = !showWorkspaceForm"
                                    class="text-blue-500 hover:text-blue-700 font-bold text-xl"
                                    title="Crear workspace"
                                >
                                    +
                                </button>
                            </div>
                            
                            <!-- Formulario crear workspace -->
                            <div v-if="showWorkspaceForm" class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <input
                                    v-model="newWorkspaceName"
                                    type="text"
                                    placeholder="Nombre del workspace..."
                                    class="w-full px-3 py-2 border rounded-lg text-sm mb-2"
                                    @keyup.enter="createWorkspace"
                                />
                                <div class="flex gap-2">
                                    <button
                                        @click="createWorkspace"
                                        class="flex-1 bg-blue-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-600"
                                    >
                                        Crear
                                    </button>
                                    <button
                                        @click="showWorkspaceForm = false; newWorkspaceName = ''"
                                        class="flex-1 bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-400"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de workspaces -->
                            <div v-if="workspaces.length === 0 && !loading" class="text-center text-gray-500 py-8 text-sm">
                                <p>No hay workspaces.</p>
                                <p class="mt-2">¡Crea el primero!</p>
                            </div>

                            <div v-else class="space-y-2">
                                <div
                                    v-for="workspace in workspaces"
                                    :key="workspace.id"
                                    class="group relative"
                                >
                                    <div
                                        @click="selectWorkspace(workspace)"
                                        class="p-3 rounded-lg cursor-pointer transition"
                                        :class="selectedWorkspace?.id === workspace.id 
                                            ? 'bg-blue-50 border-blue-300 border-2' 
                                            : 'bg-gray-50 hover:bg-gray-100 border-2 border-transparent'"
                                    >
                                        <p class="font-medium text-sm">{{ workspace.name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(workspace.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                    <button
                                        @click.stop="deleteWorkspace(workspace.id)"
                                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition text-red-500 hover:text-red-700 text-xs"
                                        title="Eliminar workspace"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main: Documentos -->
                    <div class="col-span-12 md:col-span-9 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div v-if="!selectedWorkspace" class="text-center text-gray-500 py-12">
                                <p class="text-lg mb-2">👈 Selecciona o crea un workspace para comenzar</p>
                            </div>

                            <div v-else>
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold">
                                        📄 Documentos en "{{ selectedWorkspace.name }}"
                                    </h3>
                                    <button
                                        @click="showDocumentForm = !showDocumentForm"
                                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 font-semibold"
                                    >
                                        + Nuevo Documento
                                    </button>
                                </div>

                                <!-- Formulario crear documento -->
                                <div v-if="showDocumentForm" class="mb-6 p-4 bg-gray-50 rounded-lg">
                                    <input
                                        v-model="newDocumentTitle"
                                        type="text"
                                        placeholder="Título del documento..."
                                        class="w-full px-4 py-2 border rounded-lg mb-2"
                                        @keyup.enter="createDocument"
                                    />
                                    <div class="flex gap-2">
                                        <button
                                            @click="createDocument"
                                            class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600"
                                        >
                                            Crear Documento
                                        </button>
                                        <button
                                            @click="showDocumentForm = false; newDocumentTitle = ''"
                                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </div>

                                <!-- Lista de documentos -->
                                <div v-if="loading" class="text-center py-8">
                                    <p class="text-gray-500">Cargando...</p>
                                </div>

                                <div v-else-if="documents.length === 0" class="text-center py-12 text-gray-500">
                                    <p class="text-lg">📝 No hay documentos en este workspace</p>
                                    <p class="mt-2">¡Crea el primero usando el botón verde!</p>
                                </div>

                                <div v-else class="space-y-3">
                                    <div
                                        v-for="document in documents"
                                        :key="document.id"
                                        class="p-4 border-2 rounded-lg hover:shadow-md transition flex justify-between items-start hover:border-blue-300"
                                    >
                                        <div class="flex-1">
                                            <Link
                                                :href="`/documents/${document.id}`"
                                                class="text-lg font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                            >
                                                📄 {{ document.title }}
                                            </Link>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Actualizado: {{ new Date(document.updated_at).toLocaleString() }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                                {{ document.content || 'Sin contenido' }}
                                            </p>
                                        </div>
                                        <button
                                            @click="deleteDocument(document.id)"
                                            class="ml-4 text-red-500 hover:text-red-700 text-sm font-semibold"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>