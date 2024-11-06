<script setup>
const props = defineProps({
    task: Object,
});

</script>


<template>
    <div class="bg-white p-3 mb-3 rounded shadow-lg border border-dark-gray cursor-pointer">
        <h3 class="text-md text-navy-blue font-semibold">{{ task.title }}</h3>
        <p class="text-sm text-gray">{{ task.description }}</p>
    </div>
</template>