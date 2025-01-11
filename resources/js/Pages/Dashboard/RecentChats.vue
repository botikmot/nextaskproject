<script setup>
import { ref, computed, onMounted } from 'vue';
import UserImage from '@/Components/UserImage.vue';

const recentChats = ref([])

const fetchRecentChats = async () => {
    try {
        const response = await axios.get('/conversations/recent-chats'); // Adjust the endpoint URL
        console.log('recentChats', response.data)
        recentChats.value = response.data;
    } catch (error) {
        console.error('Error fetching social highlights:', error);
    }
}
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

onMounted(() => {
    fetchRecentChats()
});
</script>

<template>
    <div>
        <h2 class="text-lg font-bold border-b border-dark-gray pb-2 text-navy-blue">Recent Chats</h2>
        <ul v-if="recentChats.length > 0" class="mt-2 list-none mb-6">
            <li v-for="chat in recentChats" :key="chat.id" class="py-1 border-b border-dark-gray">
                <div class="flex items-center">
                    <UserImage class="w-8 h-8 rounded-full object-cover mr-2" :user="chat.user" />
                    <div class="post-author-info truncate overflow-hidden whitespace-nowrap">
                        <h3 class="text-navy-blue text-sm font-semibold">{{ chat.conversation.name ? chat.conversation.name : chat.user.name }}</h3>
                        <div class="text-sm truncate overflow-hidden whitespace-nowrap" v-html="convertLinks(chat.text)"></div>
                    </div>
                </div>
            </li>
        </ul>
        <div v-else class="text-left text-gray-500 mt-8">
            <p>No recent chats available.</p>
        </div>        
        <a
            class="absolute bottom-5 right-5 cursor-pointer px-6 py-3 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
            :href="route('messages')"
        >
        Open Messages
        </a>
    </div>
</template>