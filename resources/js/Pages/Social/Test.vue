<script setup>
import { ref, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import BulletList from '@tiptap/extension-bullet-list';
import ListItem from '@tiptap/extension-list-item';
import OrderedList from '@tiptap/extension-ordered-list';
import Mention from '@tiptap/extension-mention';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css'; // Make sure to import the tippy.js CSS

const props = defineProps({
  task: Object,
});

const emit = defineEmits(['update:desc']);

// Initialize the editor
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
        char: '@',
        items: (data) => {
          return [
            { id: 1, label: 'John Doe' },
            { id: 2, label: 'Jane Doe' },
            { id: 3, label: 'Mary Smith' },
            { id: 4, label: 'James Lee' },
          ]
            .filter((item) => item.label.toLowerCase().includes(data.query.toLowerCase()))
            .map((item) => ({ id: item.id, label: item.label }));
        },
        render: () => {
          let popup;
          let suggestionElement;

          return {
            onStart: (props) => {
              // Find the EditorContent div
              const editorContentElement = props.editor.view.dom.closest('.editor-content');
              if (!editorContentElement) return;

              // Create the dropdown element
              suggestionElement = document.createElement('div');
              suggestionElement.classList.add('mention-suggestion');
              suggestionElement.innerHTML = props.items
                .map(
                  (item, index) =>
                    `<div class="mention-suggestion__item ${
                      index === props.index ? 'mention-suggestion__item--active' : ''
                    }">${item.label}</div>`
                )
                .join('');

              // Create the tippy.js popup and attach it to the EditorContent div
              popup = tippy(editorContentElement, {
                content: suggestionElement,
                interactive: true,
                trigger: 'manual',
                placement: 'bottom-start',
                appendTo: editorContentElement, // Attach to EditorContent
                arrow: false,
                showOnCreate: true,
              });

              // Position the dropdown near the cursor
              const cursorRect = props.editor.view.coordsAtPos(props.editor.state.selection.from);
              popup[0]?.setProps({
                getReferenceClientRect: () => cursorRect,
              });

              // Add event listener to make dropdown clickable
              suggestionElement.addEventListener('click', (e) => {
                const clickedItem = e.target;
                if (clickedItem && clickedItem.classList.contains('mention-suggestion__item')) {
                  const selectedItem = props.items[Array.from(suggestionElement.children).indexOf(clickedItem)];
                  props.command(selectedItem);
                  popup[0]?.hide();
                }
              });
            },
            onUpdate: (props) => {
              if (suggestionElement) {
                suggestionElement.innerHTML = props.items
                  .map(
                    (item, index) =>
                      `<div class="mention-suggestion__item ${
                        index === props.index ? 'mention-suggestion__item--active' : ''
                      }">${item.label}</div>`
                  )
                  .join('');
              }
            },
            onKeyDown: (props) => {
              if (props.event.key === 'Enter') {
                props.command(props.items[props.index]);
                popup[0]?.hide();
                return true;
              }
              return false;
            },
            onExit: () => {
              popup?.[0]?.destroy();
            },
          };
        },
      },
    }),
  ],
  content: props.task?.desc || '<p>Start typing...</p>',
  onUpdate: ({ editor }) => {
    emit('update:desc', editor.getHTML());
  },
});

// Watch editor content changes safely
watch(
  () => editor?.getHTML?.(),
  (newContent) => {
    if (newContent) {
      emit('update:desc', newContent);
    }
  }
);

// Clean up the editor on component unmount
onBeforeUnmount(() => {
  if (editor) {
    editor.destroy();
  }
});

// Toolbar actions
const toggleBold = () => editor?.chain().focus().toggleBold().run();
const toggleItalic = () => editor?.chain().focus().toggleItalic().run();
const toggleUnderline = () => editor?.chain().focus().toggleUnderline().run();
const toggleBulletList = () => editor?.chain().focus().toggleBulletList().run();
const toggleOrderedList = () => editor?.chain().focus().toggleOrderedList().run();
</script>

<template>
  <div>
    <!-- Toolbar -->
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
    <EditorContent v-if="editor" :editor="editor" class="editor-content" />
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

.editor-content {
  min-height: 150px;
  border: 1px solid #ccc;
  padding: 10px;
  border-radius: 4px;
  position: relative; /* Ensure positioning context for the dropdown */
}

.mention {
  color: #40a2e3; /* Sky Blue */
  font-weight: bold;
}

.mention-suggestion {
  position: absolute;
  background: white;
  border: 1px solid #ddd;
  border-radius: 5px;
  max-height: 150px;
  overflow-y: auto;
  z-index: 100;
  width: 200px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.mention-suggestion__item {
  padding: 5px 10px;
  cursor: pointer;
  transition: background 0.2s ease-in-out;
}

.mention-suggestion__item--active {
  background: #f0f8ff; /* Light blue background */
}
</style>
