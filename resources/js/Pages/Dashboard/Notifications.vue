<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  notif: Array,
});

const notifications = computed(() => {
  // Limit to the maximum of 2 notifications
  return (props.notif || []).slice(0, 2);
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

</script>
<template>
    <div>
        <h2 class="text-lg font-bold border-b border-dark-gray pb-2 text-navy-blue">Notifications</h2>
        <ul class="mt-2">
            <li v-for="notification in notifications" :key="notification.id" class="py-1 border-b border-dark-gray truncate overflow-hidden whitespace-nowrap">
                <div class="text-sm truncate overflow-hidden whitespace-nowrap" v-html="convertLinks(notification.data.message)"></div>
            </li>
        </ul>
    </div>
</template>