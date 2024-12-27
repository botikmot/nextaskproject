<script setup>
import { ref, computed, watch, onMounted, nextTick, onUnmounted } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import moment from 'moment';
import UserImage from '@/Components/UserImage.vue';
import axios from 'axios';

const props = defineProps(['selectedConversation']);
const messages = ref([]);
const page = usePage();
const authenticatedUserId = page.props.auth.user.id
const chatContainer = ref(null);
const hasMoreMessages = ref(true);
const currentPage = ref(1);
const isFetching = ref(false);
const conversationId = ref(null);
const messagesIds = ref([])

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

            conversationId.value = newConversation.id
            messages.value = []
            hasMoreMessages.value = true
            fetchMessages(1, newConversation.id);
            
            Echo.private(`conversation.${newConversation.id}`)
                .listen('MessageSent', (event) => {
                    // Update the messages list with the new message
                    const message_ids = []
                    message_ids.push(event.message.id)
                    messagesIds.value = message_ids
                    messages.value = messages.value.map((message) => {
                        if (message.readers) {
                            message.readers = message.readers.filter(
                                (reader) => reader.id !== event.message.user_id
                            );
                        }
                        return message;
                    });
                    
                    messages.value.push(event.message);
                    nextTick(() => {
                        scrollToBottom();
                    });
            });

            Echo.private(`conversation.${newConversation.id}`)
                .listen('MessageRead', (event) => {
                    // Update the messages list with the new message

                    // Extract the IDs of the readers from the event
                    const eventReaderIds = event.message.readers.map(reader => reader.id);

                    // Update the messages list
                    messages.value = messages.value.map((message) => {
                        // Remove readers from the message whose IDs are in `eventReaderIds`
                        message.readers = message.readers.filter(reader => !eventReaderIds.includes(reader.id));

                        // If this is the specific message being updated, replace its readers with `event.message.readers`
                        if (message.id === event.message.id) {
                            message.readers = [...event.message.readers];
                        }

                        return message; // Return the updated message
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
const fetchMessages = async (page, conversationId) => {
    if (isFetching.value || !hasMoreMessages.value) return;
    
    isFetching.value = true;
    
    try {
        const response = await fetch(`/conversations/${conversationId}/messages?page=${page}`);
        const data = await response.json();
       
        if(page === 1){
            const message_ids = [];
            const messageId = data.data[0].id
            message_ids.push(messageId)
            messagesIds.value = message_ids
            markMessagesAsRead(message_ids)
        }

        if (data.data.length > 0) {
            // Prepend older messages
            messages.value = [...data.data.reverse(), ...messages.value];
        }

        if (!data.next_page_url) {
            hasMoreMessages.value = false; // No more pages
        }
        
        currentPage.value = page;
        
    } catch (error) {
        console.error('Error fetching messages:', error);
    } finally {
        isFetching.value = false;
    }
}

const markMessagesAsRead = async (messageIds) => {
    try {
        const data = {
            message_ids: messageIds,
            conversation_id: conversationId.value,
        }
        const response = await axios.post('/messages/mark-as-read', data);
        
    } catch (error) {
        console.error('Error marking messages as read:', error);
    }
}

const handleScroll = () => {
    const messageArea = document.querySelector('.message-area');

    // Detect if the user scrolled to the top
    if (messageArea.scrollTop === 0 && hasMoreMessages.value) {
    const previousHeight = messageArea.scrollHeight;

        // Fetch the next page of messages
        fetchMessages(currentPage.value + 1, conversationId.value).then(() => {
            // Adjust scroll position to prevent "jumping"
            messageArea.scrollTop = messageArea.scrollHeight - previousHeight;
        });
    }
};


const formatDate = (date) => {
    return moment(date).fromNow(true)
}

const scrollToBottom = () => {
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
            <div ref="chatContainer" @scroll="handleScroll" class="message-area flex-1 overflow-y-auto" style="height: calc(100vh - 300px);">

                <div v-if="isFetching" class="loading-indicator">Loading Message...</div>
                <div
                    v-for="message in messages"
                    :key="message.id"
                    :class="{
                    'flex justify-end': message.user_id === authenticatedUserId, // Auth user's message aligned to the right
                    'flex justify-start': message.user_id !== authenticatedUserId, // Chat mate's message aligned to the left
                    }"
                    class="relative my-2"
                >
                    <div :class="`${message.user_id === authenticatedUserId ? 'bg-sky-blue text-color-white' : 'bg-light-gray'} px-3 py-2 rounded-lg relative shadow`">

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

                        <!-- Read Receipts -->
                        <div v-if="message.readers && message.readers.length" :class="`read-receipts absolute ${message.user_id === authenticatedUserId ? '-left-6' : '-right-6'} bottom-0 `">
                            <div v-if="message.readers.length > 1" :class="`flex items-center ${message.user_id === authenticatedUserId ? 'pr-3' : 'pl-3'}`">
                                <div v-for="(reader, index) in message.readers.slice(0, 5)" :key="reader.id" class="relative -mr-2">
                                    <img :src="'/' + reader.profile_image" alt="Profile" class="w-5 h-5 rounded-full object-cover border-2 border-color-white" />
                                </div>
                                
                                <div v-if="message.readers.length > 5" class="flex items-center text-navy-blue ml-6">
                                    <span class="text-lg font-bold">+{{ message.readers.length - 5 }}</span>
                                </div>
                            </div>
                            
                            <img
                                v-else
                                v-for="reader in message.readers"
                                :key="reader.id"
                                :src="'/' + reader.profile_image"
                                :alt="reader.name"
                                class="w-5 h-5 rounded-full border border-color-white object-cover shadow"
                                :title="reader.name"
                            />
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
                @focus="markMessagesAsRead(messagesIds)"
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
.loading-indicator {
  text-align: center;
  margin: 10px 0;
  font-size: 14px;
  color: gray;
}
/* Custom styles for chat bubbles and layout */
</style>