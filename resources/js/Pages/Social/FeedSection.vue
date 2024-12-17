<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import PostFeed from './PostFeed.vue';

const props = defineProps({
    posts: Array,
});

const form = useForm({
    content: '',
    media: []
});

const submitPost = () => {
    console.log('post', form.content)
    form.post('/post', {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
        },
        onError: (error) => {
            console.error('Error assigning users', error)
        }
    })
}

console.log('posts', props.posts)

</script>

<template>
    <div>
        <div class="post-input bg-color-white p-4 rounded-lg shadow mb-6">
            <textarea v-model="form.content" placeholder="Share something with your network..." class="w-full p-4 border rounded-md h-24"></textarea>
            <button @click="submitPost" class="bg-sky-blue text-color-white py-2 px-4 mt-4 rounded hover:bg-crystal-blue transition">Post</button>
        </div>

        <div class="post-feed">
            <PostFeed
                v-for="post in posts"
                :key="post.id"
                :post="post"
                class="post bg-color-white p-4 rounded-lg shadow mb-3"
            />
        </div>
    </div>
</template>