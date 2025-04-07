<!-- resources/js/Components/ForumPaginator.vue -->
<template>
    <div class="card">
        <Paginator
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :rowsPerPageOptions="[5, 10, 20, 30]"
            :first="first"
            @page="onPage"
        />
    </div>
</template>

<script setup>
import Paginator from 'primevue/paginator';
import { ref, watch } from 'vue';

// Props
const props = defineProps({
    totalRecords: {
        type: Number,
        required: true,
    },
    initialRows: {
        type: Number,
        default: 5,
    },
    initialPage: {
        type: Number,
        default: 1,
    },
});

// Emits
const emit = defineEmits(['page-change', 'rows-change']);

// Data
const first = ref((props.initialPage - 1) * props.initialRows);
const rowsPerPage = ref(props.initialRows);

// Watchers (Ajuste 5: Sincronização correta)
watch(() => props.initialPage, (newPage) => {
    first.value = (newPage - 1) * rowsPerPage.value;
});

watch(() => props.initialRows, (newRows) => {
    rowsPerPage.value = newRows;
    first.value = (props.initialPage - 1) * newRows; // Corrigir cálculo
});

// Methods (Ajuste 6: Emitir valores corretos)
const onPage = (event) => {
    first.value = event.first;
    rowsPerPage.value = event.rows;
    const newPage = event.page + 1; // Converter para base 1
    emit('page-change', newPage);
    emit('rows-change', event.rows);
};
</script>

<style scoped>
.card {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
}
</style>
