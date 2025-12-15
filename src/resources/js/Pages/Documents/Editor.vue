<template>
  <Head :title="document.title" />

  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="flex justify-between items-center">
            <div>
              <Link :href="`/workspaces/${workspace.id}`" class="text-blue-500 hover:text-blue-700 text-sm">
                ← Volver al Workspace
              </Link>
              <input
                v-model="title"
                @input="triggerAutoSave"
                type="text"
                class="text-3xl font-bold text-gray-800 bg-transparent border-none w-full mt-2 focus:outline-none"
              />
            </div>
            <div class="text-right">
              <div class="text-sm mb-2 h-6">
                <span v-if="saveStatus === 'saving'" class="text-yellow-600">
                  Guardando...
                </span>
                <span v-else-if="saveStatus === 'saved'" class="text-green-600">
                  ✓ Guardado
                </span>
                <span v-else-if="saveStatus === 'error'" class="text-red-600">
                  Error al guardar
                </span>
              </div>
              <button
                @click="deleteDocument"
                class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded text-sm"
              >
                Eliminar Documento
              </button>
            </div>
          </div>
        </div>

        <!-- Editor -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div id="editor" class="h-96 bg-white border border-gray-300 rounded"></div>

          <div class="mt-6 flex gap-4">
            <button
              @click="previewDocument"
              class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-6 rounded"
            >
              Vista Previa
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
  document: Object,
  workspace: Object,
});

const title = ref(props.document.title);
const content = ref(props.document.content || "");
const quill = ref(null);
const saveStatus = ref(null);
let saveTimeout = null;
let statusTimeout = null;

onMounted(() => {
  quill.value = new Quill("#editor", {
    theme: "snow",
    placeholder: "Escribe tu contenido aquí...",
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ["bold", "italic", "underline", "strike"],
        [{ list: "ordered" }, { list: "bullet" }],
        ["blockquote", "code-block"],
        [{ color: [] }, { background: [] }],
        ["link", "image"],
        ["clean"],
      ],
    },
  });

  if (props.document.content) {
    quill.value.root.innerHTML = props.document.content;
  }

  quill.value.on("text-change", () => {
    content.value = quill.value.root.innerHTML;
    triggerAutoSave();
  });
});

onUnmounted(() => {
  if (saveTimeout) clearTimeout(saveTimeout);
  if (statusTimeout) clearTimeout(statusTimeout);
});

const triggerAutoSave = () => {
  if (saveTimeout) clearTimeout(saveTimeout);
  if (statusTimeout) clearTimeout(statusTimeout);
  
  saveStatus.value = "saving";

  saveTimeout = setTimeout(() => {
    performSave();
  }, 1500);
};

const performSave = async () => {
  try {
    await axios.patch(`/documents/${props.document.id}/auto-save`, {
      title: title.value,
      content: content.value,
    });
    
    saveStatus.value = "saved";
    statusTimeout = setTimeout(() => {
      saveStatus.value = null;
    }, 3000);
  } catch (error) {
    console.error("Error saving:", error);
    saveStatus.value = "error";
    statusTimeout = setTimeout(() => {
      saveStatus.value = null;
    }, 5000);
  }
};

const deleteDocument = () => {
  if (confirm("¿Estás seguro de que deseas eliminar este documento?")) {
    router.delete(`/documents/${props.document.id}`, {
      onSuccess: () => {
        window.location.href = `/workspaces/${props.workspace.id}`;
      },
    });
  }
};

const previewDocument = () => {
  window.open(`/documents/${props.document.id}`, "_blank");
};
</script>
