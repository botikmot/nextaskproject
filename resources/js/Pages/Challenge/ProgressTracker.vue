<template>
    <div class="progress-tracker">
      <label for="progress">Progress: {{ progress }}%</label>
      <input 
        type="range" 
        id="progress" 
        v-model="progressInput" 
        @input="updateProgressInput"
        min="0" 
        :max="max"
        class="w-full"
      />
      <!-- <button 
        @click="submitProgress" 
        class="mt-4 bg-sky-blue text-color-white py-1 px-3 rounded hover:bg-navy-blue"
      >
        Update Progress
      </button> -->
    </div>
  </template>
  
  <script>
  export default {
    props: {
      progress: Number,
      max: Number,
    },
    data() {
      return {
        progressInput: 45,
      };
    },
    methods: {
      updateProgressInput() {
        this.$emit("update-progress", this.progressInput);
      },
      submitProgress() {
        alert(`Progress updated to ${this.progressInput}%`);
      },
    },
  };
  </script>
  