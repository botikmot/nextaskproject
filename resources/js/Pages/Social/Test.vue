<script setup>
import { ref, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import BulletList from '@tiptap/extension-bullet-list';
import ListItem from '@tiptap/extension-list-item';
import OrderedList from '@tiptap/extension-ordered-list';
import Mention from '@tiptap/extension-mention'; // Import Mention extension

const props = defineProps({
  task: Object,
});

const emit = defineEmits(['update:desc']);

const editor = useEditor({
  extensions: [
    StarterKit.configure({
      bulletList: false,
      listItem: false,
      orderedList: false,
    }),
    Underline,
    BulletList,
    ListItem,
    OrderedList,
    Mention.configure({
      HTMLAttributes: {
        class: 'mention',
      },
      suggestion: {
        char: '@', // Trigger mention list when @ is typed
        startOfLine: false, // Don't limit mention to the start of the line
        items(query) {
          // Return a filtered list of users based on the query typed
          console.log(query)
          return [
            { id: 1, label: 'John Doe' },
            { id: 2, label: 'Jane Doe' },
            { id: 3, label: 'Mary Smith' },
            { id: 4, label: 'James Lee' },
          ]
            .filter((item) => item.label.toLowerCase().includes(query.query.toLowerCase()))
            .map((item) => ({
              id: item.id,
              label: item.label,
            }));
        },
        render: () => {
          let component;
          const renderItems = (items) => {
            component = h(
              'div',
              { class: 'mention-list' },
              items.map((item) =>
                h(
                  'div',
                  {
                    class: 'mention-item',
                    onClick: () => {
                      // Insert mention into the editor
                      editor.value?.chain().focus().insertContent(`@${item.label}`).run();
                    },
                  },
                  item.label
                )
              )
            );
          };
          console.log(renderItems)
          return component;
        },
      },
    }),
  ],
  content: '',
});

watch(
  () => editor?.value?.getHTML(),
  (newContent) => {
    emit('update:desc', newContent);
  }
);

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy();
  }
});
</script>

<template>
  <div>
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

/* Styling for mentions */
.mention {
  background-color: #e0e0e0;
  border-radius: 5px;
  padding: 0 5px;
}

.mention-list {
  max-height: 150px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #ccc;
  position: absolute;
  z-index: 9999;
  width: 200px;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.mention-item {
  padding: 5px;
  cursor: pointer;
}

.mention-item:hover {
  background-color: #f5f5f5;
}
</style>
