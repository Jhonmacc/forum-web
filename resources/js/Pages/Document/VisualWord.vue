<template>
    <header class="p-6 mx-auto bg-neutral-900">
    <div class="bg-stone-100 shadow-inner rounded-xl max-w-7xl mx-auto">
      <!-- Título -->
      <h1 class="text-4xl font-extrabold text-center text-gray-900 mb-4">{{ word.title }}</h1>

      <!-- Subtítulo -->
      <h2 class="text-3xl text-center text-gray-700 mb-6">{{ word.subtitle }}</h2>

      <!-- Descrição -->
      <p class="text-lg text-gray-600 text-center mb-8">{{ word.description }}</p>

      <!-- Conteúdo -->
      <div v-html="content" class="prose prose-lg max-w-full text-gray-800 p-4"></div>
    </div>
</header>
  </template>

  <script>
  export default {
    props: {
      word: Object,
      content: String,
    },
  };
  </script>

  <style scoped>
  /* Estilos adicionais */
  .prose {
    line-height: 1.8;
  }

  .prose h1, .prose h2 {
    font-family: 'Poppins', sans-serif;
  }

  .prose p {
    margin-bottom: 1.5rem;
  }

  .prose ul, .prose ol {
    padding-left: 2rem;
  }

  .prose blockquote {
    border-left: 4px solid #2b6cb0;
    padding-left: 1rem;
    font-style: italic;
  }

  .prose pre {
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    font-family: 'Courier New', Courier, monospace;
  }

  /* Estilo para textos sublinhados */
  .prose u {
    text-decoration: underline;
    color: inherit;
  }

  /* Ajustes para lidar com o texto justificado */
  .prose p {
    text-align: justify;
    word-spacing: normal;
    text-justify: inter-word;
    hyphens: auto;
  }

  /* Para tratar espaços entre os parágrafos */
  .prose p + p {
    margin-top: 1rem;
  }
  </style>
