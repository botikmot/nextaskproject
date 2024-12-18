<script setup>
  import { defineProps, defineEmits } from 'vue';
  
  const props = defineProps({
    isVisible: {
      type: Boolean,
      required: true,
    },
    mediaSrc: {
      type: String,
      required: true,
    },
    mediaType: {
      type: String,
      required: true,
    },
    mediaIndex: {
      type: Number,
      required: true,
    },
    mediaList: {
      type: Array,
      required: true,
    },
  });
  
  const emit = defineEmits(['close', 'update']);
  
  const closeModal = () => {
    emit('close');
  };
  
  const prevMedia = () => {
    let newIndex = props.mediaIndex - 1;
    if (newIndex < 0) {
      newIndex = props.mediaList.length - 1;
    }
    emit('update', newIndex);
  };
  
  const nextMedia = () => {
    let newIndex = props.mediaIndex + 1;
    if (newIndex >= props.mediaList.length) {
      newIndex = 0;
    }
    emit('update', newIndex);
  };
</script>
  
<template>
    <div v-if="isVisible" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-content">
            <component :is="mediaType" :src="mediaSrc" class="media-preview" />
        </div>
        <button @click.stop="prevMedia" class="nav-button prev-button">&laquo; Prev</button>
        <button @click.stop="nextMedia" class="nav-button next-button">Next &raquo;</button>
    </div>
</template>



<style>
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .media-preview {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }
  
  .nav-button {
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    font-size: 1.5em;
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1010;
  }
  
  .prev-button {
    left: 20px;
  }
  
  .next-button {
    right: 20px;
  }
</style>
  