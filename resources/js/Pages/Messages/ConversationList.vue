<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import FriendsList from './FriendsList.vue';
import { ref, defineProps, defineEmits, computed, onMounted, watch } from 'vue'

const page = usePage();
const conversations = page.props.sharedConversations || [];
const isNewConversation = ref(false)
const selectedConversation = ref(null);
let unread = ref(0)


const props = defineProps({
  projects: Array,
  notif: Object,
});

console.log('conversations', conversations)
const conversationType = ref(null)

const emit = defineEmits(['selectConversation']);

const handleConversationStarted = (conversation) => {
    // Close the modal
    isNewConversation.value = false;

    // Automatically select the new conversation
    selectedConversation.value = conversation;
    if(conversation.type == 'private'){
        conversations.private.push(conversation)
    }else{
        conversations.group.push(conversation)
    }
    
    // Trigger the chat view (emit to parent or update UI as needed)
    emit('selectConversation', conversation);
};

const privateMessages = computed(() => {
    return [...(conversations.private || [])]
});

const groupMessages = computed(() => {
    return conversations.group
});

watch(
  () => props.notif, // Reactive source to watch
  (newValue, oldValue) => {
    console.log('notif changed:', { newValue, oldValue });
        if (newValue?.data?.type === 'chat') {
            const conversation = privateMessages.value.find(
                (element) => element.id === newValue.data.conversation_id
            );

            if (conversation) {
                conversation.messages[0].text = newValue.data.text;
                if(conversation.id !== selectedConversation.value.id){
                    unread.value++
                    conversation.unread = unread.value
                }
            }

        }
  },
  { deep: true }
);

const currentConversation = (conversation) => {
    conversation.unread = null
    selectedConversation.value = conversation
    emit('selectConversation', conversation)
}



const newConversation = (type) => {
    conversationType.value = type
    isNewConversation.value = true
}

const truncateText = (text, maxLength = 45) => {
    if (!text || typeof text !== "string") return "";
    return text.length > maxLength ? `${text.substring(0, maxLength)}...` : text;
}

onMounted(() => {
    console.log(Array.isArray(privateMessages)); // Should log true
    console.log(privateMessages);

    Echo.private('users-status')
        .listen('UserStatusChanged', (event) => {
            const contact = privateMessages.value.find(contact => {
                return contact.user_id === event.user.id;
            });
            if (contact) {
                contact.status = event.status;
            }

    });
});

</script>

<template>
    <h2 class="text-lg font-semibold text-navy-blue mb-4">Conversations</h2>
       
    <!-- If no conversations exist -->
    <div v-if="privateMessages.length === 0 && groupMessages.length === 0" class="text-center text-gray-500 p-6">
        <p>No conversations yet. Start a new one!</p>
        <button @click="newConversation('private')" class="mt-4 bg-sky-blue text-color-white py-2 px-4 rounded shadow hover:bg-blue-600">
            Start New Conversation
        </button>
        <button @click="newConversation('group')" class="mt-2 bg-navy-blue text-color-white py-2 px-4 rounded shadow hover:bg-green-600">
            Start Group Chat
        </button>
    </div>
    
    <!-- Conversation List -->
     <div class="w-full" v-else>

        <div v-if="privateMessages.length === 0" class="text-center border-b border-sky-blue text-gray-500 p-6">
            <button @click="newConversation('private')" class="mt-4 bg-sky-blue text-color-white py-2 px-4 rounded shadow hover:bg-blue-600">
                Start New Conversation
            </button>
        </div>

        <ul v-if="privateMessages.length" class="space-y-2 pb-6 border-b border-sky-blue">
            <div class="flex justify-between">
                <div class="text-sky-blue font-bold">Personal</div>
                <div>
                    <button @click="newConversation('private')" class="text-sm bg-sky-blue py-1 px-3 text-color-white rounded">+New Conversation</button>
                </div>
            </div>
            <li v-for="contact in privateMessages"
                :key="contact.id"
                class="flex items-center p-2 bg-color-white relative rounded-lg shadow cursor-pointer hover:bg-blue-100"
                @click="currentConversation(contact)"
            >
                <div v-if="contact.status == 'online'" :class="`absolute ${ contact.unread ? 'bg-red-warning w-5 h-5' : 'bg-green w-3 h-3'} rounded-full top-3 text-color-white text-xs left-10 border-2 border-color-white`">
                    <span class="flex justify-center" v-if="contact.unread">{{ contact.unread }}</span>
                </div>
                <div v-else class="absolute w-3 h-3 bg-gray rounded-full top-3 left-10 border-2 border-color-white"></div>

                <img :src="'/' + contact.chat_image" alt="Profile" class="w-10 h-10 object-cover rounded-full mr-3">
                <div class="flex flex-col">
                    <span class="font-medium">{{ contact.chat_name }}</span>
                    <span class="text-sm text-gray-500">{{ contact.messages.length ? truncateText(contact.messages[0].text) : '' }}</span>
                </div>
            </li>
        </ul>

        <div v-if="groupMessages.length === 0" class="text-center text-gray-500 p-6">
            <button @click="newConversation('group')" class="mt-2 bg-navy-blue text-color-white py-2 px-4 rounded shadow hover:bg-green-600">
                Start Group Chat
            </button>
        </div>

        <ul v-if="groupMessages.length" class="space-y-2 mt-6">
            <div class="flex justify-between">
                <div class="text-sky-blue font-bold">Group</div>
                <div>
                    <button @click="newConversation('group')" class="text-sm bg-sky-blue py-1 px-3 text-color-white rounded">+New Group Conversation</button>
                </div>
            </div>
            <li v-for="contact in groupMessages"
                :key="contact.id"
                class="flex items-center p-2 bg-color-white rounded-lg shadow cursor-pointer hover:bg-blue-100"
                @click="$emit('selectConversation', contact)"
            >
                <div class="">
                    <div class="flex items-center">
                        <div class="font-bold text-navy-blue">{{ contact.name }}</div>
                        <div class="flex items-center pl-3">
                            <div v-for="(member, index) in contact.users.slice(0, 5)" :key="member.id" class="relative -mr-3">
                                <img :src="'/' + member.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                            </div>
                            
                            <div v-if="contact.users.length > 5" class="flex items-center text-navy-blue ml-3">
                                <span class="text-lg font-bold">+{{ contact.users.length - 5 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full text-sm text-gray-500">{{ contact.messages.length ? truncateText(contact.messages[0].text) : '' }}</div>
                </div>
            </li>
        </ul>


    </div>

    <Modal :show="isNewConversation" @close="isNewConversation = false">
        <FriendsList @close="isNewConversation = false" :type="conversationType" :projects="projects" @conversationStarted="handleConversationStarted"/>
    </Modal>

</template>