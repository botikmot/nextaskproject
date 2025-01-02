<script setup>
import axios from 'axios';

const props = defineProps({
    notifications: Array,
});

const convertLinks = (text) => {
    text = text.replace(/^<p>(.*?)<\/p>$/s, '$1');
    // Convert newlines to <br> for line breaks
    text = text.replace(/\n/g, '<br>');

    // Convert **bold** or __bold__ to <strong> tags
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');  // Bold using **
    text = text.replace(/__(.*?)__/g, '<strong>$1</strong>');      // Bold using __

    // Convert *italic* or _(italic)_ to <em> tags
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');  // Italic using *
    text = text.replace(/_(.*?)_/g, '<em>$1</em>');    // Italic using _

    // Convert links to clickable anchor tags
    const pattern = /https?:\/\/\S+/g;
    const replacement = '<a class="text-sky-blue" href="$&" target="_blank">$&</a>';
    text = text.replace(pattern, replacement);

    return text;
}

const readNotification = async (id) => {
    try {
        const response = await axios.post(`/notifications/read/${id}`);
        console.log('read', response.data)
      } catch (error) {
        console.error('Error:', error);
      }
}

</script>

<template>
    <div class="p-4">
        <div v-if="notifications.length == 0">
            No notifications
        </div>
        <div v-else>
            <div
                v-for="notif in notifications"
                :key="notif.id"
                class="py-2 border-b flex items-center border-dark-gray cursor-pointer"
                @click="readNotification(notif.id)"
            >   
                <img v-if="notif.data.user_profile" :src="'/' + notif.data.user_profile" alt="Profile" class="w-5 h-5 rounded-full object-cover border-1 border-color-white" />
                <div class="text-sm pl-1 font-normal truncate" v-if="notif.data" v-html="convertLinks(notif.data.message)"></div>
            </div>
        </div>
    </div>
</template>