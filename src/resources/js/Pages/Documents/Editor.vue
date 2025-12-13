<template>
  <div class="min-h-screen bg-gray-100">
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
                v-model="form.title"
                @change="autoSave"
                type="text"
                class="text-3xl font-bold text-gray-800 bg-transparent border-none w-full mt-2 focus:outline-none"
              />
            </div>
            <div class="text-right">
              <div v-if="saveStatus" class="text-sm mb-2">
                <span :class="saveStatus === 'saving' ? 'text-yellow-600' : 'text-green-600'">
                  {{ saveStatus === 'saving' ? 'Guardando...' : 'Guardado' }}
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
          <div v-if="form.errors.content" class="text-red-600 text-sm mt-2">
            {{ form.errors.content }}
          </div>

          <div class="mt-6 flex gap-4">
            <button
              @click="saveDocument"
              class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-6 rounded font-semibold"
            >
              Guardar Cambios
            </button>
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
  </div>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
  document: Object,
  workspace: Object,
});

const form = useForm({
  title: props.document.title,
  content: props.document.content || "",
});

const quill = ref(null);
const saveStatus = ref(null);
let saveTimeout;

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
    form.content = quill.value.root.innerHTML;
    autoSave();
  });
});

const autoSave = () => {
  clearTimeout(saveTimeout);
  saveStatus.value = "saving";

  saveTimeout = setTimeout(() => {
    axios
      .patch(`/api/documents/${props.document.id}/auto-save`, {
        title: form.title,
        content: form.content,
      })
      .then(() => {
        saveStatus.value = "saved";
        setTimeout(() => {
          saveStatus.value = null;
        }, 2000);
      })
      .catch((error) => {
        saveStatus.value = null;
        console.error("Error saving:", error);
      });
  }, 2000);
};

const saveDocument = () => {
  form.patch(`/documents/${props.document.id}`, {
    preserveScroll: true,
  });
};

const deleteDocument = () => {
  if (confirm("¿Estás seguro de que deseas eliminar este documento?")) {
    form.delete(`/documents/${props.document.id}`, {
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
