<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import PostFeed from './PostFeed.vue';
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import axios from 'axios';

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
const showDropdown = ref(false); 
const matchingUsers = ref([]); // List of users matching the input after '@'
const editor = ref(null);

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
        },
        onError: (error) => {
            console.error('Error assigning users', error)
            createPostLoading.value = false
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

// Detect the '@' and type after it
const onInput = async () => {
    const inputText = form.content;
    const lastAtSymbol = inputText.lastIndexOf('@');

    // Check if '@' exists and is followed by a valid character
    if (lastAtSymbol !== -1 && lastAtSymbol < inputText.length - 1) {
        const query = inputText.slice(lastAtSymbol + 1).trim(); // Get the text after '@'
        const firstChar = query.charAt(0); // Get the first character after '@'

        // Ensure the first character is a letter (not a space or invalid character)
        if (/^[a-zA-Z]$/.test(firstChar)) {
            console.log('search user', query)
            await fetchUserSuggestions(query); // Fetch users from backend as soon as a valid character is typed
        } else {
            showDropdown.value = false; // Hide dropdown for invalid characters
        }
    } else {
        showDropdown.value = false; // Hide dropdown if '@' is not followed by anything
    }
};

// Fetch user suggestions from backend
const fetchUserSuggestions = async (query) => {
    try {
        const response = await axios.get(`/users/search`, {
            params: { query }
        });
        matchingUsers.value = response.data; // Update the list of matching users
        showDropdown.value = matchingUsers.value.length > 0; // Show dropdown if there are users to display
    } catch (error) {
        console.error('Error fetching user suggestions', error);
    }
};

// Insert the selected username into the content

// Insert Mention on User Click
const insertMention = (user) => {
    const inputText = form.content;
    const lastAtSymbol = inputText.lastIndexOf('@');

    if (lastAtSymbol !== -1) {
        // Insert the mention with a hidden structure
        const newText = inputText.slice(0, lastAtSymbol) + `${user.email}`;
            //`<span class="mention" data-id="${user.id}" style="color: skyblue;">@${user.email}</span> `; // Add mention with styled email and hidden ID
        form.content = newText; // Update the textarea content
    }

    showDropdown.value = false; // Hide dropdown after selection

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
        <div class="post-input bg-color-white p-4 rounded-lg shadow mb-6">

            <div class="mention-container relative">
                <textarea
                    v-model="form.content"
                    placeholder="Share something with your network..."
                    class="w-full p-4 border rounded-md h-24"
                    @input="onInput"
                ></textarea>
                <!-- <div ref="editorRef" contenteditable="true" class="tiptap-editor border rounded-md p-4"></div> -->
                <!-- Mention dropdown -->
                <div v-if="showDropdown && matchingUsers.length > 0" class="mention-dropdown">
                    <ul>
                        <li v-for="user in matchingUsers" :key="user.id" @click="insertMention(user)">
                            @{{ user.name }} - ({{ user.email }})
                        </li>
                    </ul>
                </div>

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
        </div>

        <div class="post-feed">
            <!-- Conditional Rendering for Posts -->
            <div v-if="allPosts && allPosts.length > 0">
                <PostFeed
                    v-for="post in allPosts"
                    :key="post.id"
                    :post="post"
                    class="post bg-color-white p-4 rounded-lg shadow mb-3"
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

<style scoped>
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
</style>