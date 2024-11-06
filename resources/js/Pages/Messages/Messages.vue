<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout pageTitle="Messages">
        <!-- Chat Page Layout -->
        <div class="flex h-screen w-full space-x-4">
            <!-- Sidebar for Conversations -->
            <aside class="w-1/4 bg-gray-100 p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Conversations</h2>
                
                <!-- Sample Conversation List -->
                <ul class="space-y-2">
                    <li v-for="contact in contacts" :key="contact.id" class="flex items-center p-2 bg-white rounded-lg shadow cursor-pointer hover:bg-blue-100">
                        <img :src="contact.profileImage" alt="Profile" class="w-10 h-10 rounded-full mr-3">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ contact.name }}</span>
                            <span class="text-sm text-gray-500">{{ contact.lastMessage }}</span>
                        </div>
                    </li>
                </ul>
            </aside>

            <!-- Main Chat Area -->
            <section class="flex-1 bg-white p-4 rounded-lg shadow flex flex-col">
                <div v-if="selectedContact" class="flex items-center mb-4 border-b pb-2">
                    <img :src="selectedContact.profileImage" alt="Profile" class="w-10 h-10 rounded-full mr-3">
                    <h2 class="text-lg font-semibold">{{ selectedContact.name }}</h2>
                </div>

                <!-- Chat Messages -->
                <div class="flex-1 overflow-y-auto space-y-4">
                    <div v-for="message in messages" :key="message.id" :class="{'self-end bg-blue-100': message.fromUser, 'self-start bg-gray-100': !message.fromUser}" class="p-2 rounded-lg max-w-xs shadow">
                        <p>{{ message.text }}</p>
                        <span class="text-xs text-gray-500">{{ message.time }}</span>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="mt-4 flex items-center">
                    <input
                        type="text"
                        v-model="newMessage"
                        placeholder="Type a message..."
                        class="flex-1 border p-2 rounded-md mr-2"
                    />
                    <button @click="sendMessage" class="bg-blue-500 text-white py-2 px-4 rounded shadow hover:bg-blue-600">Send</button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    data() {
        return {
            contacts: [
                { id: 1, name: "John Doe", lastMessage: "Hey! How's it going?", profileImage: "https://via.placeholder.com/40" },
                { id: 2, name: "Jane Smith", lastMessage: "Can we meet tomorrow?", profileImage: "https://via.placeholder.com/40" },
                { id: 3, name: "Michael Brown", lastMessage: "Looking forward to the meeting!", profileImage: "https://via.placeholder.com/40" },
            ],
            selectedContact: null,
            messages: [
                { id: 1, fromUser: false, text: "Hello! Are we still on for tomorrow?", time: "10:05 AM" },
                { id: 2, fromUser: true, text: "Yes, looking forward to it!", time: "10:06 AM" },
            ],
            newMessage: "",
        };
    },
    methods: {
        sendMessage() {
            if (this.newMessage.trim()) {
                this.messages.push({
                    id: Date.now(),
                    fromUser: true,
                    text: this.newMessage,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
                this.newMessage = "";
            }
        }
    },
    mounted() {
        // Set the selected contact to the first one in the list on load
        if (this.contacts.length > 0) {
            this.selectedContact = this.contacts[0];
        }
    }
};
</script>

<style scoped>
/* Custom styles for chat bubbles and layout */
</style>
