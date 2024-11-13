<template>
    <img 
        v-if="profileImageSrc" 
        :src="profileImageSrc" 
        alt="User Photo" 
    />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: Object,
});

const profileImageSrc = computed(() => {
    const user = props.user;
    if (!user || !user.profile_image) return ''; // Return empty string if data isn't available
    const imageUrl = user.profile_image;
    // Check if the image URL starts with 'http'
    return imageUrl.startsWith('http') ? imageUrl : '/' + imageUrl;
});
</script>