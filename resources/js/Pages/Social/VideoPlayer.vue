<template>
    <video controls :src="src" class="media-element"></video>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    src: {
        type: String,
        required: true
    },
})

const video = ref(null);

onMounted(() => {
    if (video.value) {
        const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
            video.value.src = props.src;
            observer.unobserve(entry.target);
            }
        });
        });

        observer.observe(video.value);
    }
});

</script>

<style scoped>
.media-element {
  width: 100%;
  max-height: 400px;
  object-fit: cover;
}
</style>