<!-- resources/js/Components/TextQuill.vue -->
<template>
    <div>
        <QuillEditor
            ref="quillEditor"
            v-model:content="localContent"
            :contentType="contentType"
            :options="editorOptions"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            :class="{ 'border-red-500': hasError }"
        />
    </div>
</template>

<script>
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import Swal from 'sweetalert2';
import axios from 'axios';

export default {
    name: 'TextQuill',
    components: { QuillEditor },
    props: {
        content: {
            type: String,
            default: '',
        },
        contentType: {
            type: String,
            default: 'html',
        },
        hasError: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            localContent: this.content,
            editorOptions: {
                theme: 'snow',
                placeholder: 'Escreva a descrição do post aqui...',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [1, 2, false] }],
                            ['bold', 'italic', 'underline'],
                            ['link', 'image'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['clean'],
                        ],
                        handlers: {
                            image: this.imageHandler,
                        },
                    },
                },
            },
        };
    },
    watch: {
        content(newValue) {
            this.localContent = newValue;
        },
        localContent(newValue) {
            this.$emit('update:content', newValue);
        },
    },
    methods: {
        imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.accept = 'image/jpeg,image/png,image/jpg,image/gif';

            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                if (file) {
                    console.log('Arquivo selecionado:', file.name);
                    console.log('Tamanho do arquivo:', file.size, 'bytes');
                    console.log('Tipo do arquivo:', file.type);

                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        console.log('Enviando imagem para o servidor...');
                        const response = await axios.post('/posts/upload-image', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        });

                        console.log('Resposta do servidor:', response.data);
                        const url = response.data.url;
                        console.log('URL da imagem retornada:', url);

                        // Atraso para garantir que o backend salvou a imagem
                        await new Promise(resolve => setTimeout(resolve, 500));

                        // Inserir a imagem no editor
                        const quill = this.$refs.quillEditor.getQuill();
                        const range = quill.getSelection(true);
                        if (range) {
                            quill.insertEmbed(range.index, 'image', url);
                            console.log('Imagem inserida no editor:', url);
                        } else {
                            console.error('Nenhuma seleção encontrada no editor');
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Não foi possível inserir a imagem no editor.',
                            });
                        }
                    } catch (error) {
                        console.error('Erro ao fazer upload da imagem:', error);
                        console.error('Resposta do servidor (erro):', error.response?.data);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao fazer upload da imagem!',
                            text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                        });
                    }
                }
            };
        },
        clearContent() {
            this.localContent = '';
        },
    },
};
</script>

<style scoped>
:deep(.ql-editor) {
    min-height: 150px;
    max-height: 400px;
    overflow-y: auto;
}
</style>
