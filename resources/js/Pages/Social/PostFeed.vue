<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from 'moment';
import { ref, computed, onMounted } from 'vue';
import MediaPost from './MediaPost.vue';
import UserImage from '@/Components/UserImage.vue';
import Dropdown from '@/Components/Dropdown.vue';
import TextAreaMention from './TextAreaMention.vue';

const emit = defineEmits(['postDeleted', 'postUpdated']);

const isEdit = ref(false)

const props = defineProps({
    post: Object,
});

const formatDate = (date) => {
    return moment(date).fromNow()
}

const openLink = (url) => {
    window.open(url, '_blank')
}

const form = useForm({
    id: props.post.id,
    content: props.post.content,
});

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

const confirmDelete = (id) => {
    form.id = id
    Swal.fire({
        title: 'Are you sure?',
        //text: id, //"Once deleted, this project cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/post/${form.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully deleted.', usePage().props.flash.message)
                    Swal.fire({
                        text: usePage().props.flash.message,
                        position: 'bottom-end',
                        backdrop: false,
                        timer: 2000,
                        showConfirmButton: false,
                        toast:true,
                        icon: usePage().props.flash.success ? "success" : "error",
                    });
                    if(usePage().props.flash.success){
                        emit('postDeleted', id);
                    }
                },
                onError: (error) => {
                    console.error('Error deleting post', usePage().props.flash.message)
                }
            })
        }
    });
}

const handlePost = (content) => {
    form.content = content
}

const updatePost = () => {
    console.log('post content-->', form.content)
    form.put(`/post/${form.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Successfully updated.', usePage().props.flash.message)
            Swal.fire({
                text: usePage().props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: usePage().props.flash.success ? "success" : "error",
            });

           isEdit.value = false
           if(usePage().props.flash.success){
                const updatedPost = { ...form }; // Clone the updated post data
                emit('postUpdated', updatedPost);
                form.reset()
           }
        },
        onError: (error) => {
            console.error('Error updating post', usePage().props.flash.message)
        }
    })
}

</script>
<template>
    <div>
        <div class="post-header flex justify-between">
            <div class="flex items-center">
                <UserImage class="w-10 h-10 rounded-full object-cover mr-2" :user="post.user" />
                <div class="post-author-info">
                    <h3 class="text-navy-blue font-semibold">{{ post.user.name }}</h3>
                    <p class="text-gray text-xs">{{ formatDate(post.created_at) }}</p>
                </div>
            </div>
            <div v-if="post.user.id == $page.props.auth.user.id" class="text-lg cursor-pointer">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                    </template>
                    <template #content>
                        <div
                            class="hover:bg-crystal-blue px-3 py-2 text-sm"
                            @click="confirmDelete(post.id)"
                        >
                            Delete
                        </div>
                        <div
                            class="hover:bg-crystal-blue px-3 py-2 text-sm"
                            @click="isEdit = true"
                        >
                            Edit
                        </div>
                    </template>
                </Dropdown>
            </div>
        </div>

        <p v-if="!isEdit" class="mt-2" v-html="convertLinks(post.content)"></p>
        <div v-else class="py-2">
            <TextAreaMention @content-changed="handlePost" :postContent="post.content"/>
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-sm text-navy-blue py-1 px-4 rounded" @click="isEdit = false">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen text-sm rounded-full py-1 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updatePost">Update</button>
            </div>
        </div>

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

<style>
.mention {
    font-weight: 800;
    color: #40a2e3 !important;
    border-radius: 0.4rem;
    box-decoration-break: clone;
    color: var(--purple);
    cursor: pointer;
}
</style>