<template>
    <transition :name="transitionName" :mode="mode" @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter" @before-leave="beforeLeave" @leave="leave" @after-leave="afterLeave">
        <slot v-if="isVisible"></slot>
    </transition>
</template>

<script setup>
import { ref, watch } from 'vue';

// Props para personalizar o comportamento do componente
const props = defineProps({
    transitionName: {
        type: String,
        default: 'fade', // Nome da transição (ex.: 'fade', 'slide', etc.)
    },
    mode: {
        type: String,
        default: 'out-in', // Modo da transição ('out-in', 'in-out')
    },
    trigger: {
        type: [String, Number, Boolean, Object], // Prop para disparar a transição
        default: null,
    },
});

// Emits para notificar eventos de transição
const emit = defineEmits(['before-enter', 'enter', 'after-enter', 'before-leave', 'leave', 'after-leave']);

// Estado para controlar a visibilidade do conteúdo
const isVisible = ref(true);

// Função para disparar a transição
const triggerTransition = () => {
    isVisible.value = false; // Inicia a transição (saída)
};

// Observa mudanças no prop 'trigger' para disparar a transição
watch(() => props.trigger, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        triggerTransition();
    }
});

// Métodos para emitir eventos de transição
const beforeEnter = (el) => {
    emit('before-enter', el);
};
const enter = (el) => {
    emit('enter', el);
};
const afterEnter = (el) => {
    emit('after-enter', el);
};
const beforeLeave = (el) => {
    emit('before-leave', el);
};
const leave = (el) => {
    emit('leave', el);
};
const afterLeave = (el) => {
    emit('after-leave', el);
    isVisible.value = true; // Reexibe o conteúdo após a transição de saída
};

// Expor a função para uso externo, se necessário
defineExpose({ triggerTransition });
</script>

<style scoped>
/* Transição padrão: Fade */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Transição alternativa: Slide (de baixo para cima) */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.5s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Transição alternativa: Slide (da esquerda para a direita) */
.slide-left-enter-active,
.slide-left-leave-active {
    transition: all 0.5s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>
