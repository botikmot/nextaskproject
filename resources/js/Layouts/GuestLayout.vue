<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div class="flex justify-center">
            <Link href="/">
                <ApplicationLogo class="h-20 fill-current text-gray-500" />
            </Link>
        </div>
        <div class="text-navy-blue font-semibold text-center my-4 text-xl">Your All-in-One Hub for Social Networking, Project Management, and Daily Productivity</div>
        <div class="text-sky-blue font-semibold my-4 text-2xl">Welcome to NexTask!</div>
        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
