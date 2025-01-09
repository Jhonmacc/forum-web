<template>
    <div>
      <h1>{{ category.name }}</h1>
      <ul>
        <li v-for="topic in topics" :key="topic.id">
          <inertia-link :href="`/forum/topic/${topic.id}`">{{ topic.title }}</inertia-link>
        </li>
      </ul>

      <form @submit.prevent="createTopic">
        <input v-model="title" placeholder="Título" required />
        <textarea v-model="content" placeholder="Conteúdo" required></textarea>
        <button type="submit">Criar Tópico</button>
      </form>
    </div>
  </template>

  <script>
  export default {
    props: {
      category: Object,
      topics: Array,
    },
    data() {
      return {
        title: '',
        content: '',
      };
    },
    methods: {
      createTopic() {
        this.$inertia.post('/forum/topic', {
          title: this.title,
          content: this.content,
          category_id: this.category.id,
        });
      },
    },
  };
  </script>
