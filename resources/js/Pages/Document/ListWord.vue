<template>
    <div class="p-6 bg-gray-100 min-h-screen">
        <form @submit.prevent="submitForm" class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">{{ $t('document.add_word') }}</h2>

            <div class="mb-4">
                <label class="block text-gray-700">{{ $t('document.title') }}</label>
                <input v-model="form.title" type="text" class="w-full border-gray-300 rounded-md" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">{{ $t('document.subtitle') }}</label>
                <input v-model="form.subtitle" type="text" class="w-full border-gray-300 rounded-md" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">{{ $t('document.description') }}</label>
                <textarea v-model="form.description" class="w-full border-gray-300 rounded-md"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">{{ $t('document.image') }}</label>
                <input ref="imageInput" @change="handleImage" type="file" accept="image/*" />
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">{{ $t('document.document') }}</label>
                <input ref="documentInput" @change="handleDocument" type="file" accept=".doc,.docx" />
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none">
                {{ $t('document.submit') }}
            </button>
        </form>

        <div v-if="words.length > 0" class="space-y-4 card">
            <div v-for="word in words" :key="word.id" class="flex items-center bg-white p-4 rounded-lg shadow-md">
                <div class="flex-1">
                    <img :src="`/storage/${word.path_image}`" alt="Preview" class="rounded-full h-12 w-12" />
                    <h3 class="text-lg font-semibold">{{ word.title }}</h3>
                    <h4 class="text-sm text-gray-600">{{ word.subtitle }}</h4>
                    <p class="text-sm text-gray-700">{{ word.description }}</p>
                    <a :href="`/words/${word.id}`"
                        class="mt-4 inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">{{ $t('document.view_word') }}</a>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-gray-500">
            {{ $t('document.no_words') }}
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

export default {
    setup() {
        const { t } = useI18n();
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
                Swal.fire({
                    title: t('common.error'),
                    text: error.message || t('document.error_loading'),
                    icon: 'error',
                    confirmButtonText: t('document.try_again'),
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
                    title: t('common.success'),
                    text: t('document.word_saved'),
                    icon: 'success',
                    confirmButtonText: t('common.ok'),
                    timer: 2000,
                    showConfirmButton: false,
                });

                fetchWords();
                resetForm();
            } catch (error) {
                Swal.fire({
                    title: t('common.error'),
                    text: error.message || t('document.error_submit'),
                    icon: 'error',
                    confirmButtonText: t('document.try_again'),
                });
            }
        };

        const handleImage = (event) => {
            form.value.path_image = event.target.files[0];
        };

        const handleDocument = (event) => {
            form.value.path_document = event.target.files[0];
        };

        const resetForm = () => {
            form.value = {
                title: '',
                subtitle: '',
                description: '',
                path_image: null,
                path_document: null,
            };

            imageInput.value.value = '';
            documentInput.value.value = '';
        };

        onMounted(fetchWords);

        return { form, words, fetchWords, submitForm, handleImage, handleDocument, resetForm, imageInput, documentInput };
    },
};
</script>
