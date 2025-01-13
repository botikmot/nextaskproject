
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

/* const convertLinks = (text) => {
    // Convert links to clickable anchor tags

    text = text.replace(/<\/?p>/g, ''); // Remove all <p> and </p> tags

    text = text.replace(/[\r\n]+/g, ' '); // Replace newlines or multiple spaces with a single space

    const pattern = /https?:\/\/\S+/g;
    const replacement = '<a class="text-sky-blue" href="$&" target="_blank">$&</a>';
    text = text.replace(pattern, replacement);

    return text;
} */

const convertLinks = (html) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');

  // Traverse child nodes to replace text without affecting attributes
  const traverseNodes = (node) => {
    if (node.nodeType === Node.TEXT_NODE) {
      // Convert links to clickable anchor tags
      const linkPattern = /https?:\/\/\S+/g;
      const hashtagPattern = /#(\w+)/g;

      let text = node.textContent;

      text = text.replace(
        linkPattern,
        '<a class="text-sky-blue" href="$&" target="_blank">$&</a>'
      );

      text = text.replace(
        hashtagPattern,
        '<a class="text-navy-blue text-sm font-bold hover:underline" href="/hashtags/$1">#$1</a>'
      );

      // Replace the text node with the new HTML
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = text;

      // Append each child node from tempDiv to the parent of the current node
      while (tempDiv.firstChild) {
        node.parentNode.insertBefore(tempDiv.firstChild, node);
      }

      // Remove the original text node
      node.parentNode.removeChild(node);
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      // Recursively process child nodes
      node.childNodes.forEach((child) => traverseNodes(child));
    }
  };

  traverseNodes(doc.body);
  return doc.body.innerHTML;
};

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
        <div v-else class="text-left text-gray-500 mt-8">
            <p>No social highlights this week.</p>
        </div>
        <a
            v-if="socialHighlights.length > 0"
            class="absolute bottom-5 right-5 hover:scale-105 cursor-pointer px-6 py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
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

img.emoji-image {
  width: 20px; /* Adjust the size */
  height: 20px; /* Ensure consistent height */
  object-fit: contain; /* Keeps the aspect ratio */
  vertical-align: middle; /* Aligns the emoji with text */
  display: inline; /* Ensure inline behavior */
}
</style>