
<script setup>
import { ref, computed, onMounted } from 'vue';
import UserImage from '@/Components/UserImage.vue';
import moment from 'moment';

const socialHighlights = ref([])

const fetchSocialHighlights = async () => {
    try {
        const response = await axios.get('/social/highlights'); // Adjust the endpoint URL
        console.log(response.data)
        socialHighlights.value = response.data;
    } catch (error) {
        console.error('Error fetching social highlights:', error);
    }
}

const formatDate = (date) => {
    return moment(date).fromNow()
}

const convertLinks = (text) => {
    // Convert links to clickable anchor tags

    text = text.replace(/<\/?p>/g, ''); // Remove all <p> and </p> tags

    text = text.replace(/[\r\n]+/g, ' '); // Replace newlines or multiple spaces with a single space

    const pattern = /https?:\/\/\S+/g;
    const replacement = '<a class="text-sky-blue" href="$&" target="_blank">$&</a>';
    text = text.replace(pattern, replacement);

    return text;
}

onMounted(() => {
    fetchSocialHighlights()
});

</script>

<template>
    <div class="">
        <h2 class="text-lg font-bold border-b border-dark-gray pb-2 text-navy-blue">Social Highlights</h2>
        <ul v-if="socialHighlights.length > 0" class="mt-2 list-none mb-6">
            <li v-for="post in socialHighlights" :key="post.id" class="py-1 border-b border-dark-gray">
                <div class="flex items-center">
                    <UserImage class="w-8 h-8 rounded-full object-cover mr-2" :user="post.author" />
                    <div class="post-author-info truncate overflow-hidden whitespace-nowrap">
                        <h3 class="text-navy-blue text-sm font-semibold">{{ post.author.name }}</h3>
                        <div class="text-sm truncate overflow-hidden whitespace-nowrap" v-html="convertLinks(post.content)"></div>
                    </div>
                </div>
            </li>
        </ul>
        <div v-else class="text-center text-gray-500 mt-8">
            <p>No social highlights this week.</p>
        </div>
        <a
            class="absolute bottom-4 cursor-pointer px-6 py-3 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
            :href="route('social')"
            >
            View All Posts
        </a>
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

.line-clamp {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-clamp: 2; /* Number of lines to display */
}
</style>