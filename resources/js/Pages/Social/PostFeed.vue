<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from 'moment';
import { ref, computed, onMounted } from 'vue';
import MediaPost from './MediaPost.vue';
import UserImage from '@/Components/UserImage.vue';
import Dropdown from '@/Components/Dropdown.vue';
import TextAreaMention from './TextAreaMention.vue';
import axios from 'axios';

const emit = defineEmits(['postDeleted', 'postUpdated']);

const isEdit = ref(false)
const newComment = ref("");
const showCommentInput = ref(false);
const editingCommentId = ref(null);

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
    comment: '',
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

const toggleCommentInput = () => {
    showCommentInput.value = !showCommentInput.value;
};

const confirmDeleteComment = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/post/comment/${id}`, {
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
                        props.post.comments = props.post.comments.filter(comment => comment.id !== id);
                    }
                },
                onError: (error) => {
                    console.error('Error deleting comment', usePage().props.flash.message)
                }
            })
        }
    });
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

const handleComment = (comment) => {
    form.comment = comment
}

const submitComment = async (id) => {
    console.log('post id-->', id)
    console.log('comment-->', form.comment)
    try {
        // Send the comment to the backend
        const response = await axios.post(`/post/comment/${form.id}`, form);

        // Push the new comment to the local post's comments array
        props.post.comments.push(response.data.comment);

        // Optionally clear the comment input and hide the textarea
        newComment.value = ""; // Clear the input field
        showCommentInput.value = false; // Optionally hide the comment input field
        form.reset()

    } catch (error) {
        console.error("Error submitting comment:", error);
    }

}

const updateComment = async (id) => {
    console.log('comment id->', id)
    try {
        // Send update request to the backend
        const response = await axios.put(`/post/comment/${id}`, form);

        // Update the comment in the post's comments array
        const updatedComment = response.data.comment;
        console.log('updated comment', updatedComment)
        const commentIndex = props.post.comments.findIndex(
            (comment) => comment.id === id
        );
        if (commentIndex !== -1) {
            props.post.comments[commentIndex] = updatedComment;
        }

        // Reset editing state
        form.reset()
        editingCommentId.value = null

    } catch (error) {
        console.error("Error updating comment:", error);
    }
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

       <!--  <div class="post-actions mt-4 text-sm flex gap-4">
            <button @click="likePost(post.id)" class="text-navy-blue hover:text-sky-blue">Like</button>
            <button @click="commentOnPost(post.id)" class="text-navy-blue hover:text-sky-blue">Comment</button>
            <button @click="sharePost(post.id)" class="text-navy-blue hover:text-sky-blue">Share</button>
        </div> -->
        <hr class="text-dark-gray my-3"/>
        <div v-if="showCommentInput" class="mt-3">
            <TextAreaMention @content-changed="handleComment" :placeholder="'Write a comment...'"/>
            <div class="flex justify-end">
                <button
                    class="mt-2 px-3 py-1 hover:bg-crystal-blue text-sm rounded mr-3 hover:text-navy-blue"
                    @click="showCommentInput = false"
                >
                    Cancel
                </button>
                <button
                    class="mt-2 bg-sky-blue text-color-white text-sm px-3 py-1 rounded hover:bg-crystal-blue hover:text-navy-blue"
                    @click="submitComment(post.id)"
                >
                    Submit
                </button>
            </div>
        </div>

        <div v-if="post.comments.length" class="comments mt-3">
            <div v-for="comment in post.comments" :key="comment.id" class="mb-2">
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <UserImage class="w-6 h-6 rounded-full object-cover" :user="comment.user" />
                        <div class="pl-2 text-sm font-bold text-navy-blue"> {{ comment.user.name }} </div>
                        <div class="pl-2 text-xs text-gray"> {{ formatDate(comment.created_at) }} </div>
                    </div>
                    <div v-if="comment.user.id == $page.props.auth.user.id" class="cursor-pointer">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <i class="fa-solid fa-ellipsis"></i>
                            </template>
                            <template #content>
                                <div
                                    class="hover:bg-crystal-blue px-3 py-2 text-sm"
                                    @click="confirmDeleteComment(comment.id)"
                                >
                                    Delete
                                </div>
                                <div
                                    class="hover:bg-crystal-blue px-3 py-2 text-sm"
                                    @click="editingCommentId = comment.id"
                                >
                                    Edit
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex">
                    <div v-if="editingCommentId !== comment.id" class="relative comment-area mt-2">
                        <!-- Bubble style for the comment -->
                        <div
                            class="px-3 py-2 text-sm bg-dark-gray rounded-b-xl rounded-r-xl text-navy-blue shadow-md"
                            v-html="convertLinks(comment.content)"
                        ></div>
                    </div>
                    <!-- <div v-if="editingCommentId !== comment.id" class="mt-2 px-2 py-1 text-sm bg-dark-gray rounded-lg" v-html="convertLinks(comment.content)"></div> -->
                    <div v-else class="py-2 w-full">
                        <TextAreaMention class="my-2 w-full" @content-changed="handleComment" :postContent="comment.content"/>
                        <div class="flex justify-end">
                            <button type="button" class="mr-2 bg-gray-300 text-sm text-navy-blue py-1 px-4 rounded" @click="editingCommentId = null">Cancel</button>
                            <button type="submit" class="bg-sky-blue text-linen text-sm rounded-full py-1 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updateComment(comment.id)">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="post-actions mt-4 text-sm flex gap-4">
            <button @click="likePost(post.id)" class="text-navy-blue hover:text-sky-blue">
                {{ post.user_liked ? "Unlike" : "Like" }}
            </button>
            <span>{{ post.likes_count }} {{ post.likes_count === 1 ? "Like" : "Likes" }}</span>
            <button @click="toggleCommentInput" class="text-navy-blue hover:text-sky-blue">
                Comment
            </button>
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

.comment-area::before {
    content: '';
    position: absolute;
    top: -10px; /* Position the triangle above the bubble */
    left: 0px; /* Align the triangle with the profile image */
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 2px 10px 8px 0px; /* Triangle shape for pointing upward */
    border-color: transparent transparent #E2E8F0 transparent; /* Match the bubble's background color */
}
</style>