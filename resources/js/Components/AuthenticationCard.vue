<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-neutral-900">
        <div>
            <!-- <slot name="logo" /> -->
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-stone-800 utrashadow-lg overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
