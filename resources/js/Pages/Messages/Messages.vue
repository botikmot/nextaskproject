<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ConversationList from './ConversationList.vue';
import MainChatArea from './MainChatArea.vue';

</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout pageTitle="Messages">
        <!-- Chat Page Layout -->
    <div class="flex w-full space-x-4 p-6 bg-linen">
        <!-- Sidebar for Conversations -->
        <aside class="w-1/4 bg-crystal-blue p-4 rounded-lg shadow">
            <ConversationList />
        </aside>

        <!-- Main Chat Area -->
        <section class="flex-1 bg-color-white p-4 rounded-lg shadow flex flex-col">
            <MainChatArea />
        </section>
    </div>
    </AuthenticatedLayout>
</template>


