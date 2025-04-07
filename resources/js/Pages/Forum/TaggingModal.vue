<template>
    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center p-4">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[85vh] flex flex-col">
        <!-- Cabeçalho do Modal -->
        <div class="p-8 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-2xl font-semibold text-gray-500">Escolha Tags para a sua Discussão</h2>
          <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-times text-xl"></i>
          </button>
        </div>

        <!-- Conteúdo do Modal -->
        <div class="p-8 flex-1 flex flex-col">
          <!-- Campo do Multiselect e Botão OK -->
          <div class="flex items-center space-x-4 mb-6">
            <multiselect v-model="selectedTagsInternal" :options="options" label="name" track-by="id" :multiple="true"
              placeholder="Selecione as tags" :taggable="false" :show-labels="false" :searchable="false"
              class="flex-1 border border-gray-200 rounded-md min-h-[48px]">

              <!-- Customização do item no dropdown -->
              <template #option="{ option }">
                <div class="flex items-start space-x-4 py-3">
                  <i :class="option.icon" class="text-xl mt-1" :style="{ color: option.color || '#ccc' }"></i>
                  <div class="flex-1">
                    <span class="text-gray-800 font-medium text-lg">{{ option.name }}</span>
                    <p class="text-sm text-gray-500 mt-1">{{ option.description || 'Nenhuma descrição disponível.' }}</p>
                  </div>
                </div>
              </template>

              <!-- Customização das tags selecionadas -->
              <template #tag="{ option, remove }">
                <span class="inline-flex items-center space-x-1 text-gray-800 text-sm font-medium mr-2">
                  <i :class="option.icon" class="text-sm" :style="{ color: option.color || '#ccc' }"></i>
                  <span>{{ option.name }}</span>
                  <button @click="remove(option)" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times text-xs"></i>
                  </button>
                  <span class="last:hidden">,</span>
                </span>
              </template>
            </multiselect>

            <!-- Botão OK -->
            <button @click="submitSelection" class="bg-yellow-500 text-white py-2 px-6 rounded-full hover:bg-yellow-600">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
<script>
import Multiselect from 'vue-multiselect';
import axios from 'axios';

export default {
  components: { Multiselect },
  props: {
    selectedTags: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      options: [], // Lista de tags carregadas
      selectedTagsInternal: [], // Tags selecionadas localmente
    };
  },
  mounted() {
    this.loadTags(); // Carregar tags da API ao montar
    this.selectedTagsInternal = [...this.selectedTags]; // Inicializa com as tags já selecionadas
  },
  methods: {
    // Função que carrega as tags da API
    async loadTags() {
      try {
        const { data } = await axios.get('/show');
        console.log('Tags carregadas:', data);

        this.options = data.map(tag => ({
          id: tag.id,
          name: tag.name,
          code: tag.code,
          color: tag.color || '#ccc', // Cor padrão caso a tag não tenha uma cor definida
          icon: tag.icon || 'fa-solid fa-tag', // Ícone padrão caso não tenha um ícone definido
          description: tag.description || '', // Descrição da tag
        }));
      } catch (error) {
        console.error('Erro ao carregar tags:', error);
      }
    },
    // Envia as tags selecionadas para o componente pai
    submitSelection() {
      this.$emit('select-tags', this.selectedTagsInternal);
      this.$emit('close'); // Fecha o modal
    },
  },
};
</script>

<style>
/* Importação do CSS do vue-multiselect */
@import 'vue-multiselect/dist/vue-multiselect.min.css';
</style>

<style scoped>
/* Estilização do Modal */
.fixed {
  z-index: 50; /* Garante que o modal fique acima de outros elementos */
}

/* Estilização do Multiselect */
.multiselect__tags {
  border: none !important;
  padding: 0.75rem !important; /* Aumenta o padding interno */
  min-height: 48px !important;
  display: flex !important;
  align-items: center !important;
  flex-wrap: wrap !important;
  background-color: #f9fafb !important; /* Fundo claro */
}

.multiselect__placeholder {
  color: #6b7280 !important; /* Cor do placeholder */
  padding-left: 0.75rem !important;
  font-size: 1rem !important; /* Tamanho do texto do placeholder */
}

.multiselect__content-wrapper {
  border: 1px solid #e5e7eb !important;
  border-radius: 0.375rem !important;
  max-height: 500px !important; /* Aumenta a altura máxima do dropdown */
  overflow-y: auto !important; /* Adiciona scrollbar vertical */
  overflow-x: hidden !important; /* Remove scrollbar horizontal */
  background-color: #fff !important;
  width: 100% !important; /* Garante que o dropdown ocupe a largura total */
}

.multiselect__content {
  width: 100% !important; /* Garante que o conteúdo do dropdown ocupe a largura total */
}

.multiselect__option {
  padding: 0.75rem 1.5rem !important; /* Aumenta o padding dos itens no dropdown */
  transition: background-color 0.2s ease !important;
  white-space: normal !important; /* Permite que o texto quebre em várias linhas */
}

.multiselect__option:hover {
  background-color: #f9fafb !important; /* Fundo claro ao passar o mouse */
}
</style>

<style>
/* Importação do CSS do vue-multiselect */
@import 'vue-multiselect/dist/vue-multiselect.min.css';
</style>
