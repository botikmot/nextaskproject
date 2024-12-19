<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from 'moment';
import { ref, computed, onMounted } from 'vue';
import MediaPost from './MediaPost.vue';

const props = defineProps({
    post: Object,
});

const formatDate = (date) => {
    return moment(date).fromNow()
}

const openLink = (url) => {
    window.open(url, '_blank')
}

const convertLinks = (text) => {
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
        <div class="post-header flex items-center">
            <img :src="'/' + post.user.profile_image" alt="author" class="w-10 h-10 object-cover rounded-full mr-4" />
            <div class="post-author-info">
                <h3 class="text-navy-blue font-semibold">{{ post.user.name }}</h3>
                <p class="text-gray text-xs">{{ formatDate(post.created_at) }}</p>
            </div>
        </div>

        <p class="mt-2" v-html="convertLinks(post.content)"></p>

        <div class="mt-3 relative cursor-pointer" v-if="post.link_preview">
            <img @click="openLink(post.link_preview.url)" :src="post.link_preview.image" class="w-full object-cover max-h-96"/>
            <div class="absolute bottom-5 link-preview-desc">
                <div class="font-semibold text-xl pl-3 text-dark-gray">{{ post.link_preview.title }}</div>
                <div class="text-sm pl-3 text-dark-gray">{{ post.link_preview.description }}</div>
            </div>
        </div>

        <div v-if="post.media.length" class="post-media mt-3">
            <MediaPost :post="post"/>
        </div>

        <div class="post-actions mt-4 text-sm flex gap-4">
            <button @click="likePost(post.id)" class="text-navy-blue hover:text-sky-blue">Like</button>
            <button @click="commentOnPost(post.id)" class="text-navy-blue hover:text-sky-blue">Comment</button>
            <button @click="sharePost(post.id)" class="text-navy-blue hover:text-sky-blue">Share</button>
        </div>
    </div>
</template>