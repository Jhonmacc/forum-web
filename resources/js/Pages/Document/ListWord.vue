<template>
    <div class="p-6 bg-gray-100 min-h-screen">
        <!-- Formulário -->
        <form @submit.prevent="submitForm" class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Adicionar Palavra</h2>

            <div class="mb-4">
                <label class="block text-gray-700">Título</label>
                <input v-model="form.title" type="text" class="w-full border-gray-300 rounded-md" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Subtítulo</label>
                <input v-model="form.subtitle" type="text" class="w-full border-gray-300 rounded-md" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Descrição</label>
                <textarea v-model="form.description" class="w-full border-gray-300 rounded-md"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Imagem</label>
                <input ref="imageInput" @change="handleImage" type="file" accept="image/*" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Documento</label>
                <input ref="documentInput" @change="handleDocument" type="file" accept=".doc,.docx" />
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none">
                Enviar
            </button>
        </form>

        <!-- Lista de Palavras -->
        <div v-if="words.length > 0" class="space-y-4 card">
            <div v-for="word in words" :key="word.id" class="flex items-center bg-white p-4 rounded-lg shadow-md">
                <div class="flex-1">
                    <img :src="`/storage/${word.path_image}`" alt="Preview" class="rounded-full h-12 w-12" />
                    <h3 class="text-lg font-semibold">{{ word.title }}</h3>
                    <h4 class="text-sm text-gray-600">{{ word.subtitle }}</h4>
                    <p class="text-sm text-gray-700">{{ word.description }}</p>
                    <a :href="`/words/${word.id}`"
                        class="mt-4 inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Visualizar
                        Palavra</a>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-gray-500">
            Nenhuma palavra encontrada.
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

export default {
    setup() {
        const form = ref({
            title: '',
            subtitle: '',
            description: '',
            path_image: null,
            path_document: null,
        });

        const words = ref([]);
        const imageInput = ref(null);
        const documentInput = ref(null);


        const fetchWords = async () => {
            try {
                const response = await axios.get('/api/words');
                words.value = response.data;
            } catch (error) {
                console.error(error);


                Swal.fire({
                    title: 'Erro!',
                    text: error.message || 'Erro desconhecido ao carregar palavras.',
                    icon: 'error',
                    confirmButtonText: 'Tentar novamente',
                });
            }
        };


        const submitForm = async () => {
            const formData = new FormData();
            Object.entries(form.value).forEach(([key, value]) => {
                formData.append(key, value);
            });

            try {
                await axios.post('/words', formData);


                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Palavra salva com sucesso!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    showConfirmButton: false,
                });

                fetchWords();
                resetForm();
            } catch (error) {
                console.error(error);


                Swal.fire({
                    title: 'Erro!',
                    text: error.message || 'Erro desconhecido ao enviar o formulário.',
                    icon: 'error',
                    confirmButtonText: 'Tentar novamente',
                });
            }
        };

        // Função para lidar com o envio da imagem
        const handleImage = (event) => {
            form.value.path_image = event.target.files[0];
        };

        // Função para lidar com o envio do documento
        const handleDocument = (event) => {
            form.value.path_document = event.target.files[0];
        };

        // Função para resetar o formulário e limpar os campos de imagem e documento
        const resetForm = () => {
            form.value = {
                title: '',
                subtitle: '',
                description: '',
                path_image: null,
                path_document: null,
            };

            // Limpar os campos de imagem e documento
            imageInput.value.value = '';
            documentInput.value.value = '';
        };

        // Chamando a função para buscar as palavras ao montar o componente
        onMounted(fetchWords);

        return { form, words, fetchWords, submitForm, handleImage, handleDocument, resetForm, imageInput, documentInput };
    },
};
</script>
