<template>
    <div class="flex-1">
        <!-- If no contact is selected -->
        <div v-if="!selectedContact" class="flex items-center justify-center flex-col h-full text-center text-gray-500">
                <p>Select a conversation to start chatting!</p>
            </div>
            
            <!-- If a contact is selected, show the conversation -->
            <div v-else>
                <div class="flex items-center mb-4 border-b pb-2">
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
            </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            selectedContact: null,
            messages: [
                /* { id: 1, fromUser: false, text: "Hello! Are we still on for tomorrow?", time: "10:05 AM" },
                { id: 2, fromUser: true, text: "Yes, looking forward to it!", time: "10:06 AM" }, */
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
    }
};
</script>

<style scoped>
/* Custom styles for chat bubbles and layout */
</style>