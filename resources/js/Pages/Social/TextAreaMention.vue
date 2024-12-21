<template>
    <editor-content :editor="editor" class="editor-container"/>
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
    props: ['postContent', 'placeholder'],
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
                placeholder: this.placeholder ? this.placeholder : 'Type something amazing...',
            }),
        ],
        content: this.postContent || '',
        onUpdate: ({ editor }) => {
          const content = editor.getHTML(); // Get the updated content
          this.$emit('content-changed', content); // Emit the content
        },
      })

      this.editor.commands.focus();

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

.editor-container {
  border: 1px solid rgb(117 127 141); /* Add border color */
  border-radius: 8px; /* Optional: add rounded corners */
  padding: 10px; /* Add some padding inside the editor */
  max-width: 100%;
  box-sizing: border-box; /* Ensure padding doesn’t affect width */
}

  </style>