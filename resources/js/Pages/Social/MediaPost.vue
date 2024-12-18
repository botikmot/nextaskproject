<script setup>
  import { ref, defineProps } from 'vue';
  import ImageViewer from './ImageViewer.vue';
  import VideoPlayer from './VideoPlayer.vue';
  import MediaPreview from './MediaPreview.vue'
  
  const props = defineProps({
    post: Object,
  });
  
  const isPreviewVisible = ref(false);
  const previewMediaSrc = ref('');
  const previewMediaType = ref('');
  const previewMediaIndex = ref(0);

  const getComponent = (media) => (media.media_type === 'video' ? VideoPlayer : ImageViewer);

  const showPreview = (index) => {
    previewMediaIndex.value = index;
    previewMediaSrc.value = 'storage/' + props.post.media[index].media_path;
    previewMediaType.value = props.post.media[index].media_type === 'video' ? VideoPlayer : ImageViewer;
    isPreviewVisible.value = true;
  };

  const updatePreviewMedia = (index) => {
    previewMediaIndex.value = index;
    previewMediaSrc.value = 'storage/' + props.post.media[index].media_path;
    previewMediaType.value = props.post.media[index].media_type === 'video' ? VideoPlayer : ImageViewer;
  };

</script>

<template>
    <div v-if="post.media && post.media.length" class="mt-2 flex">
      <template v-if="post.media.length === 1">
        <component :is="getComponent(post.media[0])" :src="'storage/' + post.media[0].media_path" class="single-media"  @click="showPreview(0)" />
      </template>
      <template v-else-if="post.media.length === 2">
        <component :is="getComponent(post.media[0])" :src="'storage/' + post.media[0].media_path" class="vertical-media"  @click="showPreview(0)" />
        <component :is="getComponent(post.media[1])" :src="'storage/' + post.media[1].media_path" class="vertical-media"  @click="showPreview(1)" />
      </template>
      <template v-else-if="post.media.length === 3">
        <div class="three-media">
          <component :is="getComponent(post.media[0])" :src="'storage/' + post.media[0].media_path" class="multiple-media" @click="showPreview(0)" />
          <div class="w-full">
            <component :is="getComponent(post.media[1])" :src="'storage/' + post.media[1].media_path" class="h-1/2" @click="showPreview(1)" />
            <component :is="getComponent(post.media[2])" :src="'storage/' + post.media[2].media_path" class="h-1/2" @click="showPreview(2)" />
          </div>
        </div>
      </template>
      <template v-else-if="post.media.length === 4">
        <div class="four-media">
          <component :is="getComponent(post.media[0])" :src="'storage/' + post.media[0].media_path" class="h-full" @click="showPreview(0)"/>
          <component :is="getComponent(post.media[1])" :src="'storage/' + post.media[1].media_path" class="h-full" @click="showPreview(1)"/>
          <component :is="getComponent(post.media[2])" :src="'storage/' + post.media[2].media_path" class="h-full" @click="showPreview(2)"/>
          <component :is="getComponent(post.media[3])" :src="'storage/' + post.media[3].media_path" class="h-full" @click="showPreview(3)"/>
        </div>
      </template>
      <template v-else>
        <div class="three-media">
          <component :is="getComponent(post.media[0])" :src="'storage/' + post.media[0].media_path" class="multiple-media" @click="showPreview(0)"/>
          <div class="relative w-full">
            <component :is="getComponent(post.media[1])" :src="'storage/' + post.media[1].media_path" class="h-1/3" @click="showPreview(1)"/>
            <component :is="getComponent(post.media[2])" :src="'storage/' + post.media[2].media_path" class="h-1/3" @click="showPreview(2)"/>
            <component :is="getComponent(post.media[3])" :src="'storage/' + post.media[3].media_path" class="h-1/3" @click="showPreview(3)"/>
            <div class="more-media h-1/3" @click="showPreview(3)">+{{ post.media.length - 4 }}</div>
          </div>
        </div>
      </template>
    </div>

    <MediaPreview
      :isVisible="isPreviewVisible"
      :mediaSrc="previewMediaSrc"
      :mediaType="previewMediaType"
      :mediaIndex="previewMediaIndex"
      :mediaList="post.media"
      @close="isPreviewVisible = false"
      @update="updatePreviewMedia"
    />
</template>
  
  
  
  <style>
  .single-media {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
  }
  
  .vertical-media {
    width: 50%;
    height: 450px;
    object-fit: cover;
  }

  .multiple-media {
    width: 70%;
    height: 500px;
    object-fit: cover;
  }

  .three-media {
    display: flex;
    gap: 5px;
    max-height: 500px;
  }
  
  .three-media > div {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  
  .four-media {
    display: grid;
    grid-template-columns: repeat(2, 50%);
    grid-template-rows: repeat(2, 50%);
    gap: 5px;
    object-fit: cover;
  }
      
  .more-media {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 2em;
    position: absolute;
    bottom: 0;
    left: 0;
  }
  
  
  </style>
  