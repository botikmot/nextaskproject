<script setup>
import { ref, computed, watch, onMounted, nextTick, onUnmounted } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import moment from 'moment';
import UserImage from '@/Components/UserImage.vue';

const props = defineProps(['selectedConversation']);
const messages = ref([]);
const page = usePage();
const authenticatedUserId = page.props.auth.user.id
const chatContainer = ref(null);

const form = useForm({
    text: '',
});

watch(
    () => props.selectedConversation, 
    (newConversation, oldConversation) => {
        if (oldConversation) {
            // Leave the old conversation channel
            Echo.leave(`conversation.${oldConversation.id}`);
        }

        if (newConversation) {
            fetchMessages(newConversation.id);

            Echo.private(`conversation.${newConversation.id}`)
                .listen('MessageSent', (event) => {
                    console.log('event-->>', event)
                    // Update the messages list with the new message
                    messages.value.push(event.message);
                    nextTick(() => {
                        scrollToBottom();
                    });
            });

            nextTick(() => {
                scrollToBottom();
            });

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
                //fetchMessages(props.selectedConversation.id);
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
    return moment(date).fromNow(true)
}

const scrollToBottom = () => {
    console.log('console.log(chatContainer.value);', chatContainer.value);
    if (chatContainer.value) {
        //chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        setTimeout(() => {
          chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }, 500); // Adjust the delay as needed
    }
};

onMounted(() => {
    
});

</script>

<template>
    <div class="flex-1">
        <!-- If no contact is selected -->
        <div v-if="!selectedConversation" class="flex items-center justify-center flex-col h-full text-center text-gray-500">
            <p>Select a conversation to start chatting!</p>
        </div>
            
            <!-- If a contact is selected, show the conversation -->
        <div v-else class="h-full">
            <!-- Chat Header -->
            <div class="flex items-center mb-4 border-b border-dark-gray pb-2">
                <img v-if="selectedConversation.type == 'private'" :src="'/' + selectedConversation.chat_image" alt="Profile" class="w-10 h-10 rounded-full object-cover mr-3">
                <h2 class="text-lg font-semibold">{{ selectedConversation.type == 'private' ? selectedConversation.chat_name : selectedConversation.name }}</h2>
            </div>

            <!-- Chat Messages -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto" style="height: calc(100vh - 300px);">
                <div
                    v-for="message in messages"
                    :key="message.id"
                    :class="{
                    'flex justify-end': message.user_id === authenticatedUserId, // Auth user's message aligned to the right
                    'flex justify-start': message.user_id !== authenticatedUserId, // Chat mate's message aligned to the left
                    }"
                    class=" my-2"
                >
                    <div :class="`${message.user_id === authenticatedUserId ? 'bg-sky-blue text-color-white' : 'bg-light-gray'} px-3 py-2 rounded-lg shadow`">

                        <div class="flex mb-2 items-center" v-if="selectedConversation.type === 'group' && message.user_id !== authenticatedUserId">
                            <UserImage class="w-10 h-10 rounded-full object-cover mr-2" :user="message.user"/>
                            <div>
                                <div class="text-sm font-bold text-navy-blue">{{ message.user.name }}</div>
                                <div class="text-xs text-gray">{{ formatDate(message.created_at) }}</div>
                            </div>
                        </div>

                        <p :class="{
                            'text-right': message.user_id === authenticatedUserId,  // Align text to the right for auth user
                            'text-left': message.user_id !== authenticatedUserId,  // Align text to the left for chat mate
                        }"
                        class=""
                        >{{ message.text }}</p>
                        <div
                            v-if="selectedConversation.type !== 'group' || message.user_id === authenticatedUserId"
                            :class="`${message.user_id === authenticatedUserId ? 'text-color-white' : 'text-gray' } text-xs`"
                        >
                            {{ formatDate(message.created_at) }}
                        </div>
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