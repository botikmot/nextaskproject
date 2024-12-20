<template>
    <editor-content :editor="editor" />
</template>
  
  <script>
  import Document from '@tiptap/extension-document'
  import Mention from '@tiptap/extension-mention'
  import Paragraph from '@tiptap/extension-paragraph'
  import Placeholder from '@tiptap/extension-placeholder'
  import Text from '@tiptap/extension-text'
  import { Editor, EditorContent } from '@tiptap/vue-3'
  
  import suggestion from './suggestion.js'
  
  export default {
    components: {
      EditorContent,
    },
  
    data() {
      return {
        editor: null,
        limit: 280,
      }
    },
  
    mounted() {
      this.editor = new Editor({
        extensions: [
          Document,
          Paragraph,
          Text,
          Mention.configure({
            HTMLAttributes: {
              class: 'mention',
            },
            suggestion,
            renderHTML({ node }) {
              return `${node.attrs.label}`  // Use name instead of id
            },
          }),
          Placeholder.configure({
                placeholder: 'Type something amazing...',
            }),
        ],
        content:'',
        onUpdate: ({ editor }) => {
          const content = editor.getHTML(); // Get the updated content
          this.$emit('content-changed', content); // Emit the content
        },
      })
    },

    methods: {
      clearContent() {
        this.editor.commands.setContent(''); // Clear the editor content
      },
    },
    
    beforeUnmount() {
      this.editor.destroy()
    },
  }
  </script>
  
  <style lang="scss">
  /* Basic editor styles */
  .tiptap {
    :first-child {
      margin-top: 0;
    }
  
    .mention {
      border-radius: 0.4rem;
      box-decoration-break: clone;
      color: var(--purple);
    }
  }
  
  /* Character count */
  .character-count {
    align-items: center;
    color: var(--gray-5);
    display: flex;
    font-size: 0.75rem;
    gap: .5rem;
    margin: 1.5rem;
  
    svg {
      color: var(--purple);
    }
  
    &--warning,
    &--warning svg {
      color: var(--red);
    }
  }
  [data-placeholder]::before {
    content: attr(data-placeholder);
    color: #aaa;
    pointer-events: none;
    position: absolute;
    }
  </style>