<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="profile-action-section md:grid md:grid-cols-[240px_minmax(0,1fr)] md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0">
            <div class="px-5 py-5 sm:p-6 bg-gray-50 dark:bg-gray-950/70 sm:rounded-2xl">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
