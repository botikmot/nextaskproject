<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import PostFeed from './PostFeed.vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    posts: Object,
});

const fileInput = ref(null)
const selectedFiles = ref([])

const form = useForm({
    content: '',
    media: []
});

const submitPost = () => {
    
    const formData = new FormData()
    formData.append('content', form.content)
    selectedFiles.value.forEach(file => {
        formData.append('media[]', file)
    })

    form.post('/post', {
        data: formData,
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

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleFileChange = (event) => {
    selectedFiles.value = Array.from(event.target.files)
    form.media = selectedFiles.value

    /* previews.value = selectedFiles.value.map(file => {
        return {
            url: URL.createObjectURL(file),
            type: file.type
        }
    }) */
}

console.log('posts', props.posts)

</script>

<template>
    <div>
        <div class="post-input bg-color-white p-4 rounded-lg shadow mb-6">
            <textarea v-model="form.content" placeholder="Share something with your network..." class="w-full p-4 border rounded-md h-24"></textarea>
            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center">
                    <button @click="triggerFileInput" class="bg-sky-blue px-2 py-1 rounded hover:bg-crystal-blue transition">
                        <i class="fa-solid fa-photo-film text-xl hover:text-navy-blue text-color-white"></i>
                    </button>
                    <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple accept="image/*, video/*">
                    <span v-if="form.media.length" class="pl-3 text-navy-blue font-bold text-sm">{{ form.media.length > 1 ? form.media.length + ' attachments' : form.media.length + ' attachment' }}</span>
                </div>
                <button @click="submitPost" class="bg-sky-blue text-color-white py-2 px-4 rounded hover:font-bold hover:text-navy-blue hover:bg-crystal-blue transition">Post</button>
            </div>
        </div>

        <div class="post-feed">
            <!-- Conditional Rendering for Posts -->
            <div v-if="posts.data && posts.data.length > 0">
                <PostFeed
                    v-for="post in posts.data"
                    :key="post.id"
                    :post="post"
                    class="post bg-color-white p-4 rounded-lg shadow mb-3"
                />
            </div>

            <!-- Empty State Placeholder -->
            <div v-else class="empty-state text-center p-6 bg-color-light rounded-lg">
                <h3 class="text-xl font-semibold text-color-dark mb-2">No posts yet</h3>
                <p class="text-color-gray">
                    Start by sharing your thoughts or connecting with your network.
                </p>
            </div>
        </div>
    </div>
</template>