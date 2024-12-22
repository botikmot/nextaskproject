<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import FriendsList from './FriendsList.vue';
import { ref } from 'vue';

const page = usePage();
const conversations = page.props.sharedConversations || [];
const isNewConversation = ref(false)
console.log('My conversations-->>', conversations.private)

const contacts = []
</script>

<template>
    <h2 class="text-lg font-semibold mb-4">Conversations</h2>
            
    <!-- If no conversations exist -->
    <div v-if="conversations.private.length === 0" class="text-center text-gray-500 p-6">
        <p>No conversations yet. Start a new one!</p>
        <button @click="isNewConversation = true" class="mt-4 bg-sky-blue text-color-white py-2 px-4 rounded shadow hover:bg-blue-600">
            Start New Conversation
        </button>
        <button @click="startNewGroupChat" class="mt-2 bg-navy-blue text-color-white py-2 px-4 rounded shadow hover:bg-green-600">
            Start Group Chat
        </button>
    </div>
    
    <!-- Sample Conversation List -->
    <ul v-else class="space-y-2">
        <li v-for="contact in conversations.private" :key="contact.id" class="flex items-center p-2 bg-color-white rounded-lg shadow cursor-pointer hover:bg-blue-100">
            <img :src="'/' + contact.chat_image" alt="Profile" class="w-10 h-10 rounded-full mr-3">
            <div class="flex flex-col">
                <span class="font-medium">{{ contact.chat_name }}</span>
                <span class="text-sm text-gray-500">{{ contact.messages[0] }}</span>
            </div>
        </li>
    </ul>

    <Modal :show="isNewConversation" @close="isNewConversation = false">
        <FriendsList @close="isNewConversation = false"/>
    </Modal>

</template>