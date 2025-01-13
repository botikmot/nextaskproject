import { Node } from '@tiptap/core';

export const EmojiExtension = Node.create({
  name: 'emoji',

  group: 'inline', // Ensures the emoji is treated as inline
  inline: true, // Forces inline behavior

  addAttributes() {
    return {
      src: {
        default: null,
      },
      alt: {
        default: null,
      },
      class: {
        default: 'emoji-image',
      },
    };
  },

  parseHTML() {
    return [
      {
        tag: 'img[src]',
        getAttrs: (node) => ({
          src: node.getAttribute('src'),
          alt: node.getAttribute('alt'),
          class: 'emoji-image',
        }),
      },
    ];
  },

  renderHTML({ node }) {
    return [
      'img',
      {
        src: node.attrs.src,
        alt: node.attrs.alt,
        class: node.attrs.class,
        contenteditable: false, // Ensures the emoji is not editable
      },
    ];
  },

  addCommands() {
    return {
      insertEmoji:
        (src, alt = "") =>
        ({ commands }) => {
          return commands.insertContent({
            type: this.name,
            attrs: { 
              src, 
              alt,
              class: 'emoji-image',
            },
          });
        },
    };
  },
});
