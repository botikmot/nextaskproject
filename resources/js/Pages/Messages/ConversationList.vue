<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import FriendsList from './FriendsList.vue';
import { ref, defineProps, defineEmits, computed } from 'vue'
const page = usePage();
const conversations = page.props.sharedConversations || [];
const isNewConversation = ref(false)
const selectedConversation = ref(null);
console.log('My conversations-->>', conversations.private)

const emit = defineEmits(['selectConversation']);

const handleConversationStarted = (conversation) => {
    // Close the modal
    isNewConversation.value = false;

    // Automatically select the new conversation
    selectedConversation.value = conversation;
    conversations.private.push(conversation)
    // Trigger the chat view (emit to parent or update UI as needed)
    console.log('New conversation selected:', conversation);
    emit('selectConversation', conversation);
};

const privateMessages = computed(() => {
    return conversations.private
});

const groupMessages = computed(() => {
    return conversations.group
});

</script>

<template>
    <h2 class="text-lg font-semibold text-navy-blue mb-4">Conversations</h2>
       
    <!-- If no conversations exist -->
    <div v-if="privateMessages.length === 0 && groupMessages.length === 0" class="text-center text-gray-500 p-6">
        <p>No conversations yet. Start a new one!</p>
        <button @click="isNewConversation = true" class="mt-4 bg-sky-blue text-color-white py-2 px-4 rounded shadow hover:bg-blue-600">
            Start New Conversation
        </button>
        <button @click="startNewGroupChat" class="mt-2 bg-navy-blue text-color-white py-2 px-4 rounded shadow hover:bg-green-600">
            Start Group Chat
        </button>
    </div>
    
    <!-- Conversation List -->
     <div v-else>

        <div v-if="privateMessages.length === 0" class="text-center text-gray-500 p-6">
            <button @click="isNewConversation = true" class="mt-4 bg-sky-blue text-color-white py-2 px-4 rounded shadow hover:bg-blue-600">
                Start New Conversation
            </button>
        </div>

        <ul v-if="privateMessages.length" class="space-y-2">
            <div class="flex justify-between">
                <div class="text-sky-blue font-bold">Personal</div>
                <div>
                    <button @click="isNewConversation = true" class="text-sm bg-sky-blue py-1 px-3 text-color-white rounded">+New Conversation</button>
                </div>
            </div>
            <li v-for="contact in privateMessages"
                :key="contact.id"
                class="flex items-center p-2 bg-color-white rounded-lg shadow cursor-pointer hover:bg-blue-100"
                @click="$emit('selectConversation', contact)"
            >
                <img :src="'/' + contact.chat_image" alt="Profile" class="w-10 h-10 object-cover rounded-full mr-3">
                <div class="flex flex-col">
                    <span class="font-medium">{{ contact.chat_name }}</span>
                    <span class="text-sm text-gray-500">{{ contact.messages.length ? contact.messages[0].text : '' }}</span>
                </div>
            </li>
        </ul>

        <div v-if="groupMessages.length === 0" class="text-center text-gray-500 p-6">
            <button @click="startNewGroupChat" class="mt-2 bg-navy-blue text-color-white py-2 px-4 rounded shadow hover:bg-green-600">
                Start Group Chat
            </button>
        </div>

        <ul v-if="groupMessages.length" class="space-y-2">
            <div class="flex justify-between">
                <div class="text-sky-blue font-bold">Group</div>
                <div>
                    <button @click="startNewGroupChat" class="text-sm bg-sky-blue py-1 px-3 text-color-white rounded">+New Group Messages</button>
                </div>
            </div>
            <li v-for="contact in groupMessages"
                :key="contact.id"
                class="flex items-center p-2 bg-color-white rounded-lg shadow cursor-pointer hover:bg-blue-100"
                @click="$emit('selectConversation', contact)"
            >
                <img :src="'/' + contact.chat_image" alt="Profile" class="w-10 h-10 object-cover rounded-full mr-3">
                <div class="flex flex-col">
                    <span class="font-medium">{{ contact.chat_name }}</span>
                    <span class="text-sm text-gray-500">{{ contact.messages.length ? contact.messages[0].text : '' }}</span>
                </div>
            </li>
        </ul>


    </div>

    <Modal :show="isNewConversation" @close="isNewConversation = false">
        <FriendsList @close="isNewConversation = false" @conversationStarted="handleConversationStarted"/>
    </Modal>

</template>