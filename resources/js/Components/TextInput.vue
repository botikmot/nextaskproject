<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

const props = defineProps({
    placeholder: {
        type: String,
        required: false,
        default: '',
    },
})

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-gray-300 shadow-sm focus:border-sky-blue focus:ring-sky-blue dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        v-model="model"
        :placeholder="placeholder"
        ref="input"
    />
</template>
