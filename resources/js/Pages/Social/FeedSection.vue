<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import PostFeed from './PostFeed.vue';
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import axios from 'axios';
import CreatePost from './TextAreaMention.vue';
import EmojiPicker from "vue3-emoji-picker";
import 'vue3-emoji-picker/css'

const props = defineProps({
    posts: Object,
});

const fileInput = ref(null)
const selectedFiles = ref([])
const loading = ref(false)
const nextPageUrl = ref(props.posts.next_page_url || null)
const allPosts = ref([...props.posts.data]);
const feedSection = ref(null);
const createPostLoading = ref(false)
const createPostRef = ref(null);
const emojiPickerVisible = ref(false)


const form = useForm({
    content: '',
    media: []
});

const submitPost = () => {
    
    let content = form.content
    content = content.replace(/^<p>/, '') // Remove the first <p> tag at the beginning of the content
    content = content.replace(/<\/p>$/, '') // Remove the last </p> tag at the end of the content

    form.content = content

    const formData = new FormData()
    formData.append('content', form.content)
    selectedFiles.value.forEach(file => {
        formData.append('media[]', file)
    })
    createPostLoading.value = true
    form.post('/post', {
        data: formData,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            createPostLoading.value = false
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
            allPosts.value = props.posts.data;
            nextPageUrl.value = props.posts.next_page_url;
            createPostRef.value.clearContent();
        },
        onError: (error) => {
            console.error('Error assigning users', error)
            createPostLoading.value = false
        }
    })
}

