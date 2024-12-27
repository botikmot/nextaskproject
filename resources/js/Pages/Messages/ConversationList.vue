<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import FriendsList from './FriendsList.vue';
import { ref, defineProps, defineEmits, computed, onMounted, watch } from 'vue'
import axios from 'axios';

const page = usePage();
const conversations = page.props.sharedConversations || [];
const isNewConversation = ref(false)
const selectedConversation = ref(null);


const props = defineProps({
  projects: Array,
  notif: Array,
  userChangedStatus: Object,
});

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

const markNotificationsAsRead = (notifications, activeConversationId) => {
  if (!activeConversationId) return;

  notifications.forEach((notification) => {
    if (
      notification?.data?.type === 'chat' &&
      notification?.data?.conversation_id === activeConversationId &&
      !notification?.read_at
    ) {
      notification.read_at = new Date().toISOString(); // Mark as read
      axios.post('/notifications/chat/read', {
        type: 'chat',
        conversation_id: activeConversationId,
      });
    }
  });
};

const computeUnreadCounts = (messages, notifications, activeConversationId) => {
  return (messages || []).map((message) => {
    const unreadCount = notifications.filter(
      (notification) =>
        notification?.data?.type === 'chat' &&
        notification?.data?.conversation_id === message.id &&
        notification?.data?.conversation_id !== activeConversationId &&
        !notification?.read_at
    ).length;

    return {
      ...message,
      unreadCount: parseInt(unreadCount, 10),
    };
  });
};

const privateMessages = computed(() => {
  const notifications = Array.isArray(props.notif) ? props.notif : [];
  const activeConversationId = selectedConversation.value?.id || null;

  markNotificationsAsRead(notifications, activeConversationId);

  return computeUnreadCounts(conversations.private, notifications, activeConversationId);
});

const groupMessages = computed(() => {
  const notifications = Array.isArray(props.notif) ? props.notif : [];
  const activeConversationId = selectedConversation.value?.id || null;

  markNotificationsAsRead(notifications, activeConversationId);

  return computeUnreadCounts(conversations.group, notifications, activeConversationId);
});

watch(
  () => props.userChangedStatus,
  (newValue, oldValue) => {
    if (newValue.user.id) {
        const user = privateMessages.value.find(
                (element) => element.user_id === newValue.user.id
            );
        if (user) {
            user.status = newValue.status;
        }
    }
  },
  { deep: true }
);


watch(
  () => props.notif, // Reactive source to watch
  (newValue, oldValue) => {
        const len = newValue.length - 1
        if (newValue[len]?.data?.type === 'chat') {
            const conversation = privateMessages.value.find(
                (element) => element.id === newValue[len].data.conversation_id
            );

            if (conversation) {
                conversation.messages[0].text = newValue[len].data.text;
            }

            const gconversation = groupMessages.value.find(
                (element) => element.id === newValue[len].data.conversation_id
            );

            if (gconversation) {
                gconversation.messages[0].text = newValue[len].data.text;
            }


        }
  },
  { deep: true }
);

const currentConversation = async (conversation) => {
    conversation.unreadCount = 0
    selectedConversation.value = conversation
    emit('selectConversation', conversation)
    try { 
        const data = {
            type: 'chat',
            conversation_id: conversation.id,
        }
        const response = await axios.post('/notifications/chat/read', data);
        if (response.data.success) {
            // Optionally, update the UI by filtering out the read notifications
            props.notif = props.notif.map((notif) =>
                notif.data.type === 'chat' ? { ...notif, read_at: new Date().toISOString() } : notif
            );
        }
    } catch (error) {
        console.error('Error marking chat notifications as read:', error);
    }

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
                <div v-if="contact.status == 'online'" :class="`absolute ${ contact.unreadCount > 0 ? 'bg-red-warning w-5 h-5' : 'bg-green w-3 h-3'} rounded-full top-3 text-color-white text-xs left-10 border-2 border-color-white`">
                    <span class="flex justify-center" v-if="contact.unreadCount > 0">{{ contact.unreadCount }}</span>
                </div>
                <div v-else :class="`absolute ${ contact.unreadCount > 0 ? 'bg-red-warning w-5 h-5' : 'bg-gray w-3 h-3'} rounded-full top-3 left-10 border-2 border-color-white text-color-white text-xs`">
                    <span class="flex justify-center" v-if="contact.unreadCount > 0">{{ contact.unreadCount }}</span>
                </div>

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
                class="flex items-center p-2 bg-color-white rounded-lg relative shadow cursor-pointer hover:bg-blue-100"
                @click="currentConversation(contact)"
            >
                <div class="relative">
                    <div class="flex items-center relative w-full">
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
                <div v-if="contact.unreadCount > 0" class="absolute top-3 right-3 bg-red-warning flex items-center justify-center w-8 h-8 rounded-full border-2 border-color-white text-color-white text-xs">
                    <span class="flex">{{ contact.unreadCount }}</span>
                </div>
            </li>
        </ul>


    </div>

    <Modal :show="isNewConversation" @close="isNewConversation = false">
        <FriendsList @close="isNewConversation = false" :type="conversationType" :projects="projects" @conversationStarted="handleConversationStarted"/>
    </Modal>

</template>