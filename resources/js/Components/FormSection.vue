<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="profile-form-section md:grid md:grid-cols-[240px_minmax(0,1fr)] md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0">
            <form @submit.prevent="$emit('submitted')">
                <div
                    class="px-5 py-5 bg-gray-50 dark:bg-gray-950/70 sm:p-6"
                    :class="hasActions ? 'sm:rounded-t-2xl' : 'sm:rounded-2xl'"
                >
                    <div class="grid grid-cols-6 gap-6">
                        <slot name="form" />
                    </div>
                </div>

                <div v-if="hasActions" class="flex items-center justify-end px-5 py-4 bg-white dark:bg-gray-900 text-end sm:px-6 border-t border-gray-200 dark:border-gray-700 sm:rounded-b-2xl">
                    <slot name="actions" />
                </div>
            </form>
        </div>
    </div>
</template>
