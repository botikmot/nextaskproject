<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import UserImage from '@/Components/UserImage.vue';
import axios from 'axios';
import { defineEmits } from 'vue';

const emit = defineEmits(['conversationStarted']);
const page = usePage();
const sharedFriends = page.props.sharedFriends || [];

const startConversation = async (id) => {
    try {
        console.log('Friend ID', id);
        const data = {
            friend_id: id,
        };
        const response = await axios.post('/conversations/private', data);
        console.log('Conversation', response);

        // Emit the new conversation details to the parent
        if (response.data.success) {
            const conversation = response.data.conversation;
            // Emit the conversation to the parent
            emit('conversationStarted', conversation);
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
    }
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Friends</h3>
        <div
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="friend in sharedFriends"
            :key="friend.id"
        >
            <div>
                <!-- Profile Section -->
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <UserImage class="h-8 w-8 rounded-full object-cover" :user="friend" />
                        <div class="pl-3">
                            <div class="text-navy-blue font-medium text-sm">{{ friend.name }}</div>
                            <div class="text-xs text-gray">Mutual Projects: {{ friend.mutual_projects }}</div>
                        </div>
                    </div>
                    <button @click="startConversation(friend.id)" class="px-3 text-sm text-navy-blue rounded-full hover:bg-sky-blue hover:text-color-white hover:font-bold">
                        Start Conversation
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>