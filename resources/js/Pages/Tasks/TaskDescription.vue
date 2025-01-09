<script setup>
import { ref, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import BulletList from '@tiptap/extension-bullet-list'; // Import BulletList
import ListItem from '@tiptap/extension-list-item'; // Import ListItem
import OrderedList from '@tiptap/extension-ordered-list'; // Import OrderedList

const props = defineProps({
    task: Object,
});

const emit = defineEmits(['update:desc']);

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            bulletList: false, // Disable bulletList in StarterKit
            listItem: false, // Disable listItem in StarterKit
            orderedList: false, // Disable orderedList in StarterKit
        }),
        //StarterKit,
        Underline,
        BulletList, // Add BulletList extension
        ListItem,   // Add ListItem extension
        OrderedList, // Add OrderedList extension
    ],
    content: props.task.description,
});
// Functions for toolbar actions
const toggleBold = () => editor.value.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value.chain().focus().toggleItalic().run();
const toggleUnderline = () => editor.value.chain().focus().toggleUnderline().run();
const toggleBulletList = () => editor.value.chain().focus().toggleBulletList().run();  // Bullet List function
const toggleOrderedList = () => editor.value.chain().focus().toggleOrderedList().run();  // Ordered List function

watch(
    () => editor?.value?.getHTML(),
    (newContent) => {
        emit('update:desc', newContent); // Emit the new content
    }
);


onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
});

</script>

<template>
    <div class="bg-color-white rounded-lg">
    <!-- Toolbar with buttons for bold, italic, and underline -->
    <div class="toolbar flex justify-end mb-3">
        <button @click="toggleBold" :class="{ 'is-active': editor?.isActive('bold') }">
            <i class="fa-solid fa-bold"></i>
        </button>
        <button @click="toggleItalic" :class="{ 'is-active': editor?.isActive('italic') }">
            <i class="fa-solid fa-italic"></i>
        </button>
        <button @click="toggleUnderline" :class="{ 'is-active': editor?.isActive('underline') }">
            <i class="fa-solid fa-underline"></i>
        </button>
        <button @click="toggleBulletList" :class="{ 'is-active': editor?.isActive('bulletList') }">
            <i class="fa-solid fa-list"></i>
        </button>
        <button @click="toggleOrderedList" :class="{ 'is-active': editor?.isActive('orderedList') }">
            <i class="fa-solid fa-list-ol"></i>
        </button>
    </div>

    <!-- Editor Content -->
    <EditorContent v-if="editor" :editor="editor" class="pb-2 pl-2" />
  </div>
</template>

<style scoped>
.toolbar button {
  padding: 5px 10px;
  cursor: pointer;
  border: 1px solid #ddd;
  background-color: #f7f7f7;
}

.toolbar button.is-active {
  background-color: #ddd;
}

</style>