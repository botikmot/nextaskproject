<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import moment from 'moment';

const props = defineProps(['selectedConversation']);
const messages = ref([]);
const page = usePage();
const authenticatedUserId = page.props.auth.user.id

const form = useForm({
    text: '',
});

watch(
    () => props.selectedConversation, 
    (newConversation, oldConversation) => {
        if (newConversation) {
            fetchMessages(newConversation.id);
        }
    },
    { immediate: true } // To run the watcher immediately when the component is mounted
);


const sendMessage = () => {
    form.post(`/conversations/${props.selectedConversation.id}/messages`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {            
            if(response.props.flash.success){
                form.reset()
                fetchMessages(props.selectedConversation.id);
            }
        },
        onError: (error) => {
            console.error('Error adding subtasks', error)
        }
    })
}

// Function to fetch messages
const fetchMessages = async (conversationId) => {
    try {
        const response = await fetch(`/conversations/${conversationId}/messages`);
        const data = await response.json();
        console.log('messages', data)
        messages.value = data; // Assuming the API returns a list of messages
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
}

const formatDate = (date) => {
    return moment(date).fromNow()
}

</script>

<template>
    <div class="flex-1">
        <!-- If no contact is selected -->
        <div v-if="!selectedConversation" class="flex items-center justify-center flex-col h-full text-center text-gray-500">
                <p>Select a conversation to start chatting!</p>
            </div>
            
            <!-- If a contact is selected, show the conversation -->
            <div v-else class="flex flex-col h-full">
    <!-- Chat Header -->
    <div class="flex items-center mb-4 border-b border-dark-gray pb-2">
      <img :src="'/' + selectedConversation.chat_image" alt="Profile" class="w-10 h-10 rounded-full object-cover mr-3">
      <h2 class="text-lg font-semibold">{{ selectedConversation.chat_name }}</h2>
    </div>

    <!-- Chat Messages -->
    <div class="flex-1 overflow-y-auto space-y-4">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="{
          'flex justify-end': message.user_id === authenticatedUserId, // Auth user's message aligned to the right
          'flex justify-start': message.user_id !== authenticatedUserId, // Chat mate's message aligned to the left
        }"
        class=""
      >
        <div :class="`${message.user_id === authenticatedUserId ? 'bg-sky-blue text-color-white' : 'bg-light-gray'} px-3 py-2 rounded-lg shadow`">
            <p :class="{
                'text-right': message.user_id === authenticatedUserId,  // Align text to the right for auth user
                'text-left': message.user_id !== authenticatedUserId,  // Align text to the left for chat mate
            }"
            class=""
            >{{ message.text }}</p>
            <span class="text-xs text-gray-500">{{ formatDate(message.created_at) }}</span>
        </div>
      </div>
    </div>

    <!-- Message Input -->
    <div class="mt-4 flex items-center border-t border-dark-gray pt-4">
      <input
        type="text"
        v-model="form.text"
        placeholder="Type a message..."
        class="flex-1 border p-2 rounded-md mr-2"
      />
      <button
        @click="sendMessage"
        class="bg-blue-500 text-white py-2 px-4 rounded shadow hover:bg-blue-600"
      >
        Send
      </button>
    </div>
  </div>

    </div>
</template>

<style scoped>
/* Custom styles for chat bubbles and layout */
</style>