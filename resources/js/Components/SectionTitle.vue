<template>
    <div class="md:col-span-1 flex justify-between p-5 sm:p-6">
        <div class="sm:px-0">
            <h3 class="text-base font-extrabold tracking-tight text-gray-900 dark:text-gray-100">
                <slot name="title" />
            </h3>

            <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                <slot name="description" />
            </p>
        </div>

        <div class="px-4 sm:px-0">
            <slot name="aside" />
        </div>
    </div>
</template>
