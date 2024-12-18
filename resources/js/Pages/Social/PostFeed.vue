<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from 'moment';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    post: Object,
});

const formatDate = (date) => {
    return moment(date).fromNow()
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
        <p class="mt-2">{{ post.content }}</p>
        <div class="post-actions mt-4 text-sm flex gap-4">
            <button @click="likePost(post.id)" class="text-navy-blue hover:text-sky-blue">Like</button>
            <button @click="commentOnPost(post.id)" class="text-navy-blue hover:text-sky-blue">Comment</button>
            <button @click="sharePost(post.id)" class="text-navy-blue hover:text-sky-blue">Share</button>
        </div>
    </div>
</template>