<template>
    <img :src="src" class="media-element" loading="lazy" />
</template>

<script setup>

const props = defineProps({
    src: {
        type: String,
        required: true
    },
})
</script>

<style scoped>
.media-element {
  /* width: 100%;
  height: 100%; */
  object-fit: cover;
}
</style>