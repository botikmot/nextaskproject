<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const totalSeconds = ref(0);
const totalMinutes = ref(0); // Total tracked minutes
const elapsedSeconds = ref(0);
const isRunning = ref(false); // Timer status
const interval = ref(null); // Timer interval reference
const emit = defineEmits(['update-task-duration']);

const props = defineProps({
  task: Object,
});

// Computed property for formatted time
const formattedTime = computed(() => {
    const totalSeconds = totalMinutes.value * 60 + elapsedSeconds.value; // Include elapsed seconds
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${hours}h ${minutes}m ${seconds}s`;
});

// Fetch task time from the backend
const fetchTaskTime = async () => {
  try {
    const response = await axios.get(`/tasks/${props.task.id}/time`);
    const data = response.data;
    console.log('Fetch Time Response:', data);

    if (data) {
      totalMinutes.value = data.hours * 60 + data.minutes;
      totalSeconds.value = totalMinutes.value * 60 + (data.seconds || 0);
      elapsedSeconds.value = data.seconds || 0;
      isRunning.value = data.isRunning
      console.log('totalSeconds.value:', totalSeconds.value);

      emit('update-task-duration', totalSeconds.value);

      if (data.isRunning && data.startTime) {
        console.log('Timer is running. Starting real-time updater...');
        startRealTimeUpdater(new Date(data.startTime));
      } else {
        console.log('Timer is not running. Stopping real-time updater...');
        stopRealTimeUpdater();
      }
    }
  } catch (error) {
    console.error('Error fetching task time:', error);
  }
};




// Start the timer
const startTimer = async () => {
  try {
    if (isRunning.value) {
      console.log('Stopping timer...');
      const response = await axios.post(`/tasks/${props.task.id}/stop-timer`);
      console.log('Stop Timer Response:', response.data);

      stopRealTimeUpdater();
      isRunning.value = false; // Optimistically update the state
      //emit('update-task-duration', totalSeconds.value);
      fetchTaskTime();
    } else {
      console.log('Starting timer...');
      const response = await axios.post(`/tasks/${props.task.id}/start-timer`);
      const data = response.data;
      console.log('Start Timer Response:', data);

      if (data.startTime) {
        startRealTimeUpdater(new Date(data.startTime)); // Use backend's start time
        isRunning.value = true; // Optimistically update the state
      } else {
        console.error('Error: startTime is null');
      }
    }    
  } catch (error) {
    console.error('Error starting/stopping timer:', error);
  }
};




// Start real-time updater
const startRealTimeUpdater = (startTime, initialSeconds = 0) => {
  if (interval.value) {
    console.log('Real-time updater is already running.');
    return; // Prevent multiple intervals
  }

  interval.value = setInterval(() => {
    const now = new Date();
    elapsedSeconds.value = Math.floor((now - startTime) / 1000); // Calculate elapsed seconds

    // Calculate total minutes and seconds
    totalMinutes.value = Math.floor(totalSeconds.value / 60);
  }, 1000); // Update every second
};



// Stop real-time updater
const stopRealTimeUpdater = () => {
  if (interval.value) {
    console.log('Stopping real-time updater.');
    clearInterval(interval.value);
    interval.value = null;
  }
  isRunning.value = false;
};


onMounted(() => {
  fetchTaskTime();
});
</script>

<template>
  <div class="text-sm">
    <i class="fa-regular fa-clock text-navy-blue"></i>
    <span id="task-time" class="ml-2 text-navy-blue font-semibold">{{ formattedTime }}</span>
    <button 
      class="ml-2 w-8 h-8 bg-linen rounded-full relative group" 
      @click="startTimer"
    >
      <i v-if="!isRunning" class="fa-solid fa-play text-green-leaf"></i>
      <i v-else class="fa-solid fa-pause text-red-warning"></i>
      <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
        {{ isRunning ? 'Stop timer' : 'Start timer' }}
      </span>
    </button>
  </div>
</template>
