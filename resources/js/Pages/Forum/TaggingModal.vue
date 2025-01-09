<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded shadow-lg w-1/3">
      <h2 class="text-xl font-semibold mb-4">Escolher Tags</h2>

      <!-- Multiselect Component -->
      <multiselect
        v-model="selectedTagsInternal"
        :options="options"
        label="name"
        track-by="code"
        :multiple="true"
        placeholder="Selecione ou adicione tags"
        :taggable="false"
      ></multiselect>

      <div class="mt-4 flex justify-end">
        <button @click="submitSelection" class="bg-blue-500 text-white p-2 rounded">Selecionar</button>
        <button @click="$emit('close')" class="ml-2 p-2 text-gray-500">Fechar</button>
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
  },
  methods: {
    // Função que carrega as tags da API
    async loadTags() {
  try {
    const { data } = await axios.get('/show');
    console.log('Tags carregadas:', data);  // Verifique os dados retornados pela API

    
    this.options = data.map((tag) => ({
      id: tag.id,
      name: tag.name,
      code: tag.code,
    // this.options = data.map((tag, index) => ({
    //   id: tag.id || `temp-id-${index}`,  // Atribui um id temporário se for null
    //   name: tag.name,  // 'name' não está null, então deixamos ele como está
    //   code: tag.code,  // 'code' também não está null
    }));

    this.selectedTagsInternal = [...this.selectedTags];  // Mantém as tags passadas
  } catch (error) {
    console.error('Erro ao carregar tags:', error);
  }
},



selectTags() {
  const tags = this.selectedTagsInternal.map(tag => ({
    id: tag.id || `temp-id-${Math.random()}`, // Atribui um id temporário se for null
    name: tag.name || '',  // Garantir que 'name' não seja nulo
    code: tag.code || null, // Garantir que 'code' não seja nulo
  }));

  this.$emit('select-tags', tags);  // Emite as tags para o componente pai
},
    // Envia as tags selecionadas para o componente pai
    submitSelection() {
      // Emite as tags selecionadas com 'code' ao invés de 'id'
      this.$emit('select-tags', this.selectedTagsInternal);
      this.$emit('close'); // Fecha o modal
    },
  },
};
</script>

<style>
@import 'vue-multiselect/dist/vue-multiselect.min.css';
</style>
