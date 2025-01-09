<template>
    <div v-if="links && links.length > 0" class="flex justify-center space-x-2 mt-4">
      <button
        v-for="link in links"
        :key="link.label"
        :disabled="!link.url"
        @click="changePage(link.url)"
        class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ link.label }}
      </button>
    </div>
  </template>

  <script>
  export default {
    props: {
      links: {
        type: Array,
        default: () => [] // Garantir que seja um array vazio se não for fornecido
      }
    },
    methods: {
      changePage(url) {
        this.$emit('page-changed', url);
      }
    }
  };
  </script>
