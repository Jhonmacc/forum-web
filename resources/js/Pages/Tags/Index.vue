<template>
    <div class="p-6 bg-gray-100 min-h-screen">
      <h1 class="text-2xl font-semibold mb-6">Gerenciar Tags</h1>

      <!-- Formulário de criação de tags -->
      <form @submit.prevent="createTag" class="mb-6">
        <div class="flex items-center gap-4">
          <input
            v-model="newTag.name"
            type="text"
            placeholder="Nova Tag"
            class="flex-1 p-2 border rounded focus:outline-none focus:ring focus:ring-indigo-300"
            required
          />
          <input
            v-model="newTag.color"
            type="color"
            class="w-10 h-10 border rounded"
            title="Escolha uma cor para a tag"
            />

            <input
            v-model="newTag.icon"
            type="text"
            placeholder="Ícone (ex: pi pi-github)"
            class="flex-1 p-2 border rounded focus:outline-none focus:ring focus:ring-indigo-300"
            />

          <button
            type="submit"
            class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600"
          >
            Adicionar
          </button>
        </div>
        <span v-if="errors.name" class="text-red-500 text-sm mt-2">{{ errors.name[0] }}</span>
      </form>

      <!-- Lista de tags existentes -->
      <div v-if="tags.length" class="bg-white p-4 shadow rounded">
        <ul>
          <li
            v-for="tag in tags"
            :key="tag.id"
            class="flex justify-between items-center py-2 border-b last:border-b-0"
          >
            <span>{{ tag.name }}</span>
            <span :style="{ backgroundColor: tag.color, color: 'white', padding: '5px', borderRadius: '5px' }"> {{ tag.code }} </span>
            <span>{{ tag.icon }}</span>

            <button
              @click="deleteTag(tag.id)"
              class="text-red-500 hover:text-red-700"
            >
              Excluir
            </button>
          </li>
        </ul>
      </div>
      <p v-else class="text-gray-500">Nenhuma tag encontrada.</p>
    </div>
  </template>

  <script>
  import { ref } from 'vue';
  import { Inertia } from '@inertiajs/inertia';

  export default {
    props: {
      tags: Array,  // Recebe as tags como prop
    },
    setup() {
      const newTag = ref({ name: '', color: '#000000' });  // Inicializa a cor com preto
      const errors = ref({});

      const generateCode = (name) => {
        // Gerar código baseado no nome da tag
        return name.toUpperCase().replace(/\s+/g, '_');
      };

      const createTag = () => {
        newTag.value.code = generateCode(newTag.value.name);  // Gerar o código
        Inertia.post('/tags', newTag.value, {
          onSuccess: () => {
            newTag.value.name = '';
            newTag.value.code = ''; // Limpar o código
            newTag.value.color = '#000000';
            newTag.value.icon = '';
            errors.value = {};
          },
          onError: (err) => {
            errors.value = err;
          },
        });
      };

      const deleteTag = (id) => {
        if (confirm('Deseja realmente excluir esta tag?')) {
          Inertia.delete(`/tags/${id}`);
        }
      };

      return { newTag, errors, createTag, deleteTag };
    },
  };
  </script>