const handlePost = (content) => {
    form.content = content
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

// Scroll handler
const handleScroll = async () => {
    if (!feedSection.value) return;

    const scrollableElement = feedSection.value;
    const bottomOfElement =
        scrollableElement.scrollTop + scrollableElement.clientHeight >=
        scrollableElement.scrollHeight - 200;

    if (bottomOfElement && nextPageUrl.value && !loading.value) {
        await loadMorePosts();
    }
};

// Load more posts
const loadMorePosts = async () => {
    loading.value = true;
    try {
        console.log('nextPageUrl.value-->>',nextPageUrl.value)
        const response = await axios.get(nextPageUrl.value, {
                headers: {
                    'X-Inertia': true, // Ensures Inertia returns JSON
                },
            });
        const { data } = response;
        console.log('posts-->>', data)
        // Append new posts and update the nextPageUrl
        allPosts.value.push(...data.props.posts.data);
        nextPageUrl.value = data.props.posts.next_page_url;
    } catch (error) {
        console.error("Error loading more posts:", error);
    } finally {
        loading.value = false;
    }
};

const removePost = (postId) => {
    allPosts.value = allPosts.value.filter((post) => post.id !== postId);
};

const updatePost = (updatedPost) => {
    allPosts.value = allPosts.value.map((post) =>
        post.id === updatedPost.id ? { ...post, ...updatedPost } : post
    );
};

const toggleEmojiPicker = () => {
    emojiPickerVisible.value = !emojiPickerVisible.value
}

const addEmoji = (emoji) => {
  console.log('emoji', emoji)
  const emojiUrl = `https://cdn.jsdelivr.net/npm/emoji-datasource-apple@6.0.1/img/apple/64/${emoji.r}.png`; // Use the emoji Unicode for the URL
  const emojiCode = emoji.i; // Use the emoji's native representation
  // Use Tiptap commands to insert the emoji at the current cursor position
  /* if (createPostRef.value && createPostRef.value.editor) {
    //createPostRef.value.editor.commands.insertContent(emojiCode);
        createPostRef.value.editor.commands.insertContent({
            type: "emoji",
            attrs: { src: emojiUrl, alt: emojiCode, class: "emoji-image", },
        });
  } */
  if (createPostRef.value && createPostRef.value.editor) {
    createPostRef.value.editor.commands.insertEmoji(emojiUrl, emojiCode);
  }
  emojiPickerVisible.value = false; // Hide the emoji picker
};

onMounted(() => {
    if (feedSection.value) {
        feedSection.value.addEventListener("scroll", handleScroll);
    }
});

onBeforeUnmount(() => {
    if (feedSection.value) {
        feedSection.value.removeEventListener("scroll", handleScroll);
    }
});
</script>

<template>
    <div ref="feedSection" class="feed-section w-full lg:w-2/4 overflow-y-auto h-screen pr-2">
        <div class="post-input bg-color-white p-6 rounded-lg shadow mb-6">
                <!-- User Input Section -->
                <div class="mention-container relative">
                    <CreatePost @content-changed="handlePost" ref="createPostRef" />
                </div>

                <!-- Media and Emoji Section -->
                <div class="flex items-center mt-4 justify-between">
                    <!-- Media Upload -->
                     <div class="flex items-center">
                        <div class="flex items-center">
                            <button
                                @click="triggerFileInput"
                                class="flex items-center gap-2 px-3 py-2 rounded transition"
                            >
                            <i class="fa-solid fa-photo-film text-2xl hover:text-sky-blue text-navy-blue"></i>
                                <!-- <span class="text-color-white text-sm">Add Media</span> -->
                            </button>
                            <input
                                type="file"
                                ref="fileInput"
                                @change="handleFileChange"
                                class="hidden"
                                multiple
                                accept="image/*, video/*"
                            />
                            <span
                                v-if="form.media.length"
                                class="text-navy-blue font-medium text-sm"
                                >
                                {{ form.media.length > 1
                                    ? form.media.length + ' attachments'
                                    : form.media.length + ' attachment' }}
                            </span>
                        </div>

                        <!-- Emoji Picker -->
                        <div class="pl-2">
                            <button
                                @click="toggleEmojiPicker"
                                class="flex items-center justify-center w-10 h-10 rounded-full transition"
                            >
                            <i class="fa-regular fa-face-smile text-2xl text-navy-blue hover:text-sky-blue"></i>
                            <!--  <span class="text-navy-blue text-sm">Add Emoji</span> -->
                            </button>
                            <div v-if="emojiPickerVisible" class="absolute mt-2 z-50">
                                <EmojiPicker @select="addEmoji" />
                            </div>
                        </div>
                    </div>
                    <!-- Post Button -->
                    <div class="">
                        <button
                            @click="submitPost"
                            :disabled="createPostLoading"
                            class="bg-gradient-to-r from-navy-blue to-sky-blue hover:scale-105 text-color-white py-2 px-6 rounded hover:font-bold hover:from-sky-blue hover:to-navy-blue transition disabled:opacity-50"
                        >
                            {{ createPostLoading ? "Posting..." : "Post" }}
                        </button>
                    </div>
                </div>

            </div>
        <!-- <div class="post-input bg-color-white p-4 rounded-lg shadow mb-6">

            <div class="mention-container relative">
                <CreatePost @content-changed="handlePost" ref="createPostRef"/>
            </div>

            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center">
                    <button @click="triggerFileInput" class="bg-sky-blue px-2 py-1 rounded hover:bg-crystal-blue transition">
                        <i class="fa-solid fa-photo-film text-xl hover:text-navy-blue text-color-white"></i>
                    </button>
                    <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple accept="image/*, video/*">
                    <span v-if="form.media.length" class="pl-3 text-navy-blue font-bold text-sm">{{ form.media.length > 1 ? form.media.length + ' attachments' : form.media.length + ' attachment' }}</span>
                </div>
                <button
                    @click="submitPost"
                    class="bg-sky-blue text-color-white py-2 px-4 rounded hover:font-bold hover:text-navy-blue hover:bg-crystal-blue transition"
                >
                    {{ createPostLoading ? 'Posting...' : 'Post' }}
                </button>
            </div>
        </div> -->

        <div class="post-feed">
            <!-- Conditional Rendering for Posts -->
            <div v-if="allPosts && allPosts.length > 0">
                <PostFeed
                    v-for="post in allPosts"
                    :key="post.id"
                    :post="post"
                    class="post bg-color-white p-4 rounded-lg shadow mb-3"
                    @postDeleted="removePost"
                    @postUpdated="updatePost"
                />
            </div>

            <!-- Loading Indicator -->
            <div v-if="loading" class="text-center my-6">
                <div role="status">
                    <svg aria-hidden="true" class="inline w-10 h-10 text-dark-gray animate-spin dark:text-gray-600 fill-sky-blue" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="pl-3">Loading...</span>
                </div>
            </div>

            <!-- Empty State Placeholder -->
            <div v-if="allPosts.length == 0" class="empty-state text-center p-6 bg-color-light rounded-lg">
                <h3 class="text-xl font-semibold text-color-dark mb-2">No posts yet</h3>
                <p class="text-color-gray">
                    Start by sharing your thoughts or connecting with your network.
                </p>
            </div>
        </div>
    </div>
</template>

<style>
.mention-container {
  position: relative;
}

.mention-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  background-color: white;
  border: 1px solid #ccc;
  max-height: 150px;
  overflow-y: auto;
  width: 100%;
  z-index: 10;
}

.mention-dropdown ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}

.mention-dropdown li {
  padding: 8px;
  cursor: pointer;
}

.mention-dropdown li:hover {
  background-color: #f0f0f0;
}

img.emoji-image {
  width: 20px; /* Adjust the size */
  height: 20px; /* Ensure consistent height */
  object-fit: contain; /* Keeps the aspect ratio */
  vertical-align: middle; /* Aligns the emoji with text */
}
</style>