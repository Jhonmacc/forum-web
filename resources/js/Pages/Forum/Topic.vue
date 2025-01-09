<template>
    <div>
      <h1>{{ topic.title }}</h1>
      <p>{{ topic.content }}</p>
      <div v-for="reply in topic.replies" :key="reply.id">
        <p>{{ reply.content }}</p>
      </div>

      <form @submit.prevent="createReply">
        <textarea v-model="content" placeholder="Resposta" required></textarea>
        <button type="submit">Responder</button>
      </form>
    </div>
  </template>

  <script>
  export default {
    props: {
      topic: Object,
    },
    data() {
      return {
        content: '',
      };
    },
    methods: {
      createReply() {
        this.$inertia.post(`/forum/topic/${this.topic.id}/reply`, {
          content: this.content,
        });
      },
    },
  };
  </script>
